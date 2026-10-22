<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/temporary_tables.php';
putenv('SMS_DRIVER=log');
putenv('OVERDUE_DAILY_PENALTY=0');
session_save_path(sys_get_temp_dir());
require_once __DIR__ . '/../config/session.php';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'maintenance';
$pdo = getDB();
createTestTables($pdo, ['equipment', 'borrowers', 'borrowed', 'overdue_sms_log', 'maintenance_notifications',
    'admin_notifications', 'condition_reports', 'equipment_predictions', 'activity_log']);
$pdo->exec("INSERT INTO equipment (id, equip_code, name, qr_string) VALUES (1, 'TEST-EQ', 'Test equipment', 'TEST-QR')");
$pdo->exec("INSERT INTO borrowers (student_faculty_id, first_name, last_name, department_course, email, contact_number)
    VALUES ('TEST-A', 'Actual', 'Borrower', 'Science', 'test@example.invalid', '09123456789')");
$mode = $argv[1] ?? 'create';
if (!in_array($mode, ['create', 'invalid', 'link', 'notify'], true)) throw new InvalidArgumentException('Invalid scenario.');
$today = new DateTimeImmutable($pdo->query('SELECT CURRENT_DATE')->fetchColumn());
$_SERVER['REQUEST_METHOD'] = 'POST';
if ($mode === 'create' || $mode === 'invalid') {
    $_POST = [
        'student_faculty_id' => $mode === 'invalid' ? 'DOES-NOT-EXIST' : 'TEST-A',
        'borrower_name' => 'Spoofed name', 'department' => 'Spoofed department', 'equip_code' => 'TEST-EQ',
        'borrow_date' => $today->format('Y-m-d'), 'time_out' => '08:00',
        'expected_return_date' => $today->modify('+1 day')->format('Y-m-d'), 'expected_return_time' => '17:00',
    ];
    $endpoint = __DIR__ . '/../modules/equipment_borrowing.php';
} else {
    $due = $today->modify('-1 day')->format('Y-m-d');
    $pdo->prepare("INSERT INTO borrowed (id, equipment_id, borrower_name, student_faculty_id,
        borrow_date, time_out, expected_return_date, due_date, expected_return_time, status)
        VALUES (1, 1, 'Legacy name', ?, ?, '08:00', ?, ?, '17:00', 'Overdue')")
        ->execute([$mode === 'notify' ? 'TEST-A' : null, $due, $due, $due]);
    $_POST = ['id' => 1, 'action' => $mode === 'link' ? 'link_borrower' : 'notify', 'student_faculty_id' => 'TEST-A'];
    $endpoint = __DIR__ . '/../modules/overdue.php';
}
ob_start();
register_shutdown_function(function () use ($pdo, $mode): void {
    $output = ob_get_clean();
    session_destroy();
    try {
        $response = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $row = $pdo->query('SELECT * FROM borrowed LIMIT 1')->fetch();
        if ($mode === 'invalid') {
            if ($response['success'] || $row) throw new RuntimeException('Unknown borrowers must not create loans.');
        } elseif (!$response['success']) {
            throw new RuntimeException($response['message'] ?? 'Endpoint failed.');
        } elseif ($mode === 'create') {
            if ($row['student_faculty_id'] !== 'TEST-A' || $row['borrower_name'] !== 'Actual Borrower' || $row['department'] !== 'Science'
                || $row['due_date'] !== $row['expected_return_date'] || $row['status'] !== 'Borrowed') {
                throw new RuntimeException('New loan must retain canonical borrower and due date.');
            }
        } elseif ($mode === 'link') {
            if ($row['student_faculty_id'] !== 'TEST-A' || $row['borrower_name'] !== 'Actual Borrower' || $row['department'] !== 'Science') {
                throw new RuntimeException('Legacy loan link did not retrieve canonical borrower details.');
            }
        } elseif ($mode === 'notify') {
            if (($response['notification']['status'] ?? '') !== 'Simulated' || $pdo->query('SELECT status FROM overdue_sms_log')->fetchColumn() !== 'Simulated') {
                throw new RuntimeException('Preview must be persisted without live delivery.');
            }
        }
        echo "PASS: borrowing API scenario $mode; temporary data only.\n";
    } catch (Throwable $e) {
        fwrite(STDERR, $e->getMessage() . '\nResponse: ' . $output . PHP_EOL);
        exit(1);
    }
});
require $endpoint;
