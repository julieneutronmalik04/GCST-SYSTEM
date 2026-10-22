<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/damage_disposal.php';
require_once __DIR__ . '/temporary_tables.php';
$pdo = getDB();
createTestTables($pdo, ['equipment', 'borrowers', 'borrowed', 'condition_reports', 'damage_assessments', 'disposal_requests', 'admin_notifications', 'maintenance_notifications']);
$count = 0;
function verify(bool $value, string $label): void { global $count; if (!$value) throw new RuntimeException('FAIL: ' . $label); ++$count; }
function rejects(callable $fn, string $label): void {
    try { $fn(); } catch (InvalidArgumentException|DomainException $e) { verify(true, $label); return; }
    verify(false, $label);
}
$pdo->exec("INSERT INTO borrowers (student_faculty_id, first_name, last_name, department_course, email, contact_number)
    VALUES ('TEST-A', 'Test', 'Borrower', 'Science', 'test@example.invalid', '09123456789')");
for ($id = 1; $id <= 9; ++$id) {
    $pdo->prepare("INSERT INTO equipment (id,equip_code,name,qr_string,status) VALUES (?, ?, 'Test item', ?, 'In Use')")->execute([$id, 'TEST-' . $id, 'QR-' . $id]);
    $pdo->prepare("INSERT INTO borrowed (id,equipment_id,borrower_name,student_faculty_id,borrow_date,time_out,expected_return_date,due_date,expected_return_time,status,penalty_amount)
        VALUES (?,?,'Test Borrower',?,CURRENT_DATE - INTERVAL 2 DAY,'08:00',CURRENT_DATE - INTERVAL 1 DAY,CURRENT_DATE - INTERVAL 1 DAY,'17:00','Overdue',7.50)")
        ->execute([$id, $id, $id === 7 ? null : 'TEST-A']);
}
function value(string $table, int $id, string $field) { global $pdo; return $pdo->query("SELECT $field FROM $table WHERE id=$id")->fetchColumn(); }
function inspect(int $id, string $level, string $cost): array {
    global $pdo;
    $pdo->beginTransaction();
    try { $result = assessEquipmentDamage($pdo, $id, $level, 'Inspection damage', $cost, 1); $pdo->commit(); return $result; }
    catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
verify(damageCost('12.5') === '12.50', 'money normalizes to two decimals');
foreach (['-1', '', '1e3', '1.234', '10000000000'] as $bad) rejects(fn() => damageCost($bad), 'invalid monetary input rejected');
$repair = processEquipmentReturn($pdo, 1, 'damaged', ['damage_level'=>'Not Critical','reason'=>'Broken cable','assessed_cost'=>'125.50'], 1);
verify($repair['equipment_status'] === 'Under Repair', 'not-critical returns go to standard repair');
verify(value('borrowed',1,'status') === 'Returned (Damaged)' && (float)value('borrowed',1,'damage_penalty_amount') === 125.5, 'fee attaches to return transaction');
verify((float)value('borrowed',1,'penalty_amount') === 7.5, 'overdue penalty remains separate');
verify(value('borrowed',1,'student_faculty_id') === 'TEST-A', 'fee linked to registered borrower');
verify((int)$pdo->query('SELECT COUNT(*) FROM disposal_requests')->fetchColumn() === 0, 'repair creates no disposal request');
rejects(fn()=>processEquipmentReturn($pdo,1,'damaged',['damage_level'=>'Critical','reason'=>'Duplicate','assessed_cost'=>'900'],1), 'repeated return cannot double-charge');
$critical = processEquipmentReturn($pdo,2,'damaged',['damage_level'=>'Critical','reason'=>'Unsafe broken frame','assessed_cost'=>'1200'],1);
$requestId = (int)$critical['disposal_request_id'];
verify($critical['equipment_status'] === 'For Disposal' && value('disposal_requests',$requestId,'disposal_status') === 'Pending', 'critical damage flags pending disposal');
rejects(fn()=>reviewDisposal($pdo,$requestId,'Approved','',1,'maintenance'), 'maintenance cannot approve');
rejects(fn()=>reviewDisposal($pdo,$requestId,'Rejected','',1,'admin'), 'rejection requires a reason');
reviewDisposal($pdo,$requestId,'Approved','Unsafe to repair',1,'admin');
verify(value('equipment',2,'status') === 'Disposed', 'admin approval disposes item');
verify((int)$pdo->query("SELECT COUNT(*) FROM condition_reports WHERE equipment_id=2 AND status <> 'Resolved'")->fetchColumn() === 0, 'approved disposal closes its condition reports');
verify(value('disposal_requests',$requestId,'reviewed_at') !== null, 'disposal date retained');
rejects(fn()=>reviewDisposal($pdo,$requestId,'Rejected','Changed mind',1,'admin'), 'approved decision cannot be changed');
rejects(fn()=>assertEquipmentStatusEdit($pdo,2,'Available'), 'generic edits cannot reactivate disposed equipment');
$approved = disposalRecords($pdo);
verify(count($approved) === 1 && (float)$approved[0]['total_penalty_amount'] === 1207.5, 'approved report includes damage and overdue fee totals');
verify($approved[0]['student_faculty_id'] === 'TEST-A' && $approved[0]['reason'] === 'Unsafe broken frame', 'report retains borrower and reason');
verify(disposalRecords($pdo,'Approved','2999-01-01') === [], 'report filters use disposal approval date');
inspect(3,'Not Critical','50');
$recorded = processEquipmentReturn($pdo,3,'good',[],1);
verify($recorded['borrowing_status'] === 'Returned (Damaged)' && $recorded['damage_penalty_amount'] === '50.00', 'recorded damage cannot be bypassed with Good Condition');
$inspection = inspect(4,'Critical','500');
$pending = (int)$inspection['disposal_request_id'];
rejects(fn()=>reviewDisposal($pdo,$pending,'Approved','',1,'admin'), 'active loans prevent disposal approval');
processEquipmentReturn($pdo,4,'damaged',['damage_level'=>'Critical','reason'=>'Critical return','assessed_cost'=>'500'],1);
verify((int)$pdo->query('SELECT COUNT(*) FROM disposal_requests WHERE equipment_id=4')->fetchColumn() === 1, 'inspection and return share one pending request');
reviewDisposal($pdo,$pending,'Rejected','Repair is feasible',1,'admin');
verify(value('equipment',4,'status') === 'Under Repair', 'rejected disposal routes to repair');
verify(count(disposalRecords($pdo)) === 1, 'rejected and pending items excluded from disposal report');
inspect(5,'Critical','200');
rejects(fn()=>processEquipmentReturn($pdo,5,'damaged',['damage_level'=>'Not Critical','reason'=>'Downgrade','assessed_cost'=>'10'],1), 'pending critical condition cannot be downgraded');
verify(value('borrowed',5,'status') === 'Overdue', 'failed assessment rolls back return');
$good=processEquipmentReturn($pdo,6,'good',[],1);
verify($good['equipment_status'] === 'Available' && (float)value('borrowed',6,'damage_penalty_amount') === 0.0, 'good return has no damage fee');
$legacy=processEquipmentReturn($pdo,7,'damaged',['damage_level'=>'Not Critical','reason'=>'Legacy loan','assessed_cost'=>'0','student_faculty_id'=>'TEST-A'],1);
verify(value('borrowed',7,'student_faculty_id') === 'TEST-A' && $legacy['damage_penalty_amount'] === '0.00', 'legacy borrower linked and explicit fee waiver retained');
rejects(fn()=>processEquipmentReturn($pdo,8,'damaged',['damage_level'=>'Critical','reason'=>'Invalid cost','assessed_cost'=>'-1'],1), 'invalid cost cannot create disposal or fee');
verify(value('borrowed',8,'status') === 'Overdue' && value('equipment',8,'status') === 'In Use', 'invalid return is atomic');
rejects(fn()=>assertEquipmentStatusEdit($pdo,8,'Disposed'), 'generic update cannot directly assign disposal');
rejects(fn()=>assertEquipmentStatusEdit($pdo,5,'Available'), 'pending disposal cannot be made available');
echo "PASS: $count condition, return, fee, disposal authorization, history, and report checks; temporary data only.\n";
