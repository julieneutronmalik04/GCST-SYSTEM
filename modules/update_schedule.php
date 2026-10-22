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
    $yearSection = trim((string) ($body['year_section'] ?? ''));
    $subjectName = trim((string) ($body['subject_name'] ?? ''));
    $day = trim((string) ($body['day_of_week'] ?? $body['day'] ?? ''));
    $startTime = trim((string) ($body['start_time'] ?? ''));
    $endTime = trim((string) ($body['end_time'] ?? ''));
    $roomCode = trim((string) ($body['room_code'] ?? ''));
    $roomName = trim((string) ($body['room_name'] ?? ''));
    $allowedDays = ['M', 'T', 'W', 'Th', 'F', 'S'];

    if (!$id || !$yearSection || !$subjectName || !$day || !$startTime || !$endTime || !$roomCode) {
        jsonResponse(false, 'Schedule ID, section, subject, day, time, and room are required.');
    }
    if (!in_array($day, $allowedDays, true)) {
        jsonResponse(false, 'Invalid day selected.');
    }
    if ($startTime >= $endTime) {
        jsonResponse(false, 'End time must be after start time.');
    }

    if ($roomName === '') {
        $roomCols = $pdo->query("SHOW COLUMNS FROM rooms")->fetchAll(PDO::FETCH_COLUMN);
        $roomCodeCol = in_array('room_code', $roomCols, true) ? 'room_code' : 'room_id';
        $roomStmt = $pdo->prepare("SELECT room_name FROM rooms WHERE $roomCodeCol = ? LIMIT 1");
        $roomStmt->execute([$roomCode]);
        $roomName = (string) ($roomStmt->fetchColumn() ?: '');
    }

    $stmt = $pdo->prepare(
        'UPDATE class_schedules
         SET year_section = ?, subject_name = ?, day_of_week = ?, start_time = ?, end_time = ?, room_code = ?, room_name = ?
         WHERE id = ?'
    );
    $stmt->execute([$yearSection, $subjectName, $day, $startTime, $endTime, $roomCode, $roomName ?: null, $id]);

    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare('SELECT id FROM class_schedules WHERE id = ? LIMIT 1');
        $exists->execute([$id]);
        if (!$exists->fetch()) {
            jsonResponse(false, 'Schedule not found.', [], 404);
        }
    }

    logActivity($pdo, 'UPDATE_CLASS_SCHEDULE', "schedule:$id", "$subjectName in $roomCode");
    jsonResponse(true, 'Schedule updated.');
} catch (Throwable $e) {
    error_log('[GCST Update Schedule Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}
