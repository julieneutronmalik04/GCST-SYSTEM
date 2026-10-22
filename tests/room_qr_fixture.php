<?php
// Isolated integration router. Never accessible through Apache or without the test token.
if (PHP_SAPI !== 'cli-server' || !getenv('ROOM_QR_TEST_TOKEN') || !hash_equals(getenv('ROOM_QR_TEST_TOKEN'), $_SERVER['HTTP_X_TEST_TOKEN'] ?? '')) {
    http_response_code(404); exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../services/room_qr.php';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = $_SERVER['HTTP_X_TEST_ROLE'] ?? 'admin';
$pdo = getDB();
foreach (['rooms', 'reservations', 'equipment', 'activity_log'] as $table) {
    $ddl = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM)[1];
    $ddl = preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl);
    $ddl = preg_replace('/^\s*CONSTRAINT .*\n?/m', '', $ddl);
    $ddl = preg_replace('/,\s*\) ENGINE/', "\n) ENGINE", $ddl);
    $pdo->exec($ddl);
}
$directory = realpath(__DIR__ . '/../qrcodes');
$before = glob($directory . '/room-9000000*.png');
$code = 'T-' . substr(getenv('ROOM_QR_TEST_TOKEN'), 0, 6) . '-A';
$pdo->prepare("INSERT INTO rooms(id,room_code,room_name,type,capacity,status) VALUES(900000001,?,'Original room','Classroom',30,'Available')")->execute([$code]);
$pdo->prepare("INSERT INTO equipment(id,equip_code,name,qr_string,assigned_room) VALUES(1,'TEST-EQ','Fixture asset','TEST-QR',?)")->execute([$code]);
if ($_SERVER['REQUEST_METHOD'] === 'PUT') saveRoomQr($pdo, 900000001, $code);
register_shutdown_function(function () use ($pdo, $directory, $before): void {
    $files = array_values(array_diff(glob($directory . '/room-9000000*.png'), $before));
    $images = [];
    foreach ($files as $file) $images[basename($file)] = base64_encode(file_get_contents($file));
    file_put_contents(getenv('ROOM_QR_TEST_STATE'), json_encode([
        'rooms' => $pdo->query('SELECT id,room_code,room_name,qr_code_path FROM rooms ORDER BY id')->fetchAll(),
        'assigned_room' => $pdo->query('SELECT assigned_room FROM equipment WHERE id=1')->fetchColumn(),
        'images' => $images,
    ]));
    foreach ($files as $file) {
        if (dirname(realpath($file)) === $directory && preg_match('/^room-9000000\d+-[a-f0-9]{20}\.png$/', basename($file))) unlink($file);
    }
    session_destroy();
});
require __DIR__ . '/../modules/rooms.php';
