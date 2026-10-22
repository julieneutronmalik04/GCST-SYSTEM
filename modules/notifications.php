<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$uid    = getUserId();
$role   = getRole();

$roleTableMap = [
    'admin'       => 'admin_notifications',
    'faculty'     => 'faculty_notifications',
    'vip'         => 'faculty_notifications',
    'maintenance' => 'maintenance_notifications'
];

$table = $roleTableMap[$role] ?? null;

if (!$table) {
    jsonResponse(false, 'Invalid role.', [], 403);
}

if ($method === 'GET') {
    $limit       = max(1, min(50, (int)(get('limit') ?: 20)));
    $unread_only = get('unread') === '1';

    $sql    = "SELECT * FROM {$table} WHERE (user_id IS NULL OR user_id = ?)";
    $params = [$uid];

    if ($unread_only) {
        $sql .= ' AND is_read = 0';
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ?';
    $params[] = $limit;

    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $v) {
        $stmt->bindValue($i + 1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $notifications = $stmt->fetchAll();

    $cntStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE (user_id IS NULL OR user_id = ?) AND is_read = 0"
    );
    $cntStmt->execute([$uid]);
    
    $unread_count = (int)$cntStmt->fetchColumn();

    jsonResponse(true, 'OK', [
        'notifications' => $notifications,
        'unread_count'  => $unread_count,
    ]);
}

if ($method === 'PATCH') {
    $b      = bodyJson();
    $action = $b['action'] ?? 'mark_read'; 
    $id     = (int)($b['id'] ?? 0);

    if ($action === 'mark_all_read') {
        $pdo->prepare(
            "UPDATE {$table} SET is_read = 1 WHERE (user_id IS NULL OR user_id = ?)"
        )->execute([$uid]);
        
        jsonResponse(true, 'All notifications marked as read.', ['unread_count' => 0]);
    }

    if ($action === 'mark_read' && $id) {
        $pdo->prepare("UPDATE {$table} SET is_read = 1 WHERE id = ? AND (user_id IS NULL OR user_id = ?)")->execute([$id, $uid]);
        jsonResponse(true, 'Notification marked as read.');
    }

    jsonResponse(false, 'Invalid action or missing ID.');
}

jsonResponse(false, 'Method not allowed.', [], 405);