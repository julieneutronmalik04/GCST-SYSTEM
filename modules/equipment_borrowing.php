<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/equipment_predictions.php';
require_once __DIR__ . '/../services/overdue.php';
require_once __DIR__ . '/../services/damage_disposal.php';

requireLogin(['admin', 'maintenance']);
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
refreshOverdue($pdo);

if ($method === 'GET') {
    $status = get('status');
    $search = get('search');
    
    $sql    = "SELECT b.*, (b.penalty_amount + b.damage_penalty_amount) AS total_penalty_amount, e.equip_code, e.name as equip_name,
                      CASE
                        WHEN b.status = 'Borrowed' AND b.due_date < CURRENT_DATE
                        THEN 'Overdue'
                        ELSE b.status
                      END AS current_status
               FROM borrowed b 
               JOIN equipment e ON b.equipment_id = e.id 
               WHERE 1=1";
    $params = [];
    
    if ($status !== '') {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }
    if ($search !== '') {
        $sql .= " AND (e.equip_code LIKE ? OR e.name LIKE ? OR b.borrower_name LIKE ? OR b.department LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like);
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $borrowings = $stmt->fetchAll();
    
    $raw = $pdo->query(
        "SELECT
            CASE
              WHEN status = 'Borrowed' AND due_date < CURRENT_DATE
              THEN 'Overdue'
              ELSE status
            END AS current_status,
            COUNT(*) c
         FROM borrowed
         GROUP BY current_status"
    )->fetchAll();
    $stats = ['total' => 0, 'Borrowed' => 0, 'Overdue' => 0, 'Returned' => 0, 'Returned (Damaged)' => 0];
    foreach ($raw as $r) {
        $stats[$r['current_status']] = (int)$r['c'];
        $stats['total']     += (int)$r['c'];
    }
    
    jsonResponse(true, 'OK', ['borrowings' => $borrowings, 'stats' => $stats]);
}

if ($method === 'POST') {
    $student_faculty_id   = trim((string) ($_POST['student_faculty_id'] ?? ''));
    $equip_code           = post('equip_code'); 
    $borrower_name        = post('borrower_name');
    $department           = post('department');
    $borrow_date          = post('borrow_date');
    $time_out             = post('time_out');
    $expected_return_date = post('expected_return_date');
    $expected_return_time = post('expected_return_time');
    $purpose              = post('purpose');

    if (!$student_faculty_id || !$equip_code || !$borrow_date || !$time_out || !$expected_return_date || !$expected_return_time) {
        jsonResponse(false, 'Required fields: Equipment Code, Borrower, Dates, and Times.');
    }

    $borrowerStmt = $pdo->prepare('SELECT first_name, last_name, department_course FROM borrowers WHERE student_faculty_id = ?');
    $borrowerStmt->execute([$student_faculty_id]);
    $registeredBorrower = $borrowerStmt->fetch();
    if (!$registeredBorrower) jsonResponse(false, 'Select a registered borrower by ID or name.', [], 422);
    // Contact identity and borrower details come from the database, not editable form values.
    $borrower_name = trim($registeredBorrower['first_name'] . ' ' . $registeredBorrower['last_name']);
    $department = $registeredBorrower['department_course'];

    $outTimestamp = strtotime("$borrow_date $time_out");
    $inTimestamp  = strtotime("$expected_return_date $expected_return_time");
    
    if ($outTimestamp === false || $inTimestamp === false || $outTimestamp >= $inTimestamp) {
        jsonResponse(false, 'Expected Return must be AFTER Time Out.');
    }

    $pdo->beginTransaction();
    $eqStmt = $pdo->prepare('SELECT id, name, status FROM equipment WHERE equip_code = ? LIMIT 1 FOR UPDATE');
    $eqStmt->execute([$equip_code]);
    $equipment = $eqStmt->fetch();
    
    if (!$equipment) {
        jsonResponse(false, "Equipment code '$equip_code' not found.");
    }
    if (in_array($equipment['status'], ['Damaged', 'Under Repair', 'For Disposal', 'Disposed'], true)) {
        jsonResponse(false, "Cannot borrow equipment. Current status: {$equipment['status']}");
    }

    $conflict = $pdo->prepare("
        SELECT id, borrower_name, status, borrow_date, time_out, expected_return_date, expected_return_time
        FROM borrowed
        WHERE equipment_id = ? AND status IN ('Borrowed', 'Overdue')
          AND (status = 'Overdue' OR (TIMESTAMP(borrow_date, time_out) < ?
          AND TIMESTAMP(expected_return_date, expected_return_time) > ?))
        LIMIT 1
    ");
    
    $reqEndStr   = "$expected_return_date $expected_return_time";
    $reqStartStr = "$borrow_date $time_out";
    
    $conflict->execute([$equipment['id'], $reqEndStr, $reqStartStr]);
    $overlap = $conflict->fetch();
    
    if ($overlap) {
        if ($overlap['status'] === 'Overdue') jsonResponse(false, 'This equipment is overdue with ' . $overlap['borrower_name'] . '. Process its return before creating another loan.');
        $overlapStart = date('M d, Y h:i A', strtotime("{$overlap['borrow_date']} {$overlap['time_out']}"));
        $overlapEnd   = date('M d, Y h:i A', strtotime("{$overlap['expected_return_date']} {$overlap['expected_return_time']}"));
        jsonResponse(false, "Scheduling Conflict: This equipment is already scheduled for {$overlap['borrower_name']} from $overlapStart to $overlapEnd.");
    }

    $stmt = $pdo->prepare(
        'INSERT INTO borrowed (
            equipment_id, borrower_name, student_faculty_id, department,
            borrow_date, time_out, expected_return_date, due_date, expected_return_time, purpose, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $equipment['id'],
        $borrower_name,
        $student_faculty_id,
        $department ?: null,
        $borrow_date,
        $time_out,
        $expected_return_date,
        $expected_return_date,
        $expected_return_time,
        $purpose ?: null,
        'Borrowed'
    ]);
    
    $newId = (int)$pdo->lastInsertId();
    
    $pdo->prepare("UPDATE equipment SET status = 'In Use' WHERE id = ?")->execute([$equipment['id']]);
    $pdo->commit();
    updateEquipmentPredictions($pdo, (int)$equipment['id']);
    
    logActivity($pdo, 'CREATE_BORROWING', "borrow:$newId", "Borrower: $borrower_name, Equipment: $equip_code");
    jsonResponse(true, 'Equipment borrowing recorded successfully.');
}

if ($method === 'PATCH') {
    $body = bodyJson();
    $id = (int) ($body['id'] ?? 0);
    if (!$id || ($body['action'] ?? '') !== 'return') jsonResponse(false, 'A valid return transaction is required.', [], 422);
    try {
        $result = processEquipmentReturn($pdo, $id, $body['return_condition'] ?? 'good', $body, getUserId());
    } catch (InvalidArgumentException $e) {
        jsonResponse(false, $e->getMessage(), [], 422);
    } catch (Throwable $e) {
        error_log('Equipment return failed: ' . $e->getMessage());
        jsonResponse(false, 'Unable to process the equipment return.', [], 500);
    }
    // Prediction refresh must not turn a committed return into an apparent failure.
    try { updateEquipmentPredictions($pdo, $result['equipment']['id']); } catch (Throwable $e) { error_log($e->getMessage()); }
    logActivity($pdo, 'RETURN_EQUIPMENT', 'borrow:' . $id, $result['borrowing_status'] . '; damage fee: ' . $result['damage_penalty_amount']);
    jsonResponse(true, 'Return recorded. Damage penalty: ' . $result['damage_penalty_amount'] . '.', $result);
}

if ($method === 'DELETE') {
    requireLogin('admin');
    parse_str(file_get_contents('php://input'), $b);
    $id = (int)($b['id'] ?? 0);
    
    if (!$id) jsonResponse(false, 'ID is required.');

    $history = $pdo->prepare('SELECT COUNT(*) FROM damage_assessments WHERE borrowed_id = ?');
    $history->execute([$id]);
    if ($history->fetchColumn()) jsonResponse(false, 'Borrowings with damage assessments and fees must be retained for reports.', [], 409);
    
    $pdo->prepare('DELETE FROM borrowed WHERE id = ?')->execute([$id]);
    logActivity($pdo, 'DELETE_BORROWING', "borrow:$id");
    jsonResponse(true, 'Borrowing record deleted.');
}

jsonResponse(false, 'Method not allowed.', [], 405);
