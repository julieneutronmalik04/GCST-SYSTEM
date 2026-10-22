<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
$pdo = getDB();
foreach (['equipment', 'borrowed', 'condition_reports'] as $table) {
    $columns[$table] = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_UNIQUE);
}
$type = $columns['equipment']['status']['Type'];
foreach (['For Disposal', 'Disposed'] as $state) {
    if (!str_contains($type, "'$state'")) $type = substr($type, 0, -1) . ",'$state')";
}
$pdo->exec("ALTER TABLE equipment MODIFY status $type NOT NULL DEFAULT 'Available'");
foreach (['equipment', 'borrowed', 'condition_reports'] as $table) {
    if (!isset($columns[$table]['damage_level'])) $pdo->exec("ALTER TABLE $table ADD damage_level ENUM('None','Not Critical','Critical') NOT NULL DEFAULT 'None'");
}
if (!isset($columns['borrowed']['damage_penalty_amount'])) $pdo->exec('ALTER TABLE borrowed ADD damage_penalty_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
if (!isset($columns['condition_reports']['assessed_cost'])) $pdo->exec('ALTER TABLE condition_reports ADD assessed_cost DECIMAL(12,2) NOT NULL DEFAULT 0');
$pdo->exec("CREATE TABLE IF NOT EXISTS damage_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL, borrowed_id INT NULL, condition_report_id INT NOT NULL,
    student_faculty_id VARCHAR(100) NULL,
    damage_level ENUM('Not Critical','Critical') NOT NULL,
    reason TEXT NOT NULL, assessed_cost DECIMAL(12,2) NOT NULL,
    assessed_by INT NULL, assessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_damage_loan (borrowed_id),
    CONSTRAINT fk_damage_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE RESTRICT,
    CONSTRAINT fk_damage_loan FOREIGN KEY (borrowed_id) REFERENCES borrowed(id) ON DELETE RESTRICT,
    CONSTRAINT fk_damage_report FOREIGN KEY (condition_report_id) REFERENCES condition_reports(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE IF NOT EXISTS disposal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY, equipment_id INT NOT NULL, assessment_id INT NOT NULL,
    borrowed_id INT NULL, reason TEXT NOT NULL, assessed_cost DECIMAL(12,2) NOT NULL,
    disposal_status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    requested_by INT NULL, requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_by INT NULL, reviewed_at DATETIME NULL, review_note TEXT NULL,
    UNIQUE KEY uq_disposal_assessment (assessment_id),
    KEY idx_disposal_status (disposal_status, equipment_id),
    CONSTRAINT fk_disposal_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE RESTRICT,
    CONSTRAINT fk_disposal_assessment FOREIGN KEY (assessment_id) REFERENCES damage_assessments(id) ON DELETE RESTRICT,
    CONSTRAINT fk_disposal_loan FOREIGN KEY (borrowed_id) REFERENCES borrowed(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Damage and disposal migration completed.\n";
