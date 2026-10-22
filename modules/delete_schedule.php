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

    $id = (int) ($body['schedule_id'] ?? $body['id'] ?? 0);
    if (!$id) {
        jsonResponse(false, 'Schedule ID required.');
    }

    $stmt = $pdo->prepare('DELETE FROM class_schedules WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(false, 'Schedule not found.', [], 404);
    }

    logActivity($pdo, 'DELETE_CLASS_SCHEDULE', "schedule:$id");
    jsonResponse(true, 'Schedule deleted.');
} catch (Throwable $e) {
    error_log('[GCST Delete Schedule Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}
