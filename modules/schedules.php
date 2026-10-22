<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../config/session.php';

    requireLogin('admin');

    $pdo = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    function ensureClassSchedulesTable(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS class_schedules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                year_section VARCHAR(80) NOT NULL,
                subject_name VARCHAR(160) NOT NULL,
                day_of_week VARCHAR(3) NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                room_code VARCHAR(50) NOT NULL,
                room_name VARCHAR(160) NULL,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_schedule_lookup (room_code, day_of_week, start_time, end_time),
                INDEX idx_schedule_section (year_section)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    function normalizeScheduleDays($raw): array
    {
        if (is_array($raw)) {
            $days = $raw;
        } else {
            $days = preg_split('/\s*,\s*/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);
        }
        $allowed = ['M', 'T', 'W', 'Th', 'F', 'S'];
        return array_values(array_intersect($allowed, $days));
    }

    ensureClassSchedulesTable($pdo);

    if ($method === 'GET') {
        $section = get('year_section');
        $dayFilter = get('day_of_week');
        $startFilter = get('start_time');
        $endFilter = get('end_time');
        $sectionsOnly = get('sections');

        if ($sectionsOnly) {
            $stmt = $pdo->query("SELECT DISTINCT year_section FROM class_schedules ORDER BY year_section ASC");
            jsonResponse(true, 'OK', ['sections' => array_column($stmt->fetchAll(), 'year_section')]);
        }

        $slotFilterActive = $dayFilter !== '' || ($startFilter !== '' && $endFilter !== '');
        $sql = 'SELECT * FROM class_schedules';
        $params = [];

        $where = [];
        if ($section !== '' && !$slotFilterActive) {
            $where[] = 'year_section = ?';
            $params[] = $section;
        }
        if ($dayFilter !== '') {
            $where[] = 'day_of_week = ?';
            $params[] = $dayFilter;
        }
        if ($startFilter !== '' && $endFilter !== '') {
            $where[] = 'start_time < ? AND end_time > ?';
            array_push($params, $endFilter, $startFilter);
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY FIELD(day_of_week, 'M', 'T', 'W', 'Th', 'F', 'S'), start_time ASC, year_section ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(true, 'OK', ['schedules' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $body = $_POST;
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
        }

        $yearSection = trim((string) ($body['year_section'] ?? ''));
        $subjectName = trim((string) ($body['subject_name'] ?? ''));
        $days = normalizeScheduleDays($body['days'] ?? []);
        $startTime = trim((string) ($body['start_time'] ?? ''));
        $endTime = trim((string) ($body['end_time'] ?? ''));
        $roomCode = trim((string) ($body['room_code'] ?? ''));
        $roomName = trim((string) ($body['room_name'] ?? ''));

        if (!$yearSection || !$subjectName || !$days || !$startTime || !$endTime || !$roomCode) {
            jsonResponse(false, 'Year & Section, subject, day, time, and room are required.');
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
            'INSERT INTO class_schedules (year_section, subject_name, day_of_week, start_time, end_time, room_code, room_name, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($days as $day) {
            $stmt->execute([$yearSection, $subjectName, $day, $startTime, $endTime, $roomCode, $roomName ?: null, getUserId() ?: null]);
        }

        logActivity($pdo, 'ADD_CLASS_SCHEDULE', "schedule:$yearSection", "$subjectName in $roomCode");
        jsonResponse(true, 'Schedule saved.');
    }

    if ($method === 'DELETE') {
        parse_str(file_get_contents('php://input'), $body);
        $id = (int) ($body['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, 'Schedule ID required.');
        }
        $pdo->prepare('DELETE FROM class_schedules WHERE id = ?')->execute([$id]);
        logActivity($pdo, 'DELETE_CLASS_SCHEDULE', "schedule:$id");
        jsonResponse(true, 'Schedule deleted.');
    }

    jsonResponse(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) {
    error_log('[GCST Schedules Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}
