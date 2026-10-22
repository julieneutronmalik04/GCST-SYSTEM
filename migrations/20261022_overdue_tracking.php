<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

// Rerunnable migration. No existing borrowing or borrower records are deleted.
$columns = $pdo->query('SHOW COLUMNS FROM borrowed')->fetchAll(PDO::FETCH_UNIQUE);
$borrowerColumn = $pdo->query("SHOW FULL COLUMNS FROM borrowers LIKE 'student_faculty_id'")->fetch();
if (!$borrowerColumn) throw new RuntimeException('Create the borrowers table before this migration.');
$type = $borrowerColumn['Type'];
$collation = $borrowerColumn['Collation'];
if (!preg_match('/^varchar\(\d+\)$/i', $type) || !preg_match('/^[a-z0-9_]+$/i', $collation)) {
    throw new RuntimeException('Expected a VARCHAR student_faculty_id with a text collation.');
}
$charset = explode('_', $collation)[0];
if (preg_match('/^varchar\((\d+)\)$/i', $columns['borrower_name']['Type'], $length) && (int) $length[1] < 201) {
    $pdo->exec('ALTER TABLE borrowed MODIFY borrower_name VARCHAR(201) NOT NULL');
}
if (preg_match('/^varchar\((\d+)\)$/i', $columns['department']['Type'], $length) && (int) $length[1] < 150) {
    $pdo->exec('ALTER TABLE borrowed MODIFY department VARCHAR(150) NULL');
}
if (!isset($columns['student_faculty_id'])) {
    $pdo->exec("ALTER TABLE borrowed ADD student_faculty_id $type CHARACTER SET $charset COLLATE $collation NULL AFTER borrower_name");
}
if (!isset($columns['due_date'])) {
    $pdo->exec('ALTER TABLE borrowed ADD due_date DATE NULL AFTER expected_return_date');
}
if (!isset($columns['penalty_amount'])) {
    $pdo->exec('ALTER TABLE borrowed ADD penalty_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
}
$statusType = $columns['status']['Type'] ?? '';
if (stripos($statusType, "'Overdue'") === false) {
    if (stripos($statusType, 'enum(') !== 0) throw new RuntimeException('Review the existing status column before migrating.');
    $extended = substr($statusType, 0, -1) . ",'Overdue')";
    $pdo->exec("ALTER TABLE borrowed MODIFY status $extended NOT NULL DEFAULT 'Borrowed'");
}
$pdo->exec('UPDATE borrowed SET due_date = expected_return_date WHERE due_date IS NULL');
if (!isset($columns['due_date']) || $columns['due_date']['Null'] === 'YES') {
    $pdo->exec('ALTER TABLE borrowed MODIFY due_date DATE NOT NULL');
}

// Only unambiguous full-name matches can safely identify historical records.
$pdo->exec("UPDATE borrowed b
    JOIN (SELECT MIN(student_faculty_id) AS student_faculty_id,
                 LOWER(TRIM(CONCAT(first_name, ' ', last_name))) AS full_name
          FROM borrowers GROUP BY full_name HAVING COUNT(*) = 1) r
      ON LOWER(TRIM(b.borrower_name)) COLLATE utf8mb4_unicode_ci = r.full_name COLLATE utf8mb4_unicode_ci
    SET b.student_faculty_id = r.student_faculty_id WHERE b.student_faculty_id IS NULL");
$constraint = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'borrowed'
      AND CONSTRAINT_NAME = 'fk_borrowed_registered_borrower'")->fetchColumn();
if (!$constraint) {
    $pdo->exec('ALTER TABLE borrowed ADD CONSTRAINT fk_borrowed_registered_borrower
        FOREIGN KEY (student_faculty_id) REFERENCES borrowers(student_faculty_id)
        ON UPDATE CASCADE ON DELETE SET NULL');
}
$indexes = $pdo->query("SHOW INDEX FROM borrowed WHERE Key_name = 'idx_borrowed_due_status'")->fetchAll();
if (!$indexes) $pdo->exec('ALTER TABLE borrowed ADD INDEX idx_borrowed_due_status (status, due_date)');

$pdo->exec("CREATE TABLE IF NOT EXISTS overdue_sms_log (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    borrowed_id INT NOT NULL,
    notification_date DATE NOT NULL,
    contact_number VARCHAR(50) NULL,
    message TEXT NOT NULL,
    status ENUM('Pending','Processing','Simulated','Accepted','Failed','Skipped','Unknown') NOT NULL DEFAULT 'Pending',
    provider_reference VARCHAR(100) NULL,
    error_message VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    UNIQUE KEY uq_overdue_sms_day (borrowed_id, notification_date),
    CONSTRAINT fk_overdue_sms_borrowed FOREIGN KEY (borrowed_id) REFERENCES borrowed(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Overdue tracking migration completed.\n";
