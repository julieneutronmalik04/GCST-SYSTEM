<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/overdue.php';
require_once __DIR__ . '/temporary_tables.php';
putenv('SMS_DRIVER=log');
putenv('OVERDUE_DAILY_PENALTY=2.50');
$pdo = getDB();
// Connection-local shadow tables: no application records are edited or sent SMS.
createTestTables($pdo, ['equipment', 'borrowers', 'borrowed', 'overdue_sms_log', 'maintenance_notifications', 'admin_notifications']);
$checks = 0;
function check(bool $condition, string $description): void {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $description);
    ++$checks;
}
$pdo->exec("INSERT INTO equipment (id, equip_code, name, qr_string) VALUES (1, 'TEST-EQ', 'Test equipment', 'TEST-QR')");
$pdo->exec("INSERT INTO borrowers (student_faculty_id, first_name, last_name, department_course, email, contact_number) VALUES
    ('TEST-A', 'Test', 'Borrower', 'Science', 'a@example.invalid', '09123456789'),
    ('TEST-B', 'Test', 'Borrower', 'Faculty', 'b@example.invalid', '09987654321'),
    ('TEST-C', 'Missing', 'Phone', 'Science', 'c@example.invalid', '')");
$today = new DateTimeImmutable($pdo->query('SELECT CURRENT_DATE')->fetchColumn());
function loan(int $id, int $offset, string $status, ?string $studentId = 'TEST-A', float $penalty = 0): void {
    global $pdo, $today;
    $due = $today->modify(sprintf('%+d days', $offset))->format('Y-m-d');
    $pdo->prepare('INSERT INTO borrowed (id, equipment_id, borrower_name, student_faculty_id, department,
        borrow_date, time_out, expected_return_date, due_date, expected_return_time, status, penalty_amount)
        VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
        $id, 'Test Borrower', $studentId, 'Science', $today->modify('-20 days')->format('Y-m-d'),
        '08:00:00', $due, $due, '17:00:00', $status, $penalty,
    ]);
}
loan(1, -3, 'Borrowed');
loan(2, 0, 'Borrowed');
loan(3, 3, 'Borrowed');
loan(4, -10, 'Returned', 'TEST-A', 17);
loan(5, -10, 'Returned (Damaged)', 'TEST-A', 19);
loan(6, -2, 'Overdue', 'TEST-B');
loan(7, -1, 'Borrowed', null);
loan(8, -1, 'Borrowed', 'TEST-C');
$first = refreshOverdue($pdo);
check($first['newly_overdue'] === 3, 'only past-due borrowed records transition');
$states = $pdo->query('SELECT id, status, penalty_amount FROM borrowed')->fetchAll(PDO::FETCH_UNIQUE);
check($states[1]['status'] === 'Overdue' && (float) $states[1]['penalty_amount'] === 7.5, 'three overdue days accrue 7.50');
check($states[2]['status'] === 'Borrowed' && $states[3]['status'] === 'Borrowed', 'today and future loans stay borrowed');
check($states[4]['status'] === 'Returned' && (float) $states[4]['penalty_amount'] === 17.0, 'returned loans retain status and penalty');
check($states[5]['status'] === 'Returned (Damaged)' && (float) $states[5]['penalty_amount'] === 19.0, 'damaged returns remain intact');
check((float) $states[6]['penalty_amount'] === 5.0, 'already-overdue penalties refresh');
check((int) $pdo->query('SELECT COUNT(*) FROM maintenance_notifications')->fetchColumn() === 3, 'maintenance alerts created');
check((int) $pdo->query('SELECT COUNT(*) FROM admin_notifications')->fetchColumn() === 3, 'admin alerts created');
check(refreshOverdue($pdo)['newly_overdue'] === 0, 'checker is idempotent');
check((int) $pdo->query('SELECT COUNT(*) FROM maintenance_notifications')->fetchColumn() === 3, 'no duplicate bell notifications');
$rows = getOverdueRecords($pdo);
check(count($rows) === 4, 'overdue list excludes returned, today, and future loans');
$byId = array_column($rows, null, 'id');
check($byId[1]['contact_number'] === '09123456789' && $byId[6]['contact_number'] === '09987654321', 'contacts join by ID despite duplicate names');
check($byId[7]['contact_number'] === null, 'legacy unlinked loans remain visible');
check(sendOverdueSms('09123456789', 'Preview only')['status'] === 'Simulated', 'default driver simulates SMS');
check(sendOverdueSms('09123456789,09987654321', 'No bulk injection')['status'] === 'Skipped', 'multiple/invalid recipients rejected');
$preview = notifyOverdueBorrower($pdo, 1);
check($preview['status'] === 'Simulated' && !$preview['duplicate'], 'preview logged without delivery');
$stored = $pdo->query('SELECT * FROM overdue_sms_log WHERE borrowed_id = 1')->fetch();
check($stored['message'] === 'Hello Test Borrower, your borrowed equipment TEST-EQ is overdue. Please return it immediately to avoid penalties.', 'warning message has correct borrower and equipment');
check(notifyOverdueBorrower($pdo, 1)['duplicate'], 'automatic repeated preview is deduplicated');
$sent = 0;
$accept = function ($phone, $message) use (&$sent): array {
    ++$sent;
    return ['status' => 'Accepted', 'provider_reference' => 'fake-provider-id'];
};
check(notifyOverdueBorrower($pdo, 1, true, $accept)['status'] === 'Accepted', 'explicit retry can promote preview to provider acceptance');
check(notifyOverdueBorrower($pdo, 1, true, $accept)['duplicate'] && $sent === 1, 'accepted reminders cannot be resent the same day');
check(notifyOverdueBorrower($pdo, 7)['status'] === 'Skipped', 'missing borrower contact is skipped');
check(notifyOverdueBorrower($pdo, 8)['status'] === 'Skipped', 'empty phone is skipped');
$unknown = fn() => ['status' => 'Unknown', 'error_message' => 'Simulated timeout'];
check(notifyOverdueBorrower($pdo, 6, false, $unknown)['status'] === 'Unknown', 'uncertain provider result is recorded');
check(notifyOverdueBorrower($pdo, 6, true, $accept)['duplicate'] && $sent === 1, 'uncertain requests cannot be blindly resent');
try {
    notifyOverdueBorrower($pdo, 4, false, $accept);
    check(false, 'returned loan should reject reminders');
} catch (InvalidArgumentException $e) {
    check(true, 'returned loans cannot receive reminders');
}
$pdo->exec("UPDATE overdue_sms_log SET notification_date = CURRENT_DATE - INTERVAL 1 DAY, status = 'Accepted' WHERE borrowed_id = 6");
check(notifyOverdueBorrower($pdo, 6, true, $accept)['status'] === 'Accepted' && $sent === 2, 'new calendar day permits one new reminder');
$pdo->exec("UPDATE borrowed SET status = 'Returned' WHERE id = 1");
putenv('OVERDUE_DAILY_PENALTY=5');
refreshOverdue($pdo);
check((float) $pdo->query('SELECT penalty_amount FROM borrowed WHERE id = 1')->fetchColumn() === 7.5, 'return freezes accrued penalty');
loan(9, -1, 'Overdue');
check((dispatchOverdueNotifications($pdo)['Simulated'] ?? 0) === 1, 'scheduled dispatch processes outstanding loans');
check(dispatchOverdueNotifications($pdo) === [], 'scheduled repeat does not resend handled warnings');
putenv('OVERDUE_DAILY_PENALTY=0');
refreshOverdue($pdo);
check((float) $pdo->query('SELECT penalty_amount FROM borrowed WHERE id = 9')->fetchColumn() === 0.0, 'zero configured rate creates no penalty');
echo "PASS: $checks overdue tracking checks; temporary data only; no external SMS sent.\n";
