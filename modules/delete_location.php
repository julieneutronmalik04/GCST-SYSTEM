<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../config/session.php';

    requireLogin('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed.', [], 405);
    }

    $pdo = getDB();
    $body = $_POST;
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
    }

    $id = (int) ($body['id'] ?? 0);
    $locationName = trim((string) ($body['location_name'] ?? ''));

    if (!$id && $locationName === '') {
        jsonResponse(false, 'Location ID or name is required.');
    }

    if ($id) {
        $stmt = $pdo->prepare('SELECT location_name FROM locations WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $locationName = (string) ($stmt->fetchColumn() ?: '');
    }

    if ($locationName === '') {
        jsonResponse(false, 'Location not found.', [], 404);
    }

    $roomCols = $pdo->query('SHOW COLUMNS FROM rooms')->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('location', $roomCols, true)) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE location = ?');
        $check->execute([$locationName]);
        if ((int) $check->fetchColumn() > 0) {
            jsonResponse(false, 'Cannot delete location. It is currently linked to existing rooms.');
        }
    }

    $delete = $id
        ? $pdo->prepare('DELETE FROM locations WHERE id = ?')
        : $pdo->prepare('DELETE FROM locations WHERE location_name = ?');
    $delete->execute([$id ?: $locationName]);

    if ($delete->rowCount() === 0) {
        jsonResponse(false, 'Location not found.', [], 404);
    }

    logActivity($pdo, 'DELETE_LOCATION', "location:$locationName");
    jsonResponse(true, 'Location deleted.');
} catch (Throwable $e) {
    error_log('[GCST Delete Location Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}
