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

    function ensureSectionTables(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS school_sections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_name VARCHAR(80) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

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

        $sections = $pdo->query("SELECT DISTINCT year_section FROM class_schedules WHERE year_section <> ''")->fetchAll(PDO::FETCH_COLUMN);
        $insert = $pdo->prepare('INSERT IGNORE INTO school_sections (section_name) VALUES (?)');
        foreach ($sections as $section) {
            $insert->execute([$section]);
        }
    }

    ensureSectionTables($pdo);

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT s.id, s.section_name, COUNT(cs.id) AS schedule_count
             FROM school_sections s
             LEFT JOIN class_schedules cs ON cs.year_section = s.section_name
             GROUP BY s.id, s.section_name
             ORDER BY s.section_name ASC'
        );
        jsonResponse(true, 'OK', ['sections' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $body = $_POST;
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
        }

        $action = trim((string) ($body['action'] ?? 'add'));
        $id = (int) ($body['id'] ?? 0);
        $sectionName = trim((string) ($body['section_name'] ?? ''));

        if ($action === 'add') {
            if ($sectionName === '') {
                jsonResponse(false, 'Section name is required.');
            }
            try {
                $stmt = $pdo->prepare('INSERT INTO school_sections (section_name) VALUES (?)');
                $stmt->execute([$sectionName]);
            } catch (PDOException $e) {
                jsonResponse(false, 'Section already exists.');
            }
            logActivity($pdo, 'ADD_SECTION', "section:$sectionName");
            jsonResponse(true, 'Section added.');
        }

        if ($action === 'update') {
            if (!$id || $sectionName === '') {
                jsonResponse(false, 'Section ID and name are required.');
            }

            $oldStmt = $pdo->prepare('SELECT section_name FROM school_sections WHERE id = ? LIMIT 1');
            $oldStmt->execute([$id]);
            $oldName = (string) ($oldStmt->fetchColumn() ?: '');
            if ($oldName === '') {
                jsonResponse(false, 'Section not found.', [], 404);
            }

            try {
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE school_sections SET section_name = ? WHERE id = ?')->execute([$sectionName, $id]);
                $pdo->prepare('UPDATE class_schedules SET year_section = ? WHERE year_section = ?')->execute([$sectionName, $oldName]);
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                jsonResponse(false, 'Unable to update section. It may already exist.');
            }

            logActivity($pdo, 'UPDATE_SECTION', "section:$id", "$oldName to $sectionName");
            jsonResponse(true, 'Section updated.');
        }

        if ($action === 'delete') {
            if (!$id) {
                jsonResponse(false, 'Section ID is required.');
            }

            $stmt = $pdo->prepare('SELECT section_name FROM school_sections WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $sectionName = (string) ($stmt->fetchColumn() ?: '');
            if ($sectionName === '') {
                jsonResponse(false, 'Section not found.', [], 404);
            }

            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM class_schedules WHERE year_section = ?')->execute([$sectionName]);
            $pdo->prepare('DELETE FROM school_sections WHERE id = ?')->execute([$id]);
            $pdo->commit();

            logActivity($pdo, 'DELETE_SECTION', "section:$sectionName");
            jsonResponse(true, 'Section deleted. Associated class schedules were removed.');
        }

        jsonResponse(false, 'Invalid section action.');
    }

    jsonResponse(false, 'Method not allowed.', [], 405);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[GCST Manage Sections Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}
