<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/temporary_tables.php';
session_save_path(sys_get_temp_dir());
require_once __DIR__ . '/../config/session.php';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = ($argv[2] ?? '') === 'maintenance' ? 'maintenance' : 'admin';
register_shutdown_function(fn() => session_destroy());
$pdo = getDB();
createTestTables($pdo, ['equipment', 'borrowers', 'borrowed', 'condition_reports', 'damage_assessments', 'disposal_requests']);
$pdo->exec("INSERT INTO equipment(id,equip_code,name,qr_string,status) VALUES (1,'APPROVED-EQ','Disposed test asset','QR-TEST','Disposed'),(2,'PENDING-EQ','Pending asset','QR-PENDING','For Disposal')");
$pdo->exec("INSERT INTO borrowed(id,equipment_id,borrower_name,student_faculty_id,borrow_date,time_out,expected_return_date,due_date,expected_return_time,status,penalty_amount,damage_penalty_amount)
    VALUES(1,1,'Test Borrower','TEST-ID',CURRENT_DATE,'08:00',CURRENT_DATE,CURRENT_DATE,'17:00','Returned (Damaged)',7.50,1200)");
$pdo->exec("INSERT INTO condition_reports(id,report_code,equipment_id,issue,reported_by,damage_level,assessed_cost) VALUES(1,'TEST-REPORT',1,'Unsafe equipment',1,'Critical',1200)");
$reason = '=Unsafe motor and broken chassis require disposal. ' . str_repeat('Critical safety damage observed during inspection. ', 6) . 'END-OF-REASON';
$pdo->prepare("INSERT INTO damage_assessments(id,equipment_id,borrowed_id,condition_report_id,student_faculty_id,damage_level,reason,assessed_cost) VALUES(1,1,1,1,'TEST-ID','Critical',?,1200)")->execute([$reason]);
$pdo->prepare("INSERT INTO disposal_requests(id,equipment_id,assessment_id,borrowed_id,reason,assessed_cost,disposal_status,reviewed_by,reviewed_at,review_note)
    VALUES(1,1,1,1,?,1200,'Approved',1,NOW(),'Approved after inspection')")->execute([$reason]);
$pdo->exec("INSERT INTO disposal_requests(id,equipment_id,assessment_id,reason,assessed_cost,disposal_status) VALUES(2,2,2,'Should not be exported',50,'Pending')");
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['type'=>'disposal','format'=>$argv[1] ?? 'csv'];
if (($argv[2] ?? '') === 'future') $_GET['from'] = '2999-01-01';
require __DIR__ . '/../modules/report_export.php';
