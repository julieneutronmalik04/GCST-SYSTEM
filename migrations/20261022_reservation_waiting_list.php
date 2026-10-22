<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
$pdo = getDB();
$column = $pdo->query("SHOW COLUMNS FROM reservations LIKE 'status'")->fetch();
if (str_starts_with($column['Type'], 'enum(') && !str_contains($column['Type'], "'Waiting List'")) {
    // Preserve any existing status values, including legacy Archived/Completed.
    $type = substr($column['Type'], 0, -1) . ",'Waiting List')";
    $pdo->exec("ALTER TABLE reservations MODIFY status $type NOT NULL DEFAULT 'Pending'");
}
echo "Reservation Waiting List status is ready.\n";
