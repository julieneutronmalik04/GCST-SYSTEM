<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../services/overdue.php';
requireLogin(['admin', 'maintenance']);
$pdo = getDB();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $check = refreshOverdue($pdo);
        jsonResponse(true, 'OK', $check + [
            'overdue' => getOverdueRecords($pdo),
            'sms_mode' => overdueConfig()['sms_driver'] === 'log' ? 'preview' : 'live',
        ]);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if (!$id) jsonResponse(false, 'A borrowing ID is required.', [], 422);
        refreshOverdue($pdo);
        if ($action === 'notify') {
            $result = notifyOverdueBorrower($pdo, $id, true);
            $messages = [
                'Simulated' => 'SMS preview recorded. No message was sent.',
                'Accepted' => 'The SMS provider accepted the reminder. Delivery is not yet confirmed.',
                'Processing' => 'A reminder is already being processed.',
                'Unknown' => 'The SMS result is uncertain. Check the provider before retrying.',
            ];
            $message = !empty($result['duplicate']) ? 'A reminder has already been handled today: ' . $result['status'] . '.'
                : ($messages[$result['status']] ?? $result['error_message'] ?? 'Reminder processed.');
            jsonResponse(!in_array($result['status'], ['Failed', 'Skipped', 'Unknown'], true), $message, ['notification' => $result]);
        }
        if ($action === 'link_borrower') {
            $studentId = trim((string) ($_POST['student_faculty_id'] ?? ''));
            $stmt = $pdo->prepare('SELECT student_faculty_id, first_name, last_name, department_course FROM borrowers WHERE student_faculty_id = ?');
            $stmt->execute([$studentId]);
            $borrower = $stmt->fetch();
            if (!$borrower) jsonResponse(false, 'No registered borrower has this ID.', [], 422);
            $stmt = $pdo->prepare("UPDATE borrowed SET student_faculty_id = ?, borrower_name = ?, department = ?
                WHERE id = ? AND status = 'Overdue' AND student_faculty_id IS NULL");
            $stmt->execute([$borrower['student_faculty_id'], trim($borrower['first_name'] . ' ' . $borrower['last_name']), $borrower['department_course'], $id]);
            if (!$stmt->rowCount()) jsonResponse(false, 'The overdue loan was not found or is already linked.', [], 409);
            logActivity($pdo, 'LINK_BORROWER', 'borrow:' . $id, 'Registered borrower ID: ' . $borrower['student_faculty_id']);
            jsonResponse(true, 'Registered borrower linked.');
        }
        jsonResponse(false, 'Invalid action.', [], 422);
    }
    jsonResponse(false, 'Method not allowed.', [], 405);
} catch (InvalidArgumentException $e) {
    jsonResponse(false, $e->getMessage(), [], 422);
} catch (Throwable $e) {
    error_log('Overdue module error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to process overdue records. Check that the overdue database migration has been run.', [], 500);
}
