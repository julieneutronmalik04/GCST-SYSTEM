<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/20261022_reservation_waiting_list.php';
$pdo = getDB();
$role = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
if (str_starts_with($role['Type'], 'enum(') && !str_contains($role['Type'], "'vip'")) {
    $type = substr($role['Type'], 0, -1) . ",'vip')";
    $default = $role['Default'] === null ? '' : ' DEFAULT ' . $pdo->quote($role['Default']);
    $pdo->exec("ALTER TABLE users MODIFY role $type NOT NULL$default");
}
$columns = $pdo->query('SHOW COLUMNS FROM reservations')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('priority', $columns, true)) {
    $pdo->exec("ALTER TABLE reservations ADD priority ENUM('Regular','VIP') NOT NULL DEFAULT 'Regular' AFTER status");
    // One-time backfill; later account role changes do not rewrite request history.
    $pdo->exec("UPDATE reservations r JOIN users u ON u.id = r.faculty_id SET r.priority = 'VIP' WHERE u.role = 'vip'");
}
echo "VIP user role and reservation priority are ready.\n";
