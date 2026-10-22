<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/reservations.php';
require_once __DIR__ . '/temporary_tables.php';
$pdo = getDB();
createTestTables($pdo, ['users', 'rooms', 'reservations']);
foreach ([10 => 'faculty', 11 => 'faculty', 20 => 'vip', 21 => 'vip', 22 => 'vip'] as $id => $role) {
    $pdo->prepare('INSERT INTO users(id,username,password,full_name,role,is_active) VALUES(?,?,?,?,?,?)')
        ->execute([$id, 'fixture-' . $id, 'unused', 'Fixture User ' . $id, $role, $id === 22 ? 0 : 1]);
}
$pdo->exec("INSERT INTO rooms(id,room_code,room_name,type,capacity,status) VALUES
 (7001,'TEST-A','Test classroom','Classroom',30,'Available'),
 (7002,'TEST-M','Maintenance','Classroom',30,'Under Maintenance')");
$day = $pdo->query('SELECT CURDATE() + INTERVAL 1 DAY')->fetchColumn();
$checks = 0;
function checkVip(bool $value, string $message): void { global $checks; if (!$value) throw new RuntimeException($message); $checks++; }
function makeVipRequest(int $user, string $start, string $end, array $extra = []): array {
    global $pdo, $day;
    return createReservation($pdo, $extra + ['room_id' => 'TEST-A', 'res_date' => $day, 'time_start' => $start, 'time_end' => $end, 'attendees' => 10], $user, 'Fixture');
}
function vipRow(int $id): array { global $pdo; $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = ?'); $stmt->execute([$id]); return $stmt->fetch(); }
function vipFails(callable $action, string $message): void {
    try { $action(); } catch (InvalidArgumentException | DomainException $error) { checkVip(str_contains($error->getMessage(), $message), 'Expected validation: ' . $message); return; }
    throw new RuntimeException('Expected failure: ' . $message);
}
$regular = makeVipRequest(10, '09:00', '10:00', ['priority' => 'VIP']);
checkVip($regular['priority'] === 'Regular', 'Client cannot forge VIP priority');
reviewReservation($pdo, $regular['reservation_id'], 'approve', 1, 'admin');
$vip = makeVipRequest(20, '09:30', '10:30');
$vipId = $vip['reservation_id'];
$regularId = $regular['reservation_id'];
checkVip($vip['priority'] === 'VIP' && $vip['reservation_status'] === 'Waiting List', 'VIP role tags a conflicting request');
checkVip(vipRow($regularId)['status'] === 'Approved', 'Submission never displaces a booking');
$info = attachReservationPriorityConflicts($pdo, [vipRow($vipId)])[0];
checkVip(count($info['priority_conflicts']) === 1 && $info['priority_conflicts'][0]['id'] === $regularId, 'Admin priority conflict projection identifies regular booking');
vipFails(fn() => reviewReservation($pdo, $vipId, 'approve', 20, 'vip', '', null, true, [$regularId]), 'Access denied');
vipFails(fn() => reviewReservation($pdo, $vipId, 'cancel', 21, 'vip'), 'Access denied');
vipFails(fn() => reviewReservation($pdo, $vipId, 'approve', 1, 'admin'), 'overlaps');
vipFails(fn() => reviewReservation($pdo, $vipId, 'approve', 1, 'admin', '', null, true, [99999]), 'conflicts changed');
checkVip(vipRow($regularId)['status'] === 'Approved' && vipRow($vipId)['status'] === 'Waiting List', 'Failed or stale confirmation rolls back all changes');
$approved = reviewReservation($pdo, $vipId, 'approve', 1, 'admin', '', null, true, [$regularId]);
checkVip(vipRow($vipId)['status'] === 'Approved', 'Admin approves VIP priority request');
checkVip(vipRow($regularId)['status'] === 'Waiting List' && count($approved['displaced']) === 1, 'Approved regular booking moves to Waiting List');
checkVip(str_contains(vipRow($regularId)['admin_note'], 'VIP') && $approved['affected'][0]['new_status'] === 'Waiting List', 'Displacement reason and notification transition recorded');
vipFails(fn() => reviewReservation($pdo, $regularId, 'approve', 1, 'admin', '', null, true, [$vipId]), 'Only VIP');
vipFails(fn() => reviewReservation($pdo, $regularId, 'approve', 1, 'admin'), 'overlaps');
$secondVip = makeVipRequest(21, '09:45', '10:15');
$secondId = $secondVip['reservation_id'];
$info = attachReservationPriorityConflicts($pdo, [vipRow($secondId)])[0];
checkVip($info['vip_conflict_count'] === 1 && $info['priority_conflicts'] === [], 'VIP conflict distinguished from regular conflict');
vipFails(fn() => reviewReservation($pdo, $secondId, 'approve', 1, 'admin', '', null, true, [$vipId]), 'cannot displace each other');
checkVip(vipRow($vipId)['status'] === 'Approved', 'VIP-to-VIP conflict leaves approved VIP intact');
$cancel = reviewReservation($pdo, $vipId, 'cancel', 20, 'vip');
checkVip(vipRow($regularId)['status'] === 'Pending' && vipRow($secondId)['status'] === 'Pending', 'Cancelling VIP releases regular and VIP waiting requests for review');
$regularApproval = reviewReservation($pdo, $regularId, 'approve', 1, 'admin');
checkVip(vipRow($secondId)['status'] === 'Waiting List', 'Regular approval cannot automatically reject VIP request');
checkVip($regularApproval['affected'][0]['new_status'] === 'Waiting List', 'Mixed conflict decisions expose actual transition status');
$pending = makeVipRequest(10, '12:00', '13:00');
$priority = makeVipRequest(20, '12:00', '13:00');
reviewReservation($pdo, $priority['reservation_id'], 'approve', 1, 'admin');
checkVip(vipRow($pending['reservation_id'])['status'] === 'Waiting List', 'VIP approval retains overlapping regular pending request on Waiting List');
$adjacent = makeVipRequest(11, '13:00', '14:00');
checkVip($adjacent['reservation_status'] === 'Pending', 'Adjacent regular interval unaffected');
vipFails(fn() => makeVipRequest(22, '15:00', '16:00'), 'cannot submit');
vipFails(fn() => makeVipRequest(20, '15:00', '16:00', ['room_id' => 'TEST-M']), 'maintenance');
vipFails(fn() => makeVipRequest(20, '15:00', '16:00', ['attendees' => 31]), 'capacity');
// One VIP can displace multiple adjacent approved regular intervals atomically.
$left = makeVipRequest(10, '16:00', '17:00');
$right = makeVipRequest(11, '17:00', '18:00');
reviewReservation($pdo, $left['reservation_id'], 'approve', 1, 'admin');
reviewReservation($pdo, $right['reservation_id'], 'approve', 1, 'admin');
$wide = makeVipRequest(20, '16:30', '17:30');
$result = reviewReservation($pdo, $wide['reservation_id'], 'approve', 1, 'admin', '', null, true, [$left['reservation_id'], $right['reservation_id']]);
checkVip(count($result['displaced']) === 2 && vipRow($left['reservation_id'])['status'] === 'Waiting List' && vipRow($right['reservation_id'])['status'] === 'Waiting List', 'All overlapping regular bookings waitlisted in one transaction');
$clock = reservationClock($pdo);
if ($clock['clock_time'] < '23:59:00') {
    $nowStart = substr($clock['clock_time'], 0, 5);
    $current = makeVipRequest(10, $nowStart, '23:59:59', ['res_date' => $clock['today']]);
    reviewReservation($pdo, $current['reservation_id'], 'approve', 1, 'admin');
    $pdo->exec("UPDATE rooms SET status = 'Occupied' WHERE id = 7001");
    $currentVip = makeVipRequest(21, $nowStart, '23:59:59', ['res_date' => $clock['today']]);
    reviewReservation($pdo, $currentVip['reservation_id'], 'approve', 1, 'admin', '', null, true, [$current['reservation_id']]);
    checkVip(vipRow($current['reservation_id'])['status'] === 'Waiting List' && vipRow($currentVip['reservation_id'])['status'] === 'Approved', 'Confirmed VIP priority can displace current regular booking in an occupied room');
    $pdo->exec("UPDATE rooms SET status = 'Available' WHERE id = 7001");
}
$pdo->exec("UPDATE users SET role = 'faculty' WHERE id = 20");
checkVip(vipRow($wide['reservation_id'])['priority'] === 'VIP', 'Historical priority snapshot retained after account role change');
checkVip(makeVipRequest(20, '18:00', '19:00')['priority'] === 'Regular', 'New requests use current database role');
$overlaps = $pdo->query("SELECT COUNT(*) FROM reservations a JOIN reservations b ON a.id < b.id AND a.room_id = b.room_id AND a.res_date = b.res_date
 AND a.time_start < b.time_end AND a.time_end > b.time_start WHERE a.status = 'Approved' AND b.status = 'Approved'")->fetchColumn();
checkVip((int) $overlaps === 0, 'No overlapping approved bookings after all priority transitions');
checkVip(!$pdo->inTransaction(), 'No transaction left open');
echo "PASS: $checks VIP reservation workflow checks. Production records untouched.\n";
