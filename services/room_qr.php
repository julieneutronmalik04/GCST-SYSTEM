<?php
require_once __DIR__ . '/../modules/qr_generator.php';

function roomQrFilename(int $id, string $code): string {
    if ($id < 1 || trim($code) === '') throw new InvalidArgumentException('Room ID and code are required.');
    // Separate namespace from equipment; user-entered room codes never become filesystem paths.
    return 'room-' . $id . '-' . substr(hash('sha256', $code), 0, 20);
}

function saveRoomQr(PDO $pdo, int $id, string $code): string {
    $filename = roomQrFilename($id, $code);
    $path = 'qrcodes/' . $filename . '.png';
    $absolute = __DIR__ . '/../' . $path;
    if (!is_file($absolute) || filesize($absolute) === 0) {
        $path = generateQR($code, $filename);
        if ($path === false) throw new RuntimeException('Unable to generate the room QR image.');
    }
    $pdo->prepare('UPDATE rooms SET qr_code_path = ? WHERE id = ?')->execute([$path, $id]);
    return $path;
}
