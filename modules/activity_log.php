<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin('admin');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();

if ($method === 'GET') {
    $limit = (int)get('limit', '10');
    
    $stmt = $pdo->prepare(
        "SELECT a.*, u.full_name 
         FROM activity_log a 
         LEFT JOIN users u ON a.user_id = u.id 
         ORDER BY a.created_at DESC 
         LIMIT ?"
    );
    $stmt->execute([$limit]);
    $logs = $stmt->fetchAll();
    
    jsonResponse(true, 'OK', ['logs' => $logs]);
}

jsonResponse(false, 'Method not allowed.', [], 405);