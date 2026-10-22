<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/room_qr.php';
$pdo = getDB();
$columns = $pdo->query('SHOW COLUMNS FROM rooms')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('qr_code_path', $columns, true)) $pdo->exec('ALTER TABLE rooms ADD qr_code_path VARCHAR(255) NULL');
$codeColumn = in_array('room_code', $columns, true) ? 'room_code' : 'room_id';
$rooms = $pdo->query("SELECT id, $codeColumn AS code FROM rooms")->fetchAll();
foreach ($rooms as $room) saveRoomQr($pdo, (int) $room['id'], $room['code']);
echo 'Room QR migration completed; images available for ' . count($rooms) . " rooms.\n";
