<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin('admin');
header('Content-Type: application/json');

$pdo = getDB();

try {
    $availRooms = $pdo->query("
        SELECT COUNT(*) FROM rooms r 
        WHERE r.status = 'Available' 
          AND NOT EXISTS (
            SELECT 1 FROM reservations res 
            WHERE res.room_id = r.id AND res.status = 'Approved' 
              AND res.res_date = CURDATE() 
              AND CURTIME() BETWEEN res.time_start AND res.time_end
          )
    ")->fetchColumn();
    
    $totalRes = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    
    $availEquip = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'Available'")->fetchColumn();
    
    $borrowedEquip = $pdo->query("SELECT COUNT(*) FROM borrowed WHERE status IN ('Borrowed', 'Overdue')")->fetchColumn();
    
    jsonResponse(true, 'OK', [
        'stats' => [
            'available_rooms' => (int)$availRooms,
            'total_reservations' => (int)$totalRes,
            'available_equipment' => (int)$availEquip,
            'borrowed_equipment' => (int)$borrowedEquip
        ]
    ]);
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
