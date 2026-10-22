<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/equipment_predictions.php';
require_once __DIR__ . '/../services/damage_disposal.php';

setCors();
requireLogin(['admin','maintenance']);

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$userId = getUserId();

function nextReportCode(PDO $pdo): string {
    $year = date('Y');
    $prefix = "REP-$year-";
    $stmt = $pdo->prepare(
        'SELECT report_code
         FROM condition_reports
         WHERE report_code LIKE ?
         ORDER BY report_code DESC
         LIMIT 1'
    );
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $next = $last ? ((int)substr($last, strlen($prefix)) + 1) : 1;

    do {
        $code = $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
        $check = $pdo->prepare('SELECT COUNT(*) FROM condition_reports WHERE report_code = ?');
        $check->execute([$code]);
        $exists = (int)$check->fetchColumn() > 0;
        $next++;
    } while ($exists);

    return $code;
}

if ($method === 'GET') {
    $stmt = $pdo->query(
        'SELECT cr.*, e.name as equip_name, e.equip_code, u.full_name as reporter_name
         FROM condition_reports cr
         LEFT JOIN equipment e ON cr.equipment_id = e.id
         LEFT JOIN users u     ON cr.reported_by  = u.id
         ORDER BY cr.created_at DESC'
    );
    $reports = $stmt->fetchAll();
    jsonResponse(true, 'OK', ['reports' => $reports]);
}

if ($method === 'POST') {
    $equipment_id = (int)post('equipment_id');
    $issue        = trim((string) ($_POST['issue'] ?? ''));
    $severity     = post('severity') ?: 'Low';
    $action       = post('action_needed') ?: 'Monitor';
    $damageLevel = $_POST['damage_level'] ?? 'None';
    if (!in_array($damageLevel, ['None', 'Not Critical', 'Critical'], true)) jsonResponse(false, 'Invalid damage level.', [], 422);
    if ($severity === 'High' && $damageLevel === 'None') jsonResponse(false, 'Choose a damage level for a high-severity damage report.', [], 422);
    $cost = '0.00';
    if ($damageLevel !== 'None') {
        try { $cost = damageCost($_POST['assessed_cost'] ?? null); }
        catch (InvalidArgumentException $e) { jsonResponse(false, $e->getMessage(), [], 422); }
        $action = $damageLevel === 'Critical' ? 'Decommission' : 'Repair';
    }

    if (!$equipment_id || empty($issue)) {
        jsonResponse(false, 'Equipment and issue description are required.');
    }

    $allowed_sev    = ['Low','Medium','High'];
    $allowed_action = ['Monitor','Repair','Replace','Decommission'];
    if (!in_array($severity, $allowed_sev, true))   jsonResponse(false, 'Invalid severity.');
    if (!in_array($action,   $allowed_action, true)) jsonResponse(false, 'Invalid action.');

    // Check equipment exists
    $chk = $pdo->prepare('SELECT id, name FROM equipment WHERE id = ? LIMIT 1');
    $chk->execute([$equipment_id]);
    $equipment = $chk->fetch();
    if (!$equipment) jsonResponse(false, 'Equipment not found.');

    try {
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT status FROM equipment WHERE id = ? FOR UPDATE');
        $lock->execute([$equipment_id]);
        if ($lock->fetchColumn() === 'Disposed') throw new InvalidArgumentException('Disposed equipment cannot receive new reports.');
        $report_code = nextReportCode($pdo);
        $stmt = $pdo->prepare(
            'INSERT INTO condition_reports
               (report_code, equipment_id, issue, severity, action_needed, reported_by, status, damage_level, assessed_cost)
             VALUES (?, ?, ?, ?, ?, ?, "Open", ?, ?)'
        );
        $stmt->execute([$report_code, $equipment_id, $issue, $severity, $action, $userId, $damageLevel, $cost]);
        $reportId = (int) $pdo->lastInsertId();

        if ($damageLevel !== 'None') assessEquipmentDamage($pdo, $equipment_id, $damageLevel, $issue, $cost, $userId, null, $reportId);
        $pdo->commit();
        try { updateEquipmentPredictions($pdo, $equipment_id); } catch (Throwable $e) { error_log($e->getMessage()); }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        jsonResponse(false, 'Unable to create condition report: ' . $e->getMessage(), [], 422);
    }

    $msg = "New condition report ($severity severity) filed for " . $equipment['name'];
    try {
        $pdo->prepare('INSERT INTO maintenance_notifications (user_id, type, title, body, link_ref) VALUES (NULL, "report", "New Condition Report", ?, "condition_reports")')->execute([$msg]);
        $pdo->prepare('INSERT INTO admin_notifications (user_id, type, title, body, link_ref) VALUES (NULL, "report", "New Condition Report", ?, "condition_reports")')->execute([$msg]);
    } catch (Exception $e) {}

    jsonResponse(true, 'Report submitted successfully.', [
        'id' => $reportId,
        'report_code' => $report_code,
    ]);
}

if ($method === 'PATCH') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($body['id'] ?? 0);
    if (!$id) jsonResponse(false, 'Report ID required.');

    $stmt = $pdo->prepare('SELECT r.*, e.name as equip_name, u.role as reporter_role FROM condition_reports r JOIN equipment e ON r.equipment_id = e.id JOIN users u ON r.reported_by = u.id WHERE r.id=?');
    $stmt->execute([$id]);
    $report = $stmt->fetch();
    if (!$report) jsonResponse(false, 'Report not found.');
    if ($report['status'] === 'Resolved') jsonResponse(false, 'Report already resolved.');

    $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare('SELECT status FROM equipment WHERE id = ? FOR UPDATE');
        $lock->execute([$report['equipment_id']]);
        $equipmentStatus = $lock->fetchColumn();
        if (in_array($equipmentStatus, ['For Disposal', 'Disposed'], true)) throw new InvalidArgumentException('Disposal items cannot be restored through condition-report resolution.');
        $stmt = $pdo->prepare('UPDATE condition_reports SET status="Resolved", resolved_at=NOW() WHERE id=?');
        $stmt->execute([$id]);
        $other = $pdo->prepare('SELECT COUNT(*) FROM condition_reports WHERE equipment_id=? AND status <> "Resolved" AND damage_level <> "None"');
        $other->execute([$report['equipment_id']]);
        if (!$other->fetchColumn()) {
            $active = $pdo->prepare("SELECT COUNT(*) FROM borrowed WHERE equipment_id=? AND status IN ('Borrowed','Overdue')");
            $active->execute([$report['equipment_id']]);
            $pdo->prepare('UPDATE equipment SET status=?, damage_level="None" WHERE id=?')->execute([$active->fetchColumn() ? 'In Use' : 'Available', $report['equipment_id']]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        jsonResponse(false, $e->getMessage(), [], 422);
    }
    try { updateEquipmentPredictions($pdo, (int)$report['equipment_id']); } catch (Throwable $e) { error_log($e->getMessage()); }

    $roleTable = $report['reporter_role'] . '_notifications';
    $msg = "Your condition report for " . $report['equip_name'] . " has been resolved. Equipment availability follows the remaining assessments and active loans.";
    try {
        $pdo->prepare("INSERT INTO {$roleTable} (user_id, type, title, body, link_ref) VALUES (?, 'report', 'Report Resolved', ?, 'condition_reports')")->execute([$report['reported_by'], $msg]);
    } catch (Exception $e) {}

    jsonResponse(true, 'Report marked as resolved.');
}

jsonResponse(false, 'Unsupported request method.', [], 405);
