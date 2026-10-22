<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/qr_generator.php';
require_once __DIR__ . '/equipment_predictions.php';
require_once __DIR__ . '/../services/damage_disposal.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();

// ── Helpers ──

function nextEquipCode(PDO $pdo): string {
    $stmt = $pdo->query('SELECT equip_code FROM equipment ORDER BY id DESC LIMIT 1');
    $last = $stmt->fetchColumn();
    if (!$last) return 'EQ-001';
    preg_match('/(\d+)$/', $last, $m);
    return 'EQ-' . str_pad((int)($m[1] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
}


function findRoom(PDO $pdo, string $roomRef): array|false {
    if ($roomRef === 'SBO Office') {
        return ['id' => 0, 'room_name' => 'SBO Office'];
    }

    try {
        $s = $pdo->prepare('SELECT id, room_name FROM rooms WHERE room_code = ? LIMIT 1');
        $s->execute([$roomRef]);
        $r = $s->fetch();
        if ($r) return $r;
    } catch (PDOException) {}

    try {
        $s = $pdo->prepare('SELECT id, room_name FROM rooms WHERE room_id = ? LIMIT 1');
        $s->execute([$roomRef]);
        return $s->fetch() ?: false;
    } catch (PDOException) {}

    return false;
}

if ($method === 'GET') {
    $id     = (int)get('id');
    $search = get('search');
    $status = get('status');
    $room   = get('room');

    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM equipment WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $eq = $stmt->fetch();
        if (!$eq) {
            jsonResponse(false, 'Equipment not found.', [], 404);
        }
        jsonResponse(true, 'OK', ['equipment' => $eq]);
    }

    $sql    = 'SELECT e.* FROM equipment e WHERE 1=1';
    $params = [];

    if ($search !== '') {
        $sql   .= ' AND (e.equip_code LIKE ? OR e.name LIKE ? OR e.serial_number LIKE ?)';
        $like   = "%$search%";
        array_push($params, $like, $like, $like);
    }
    if ($status !== '') { $sql .= ' AND e.status = ?';         $params[] = $status; }
    if ($room   !== '') { $sql .= ' AND e.assigned_room = ?';  $params[] = $room;   }
    $sql .= ' ORDER BY e.equip_code ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipment = $stmt->fetchAll();

    foreach ($equipment as &$eq) {
        $room = findRoom($pdo, $eq['assigned_room'] ?? '');
        $eq['room_name'] = $room ? $room['room_name'] : '';
    }
    unset($eq);

    try {
        $predictions = fetchEquipmentPredictions($pdo);
        $predictionMap = [];
        foreach ($predictions as $prediction) {
            $predictionMap[(int)$prediction['equipment_id']] = $prediction;
        }
        foreach ($equipment as &$eq) {
            $eq['prediction'] = $predictionMap[(int)$eq['id']] ?? null;
        }
        unset($eq);
    } catch (Throwable) {
    }

    // Stats
    $raw   = $pdo->query('SELECT status, COUNT(*) c FROM equipment GROUP BY status')->fetchAll();
    $stats = ['total' => 0, 'Available' => 0, 'In Use' => 0, 'Damaged' => 0, 'Under Repair' => 0];
    foreach ($raw as $r) {
        $stats[$r['status']] = (int)$r['c'];
        $stats['total']     += (int)$r['c'];
    }

    jsonResponse(true, 'OK', ['equipment' => $equipment, 'stats' => $stats]);
}

// ── POST — create + QR ──
if ($method === 'POST') {
    requireLogin('admin');

    $equip_code    = post('equip_code')     ?: nextEquipCode($pdo);
    $name          = post('name');
    $description   = post('description');
    $status        = post('status')         ?: 'Available';
    $assigned_room = post('assigned_room') ?: 'SBO Office';
    $serial_number = post('serial_number');
    $purchased_date= post('purchased_date');

    if (!$name) {
        jsonResponse(false, 'Equipment name is required.');
    }

    $valid_statuses = ['Available', 'In Use', 'Damaged', 'Under Repair'];
    if (!in_array($status, $valid_statuses, true)) {
        jsonResponse(false, 'Invalid status value.');
    }

    // Verify room exists only if a room is assigned
    if ($assigned_room && !findRoom($pdo, $assigned_room)) {
        jsonResponse(false, "Room '$assigned_room' not found. Please add the room first.");
    }

    // Check duplicate equip_code
    $chk = $pdo->prepare('SELECT id FROM equipment WHERE equip_code = ? LIMIT 1');
    $chk->execute([$equip_code]);
    if ($chk->fetch()) {
        jsonResponse(false, "Equipment code '$equip_code' already exists.");
    }

    $qr_string  = $equip_code;               
    $qr_filename = $equip_code;             

    $qr_path = generateQR($qr_string, $qr_filename);

    if ($qr_path === false) {
        error_log("[GCST] QR generation failed for $equip_code");
    }

    $stmt = $pdo->prepare(
        'INSERT INTO equipment
           (equip_code, name, description, qr_string, qr_image_path,
            status, assigned_room, serial_number, purchased_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $equip_code,
        $name,
        $description  ?: null,
        $qr_string,
        $qr_path      ?: null,
        $status,
        $assigned_room,
        $serial_number ?: null,
        $purchased_date ?: null,
    ]);
    $newId = (int)$pdo->lastInsertId();

    // Notify admins of new equipment
    notifyAdmins($pdo, 'equipment_added',
        "Equipment Added: $equip_code",
        "$equip_code — $name has been added to $assigned_room.",
        'equipment'
    );

    logActivity($pdo, 'CREATE_EQUIPMENT', "equip:$newId", "$equip_code — $name");

    updateEquipmentPredictions($pdo, $newId);

    jsonResponse(true, 'Equipment added successfully', [
        'status'        => 'success',
        'equip_id'      => $newId,
        'equip_code'    => $equip_code,
        'qr_string'     => $qr_string,
        'qr_path'       => $qr_path,
        'qr_image_path' => $qr_path,
        'qr_generated'  => ($qr_path !== false),
    ]);
}

// ── PUT — full update ─────────────────────────────────────────
if ($method === 'PUT') {
    requireLogin('admin');

    $b             = bodyForm();
    $id            = (int)($b['id']             ?? 0);
    $new_code      = clean($b['equip_code']     ?? '');
    $name          = clean($b['name']           ?? '');
    $description   = clean($b['description']    ?? '');
    $status        = clean($b['status']         ?? '');
    $assigned_room = clean($b['assigned_room']  ?? '') ?: 'SBO Office';
    $serial_number = clean($b['serial_number']  ?? '');
    $purchased_date= clean($b['purchased_date'] ?? '');

    if (!$id || !$name || !$status) {
        jsonResponse(false, 'ID, name, and status are required.');
    }

    if ($assigned_room && !findRoom($pdo, $assigned_room)) {
        jsonResponse(false, "Room '$assigned_room' not found.");
    }

    $pdo->beginTransaction();
    $old = $pdo->prepare('SELECT status, equip_code, qr_image_path FROM equipment WHERE id = ? FOR UPDATE');
    $old->execute([$id]);
    $oldRow = $old->fetch();
    if (!$oldRow) {
        jsonResponse(false, 'Equipment not found.');
    }

    assertEquipmentStatusEdit($pdo, $id, $status);
    $statusChanged = $oldRow['status'] !== $status;
    $old_code = $oldRow['equip_code'];
    $qr_path = $oldRow['qr_image_path'];
    $qr_string = $old_code;
    
    if ($new_code && $new_code !== $old_code) {
        $chk = $pdo->prepare('SELECT id FROM equipment WHERE equip_code = ? AND id != ?');
        $chk->execute([$new_code, $id]);
        if ($chk->fetch()) {
            jsonResponse(false, "Equipment code '$new_code' is already taken.");
        }
        
        // Delete old QR code file
        if (!empty($oldRow['qr_image_path'])) {
            $file = __DIR__ . '/../' . $oldRow['qr_image_path'];
            if (file_exists($file)) @unlink($file);
        }
        
        // Generate new QR
        $qr_string = $new_code;
        $qr_path = generateQR($qr_string, $new_code);
        if ($qr_path === false) error_log("[GCST] QR generation failed for $new_code during update");
    } else {
        $new_code = $old_code;
    }

    $stmt = $pdo->prepare(
        'UPDATE equipment
         SET equip_code=?, qr_string=?, qr_image_path=?, name=?, description=?, status=?, assigned_room=?, serial_number=?, purchased_date=?
         WHERE id=?'
    );
    $stmt->execute([
        $new_code, $qr_string, $qr_path ?: null, $name, $description ?: null, $status, $assigned_room,
        $serial_number ?: null, $purchased_date ?: null, $id
    ]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(false, 'Equipment not found or nothing changed.');
    }

    if ($statusChanged && $status === 'Damaged') {
        notifyAdmins($pdo, 'equipment_damaged',
            'Equipment Marked Damaged',
            "{$oldRow['equip_code']} — $name has been marked as Damaged.",
            'equipment'
        );
    }

    $pdo->commit();
    logActivity($pdo, 'UPDATE_EQUIPMENT', "equip:$id", "Updated: $name → $status");
    updateEquipmentPredictions($pdo, $id);
    jsonResponse(true, 'Equipment updated successfully.');
}

if ($method === 'PATCH') {
    requireLogin(['admin', 'maintenance']);

    $b      = bodyJson();
    $id     = (int)($b['id']     ?? 0);
    $status = clean($b['status'] ?? '');

    if (!$id || !$status) {
        jsonResponse(false, 'Equipment ID and status are required.');
    }

    $valid = ['Available', 'In Use', 'Damaged', 'Under Repair'];
    if (!in_array($status, $valid, true)) {
        jsonResponse(false, 'Invalid status. Use: Available, In Use, Damaged, or Under Repair.');
    }

    // Fetch for notification
    $pdo->beginTransaction();
    $row = $pdo->prepare('SELECT equip_code, name, status FROM equipment WHERE id = ? FOR UPDATE');
    $row->execute([$id]);
    $eq = $row->fetch();
    if (!$eq) jsonResponse(false, 'Equipment not found.');

    assertEquipmentStatusEdit($pdo, $id, $status);
    $pdo->prepare('UPDATE equipment SET status = ? WHERE id = ?')->execute([$status, $id]);

    if ($eq['status'] !== $status && $status === 'Damaged') {
        notifyAdmins($pdo, 'equipment_damaged',
            'Equipment Marked Damaged',
            "{$eq['equip_code']} — {$eq['name']} has been marked as Damaged.",
            'equipment'
        );
    }

    $pdo->commit();
    logActivity($pdo, 'UPDATE_EQUIP_STATUS', "equip:$id", "{$eq['equip_code']} → $status");
    updateEquipmentPredictions($pdo, $id);
    jsonResponse(true, "Status updated to '$status'.");
}

// ── DELETE ──
if ($method === 'DELETE') {
    requireLogin('admin');

    $b  = bodyForm();
    $id = (int)($b['id'] ?? 0);
    if (!$id) {
        jsonResponse(false, 'Equipment ID required.');
    }

    $stmt = $pdo->prepare('SELECT qr_image_path, name, equip_code FROM equipment WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        jsonResponse(false, 'Equipment not found.');
    }

    $history = $pdo->prepare('SELECT COUNT(*) FROM damage_assessments WHERE equipment_id = ?');
    $history->execute([$id]);
    if ($history->fetchColumn()) jsonResponse(false, 'Equipment with damage/disposal history must be retained for reports.', [], 409);

    // Delete QR image file from disk
    if (!empty($row['qr_image_path'])) {
        $file = __DIR__ . '/../' . $row['qr_image_path'];
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    $pdo->prepare('DELETE FROM equipment WHERE id = ?')->execute([$id]);
    logActivity($pdo, 'DELETE_EQUIPMENT', "equip:$id", "Deleted: {$row['equip_code']} — {$row['name']}");
    jsonResponse(true, 'Equipment deleted successfully.');
}

    jsonResponse(false, 'Method not allowed.', [], 405);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log("[GCST Error] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code($e instanceof InvalidArgumentException ? 422 : 500);
    $msg = $e->getMessage();
    if (strpos($msg, 'GD library') !== false) {
        $finalMsg = $msg;
    } else {
        $finalMsg = 'Internal Server Error: ' . $msg;
    }
    echo json_encode([
        'status'  => 'error',
        'message' => $finalMsg
    ]);
    exit;
}

function notifyAdmins(PDO $pdo, string $type, string $title, string $body, string $link = ''): void {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO notifications (user_id, type, title, body, link_ref)
             VALUES (NULL, ?, ?, ?, ?)'
        );
        $stmt->execute([$type, $title, $body, $link ?: null]);
    } catch (PDOException $e) {
        error_log('[GCST] notifyAdmins failed: ' . $e->getMessage());
    }
}
