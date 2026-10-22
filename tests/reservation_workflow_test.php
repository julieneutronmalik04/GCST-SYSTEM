<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/reservations.php';
require_once __DIR__ . '/temporary_tables.php';
$pdo = getDB();
createTestTables($pdo, ['users', 'rooms', 'reservations']);
foreach ([10, 11, 12, 13, 14, 15] as $userId) {
    $pdo->prepare("INSERT INTO users(id,username,password,full_name,role,is_active) VALUES(?,?,?,'Fixture Faculty','faculty',1)")
        ->execute([$userId, 'fixture-' . $userId, 'unused']);
}
$pdo->exec("INSERT INTO rooms(id,room_code,room_name,type,capacity,status) VALUES
 (7001,'TEST-A','Test classroom','Classroom',30,'Available'),
 (7002,'TEST-B','Other classroom','Classroom',30,'Occupied'),
 (7003,'TEST-M','Maintenance','Classroom',30,'Under Maintenance')");
$day = $pdo->query('SELECT CURDATE() + INTERVAL 1 DAY')->fetchColumn();
$checks = 0;
function check(bool $value, string $label): void { global $checks; if (!$value) throw new RuntimeException($label); $checks++; }
function requestRoom(string $room, string $start, string $end, int $faculty = 10, int $attendees = 10): array {
    global $pdo, $day;
    return createReservation($pdo, ['room_id' => $room, 'res_date' => $day, 'time_start' => $start, 'time_end' => $end, 'attendees' => $attendees], $faculty, 'Fixture Faculty');
}
function state(int $id): string { global $pdo; $stmt = $pdo->prepare('SELECT status FROM reservations WHERE id = ?'); $stmt->execute([$id]); return (string) $stmt->fetchColumn(); }
function fails(callable $action, string $message): void {
    try { $action(); } catch (InvalidArgumentException | DomainException $error) { check(str_contains($error->getMessage(), $message), 'Expected validation: ' . $message); return; }
    throw new RuntimeException('Expected failure: ' . $message);
}
$a = requestRoom('TEST-A', '09:00', '10:00');
check($a['reservation_status'] === 'Pending', 'Free room is pending');
$b = requestRoom('TEST-A', '09:00', '10:00', 11);
check($b['reservation_status'] === 'Pending', 'Pending request does not reserve a slot');
$v = reservationAvailability($pdo, 'TEST-A', $day, '09:15', '', 10);
check($v['checked'] && !$v['complete'] && !$v['conflict'], 'Start-only availability ignores pending requests');
$row = $pdo->query('SELECT * FROM reservations WHERE id=' . $a['reservation_id'])->fetch();
check((int) $row['room_id'] === 7001 && $row['room_code'] === 'TEST-A', 'Both room foreign key and code stored');
check($row['time_start'] === '09:00:00', 'Times normalized');
$approved = reviewReservation($pdo, $a['reservation_id'], 'approve', 1, 'admin');
check(state($a['reservation_id']) === 'Approved', 'Admin approval succeeds');
check(state($b['reservation_id']) === 'Rejected' && count($approved['affected']) === 1, 'Exact conflicting pending request auto-rejected');
$v = reservationAvailability($pdo, 'TEST-A', $day, '09:15', '', 10);
check($v['conflict'] && $v['request_status'] === 'Waiting List', 'Conflict detected without end time');
check(!reservationAvailability($pdo, 'TEST-A', $day, '10:00', '', 10)['conflict'], 'Start at previous end is available');
check(reservationAvailability($pdo, 'TEST-A', $day, '09:00', '', 10)['conflict'], 'Start at approved start conflicts');
check(!reservationAvailability($pdo, 'TEST-A', $day, '08:00', '', 10)['conflict'], 'Earlier start alone is available');
check(reservationAvailability($pdo, 'TEST-A', $day, '08:00', '09:30', 10)['conflict'], 'End-time update catches later overlap');
$waiting = requestRoom('TEST-A', '09:30', '10:30', 12);
check($waiting['reservation_status'] === 'Waiting List', 'Conflicting request saved on Waiting List');
fails(fn() => reviewReservation($pdo, $waiting['reservation_id'], 'approve', 1, 'admin'), 'overlaps');
check(state($waiting['reservation_id']) === 'Waiting List', 'Blocked approval leaves original status');
$adjacent = requestRoom('TEST-A', '10:00', '11:00', 13);
check($adjacent['reservation_status'] === 'Pending', 'Adjacent reservation remains bookable');
$otherDay = $pdo->query('SELECT CURDATE() + INTERVAL 2 DAY')->fetchColumn();
check(!reservationAvailability($pdo, 'TEST-A', $otherDay, '09:15', '', 10)['conflict'], 'Other dates are unaffected');
check(requestRoom('TEST-B', '09:00', '10:00')['reservation_status'] === 'Pending', 'Current occupied status does not block future free slot');
check(!reservationAvailability($pdo, 'TEST-B', $day, '09:15', '', 10)['conflict'], 'Other rooms are unaffected');
$clock = reservationClock($pdo);
check(reservationAvailability($pdo, 'TEST-B', $clock['today'], substr($clock['clock_time'], 0, 5), '', 10)['conflict'], 'Manual occupancy blocks a start time in the current minute');
fails(fn() => requestRoom('TEST-M', '09:00', '10:00'), 'maintenance');
fails(fn() => requestRoom('TEST-A', '11:00', '12:00', 10, 31), 'capacity');
fails(fn() => requestRoom('TEST-A', '11:00', '12:00', 10, 0), 'positive whole');
fails(fn() => requestRoom('TEST-A', '11:00', ''), 'End time is required');
fails(fn() => requestRoom('TEST-A', '12:00', '11:00'), 'after start');
fails(fn() => reservationAvailability($pdo, 'TEST-A', '2026-02-30', '09:00', '', 10), 'valid reservation date');
fails(fn() => reservationAvailability($pdo, 'TEST-A', $day, '25:00', '', 10), 'valid time');
fails(fn() => reviewReservation($pdo, $a['reservation_id'], 'cancel', 11, 'faculty'), 'Access denied');
check(state($a['reservation_id']) === 'Approved', 'Ownership failure rolls back');
$cancelled = reviewReservation($pdo, $a['reservation_id'], 'cancel', 10, 'faculty');
check(state($waiting['reservation_id']) === 'Pending' && count($cancelled['promoted']) === 1, 'Released slot promotes waiting request for review');
check(state($b['reservation_id']) === 'Rejected', 'Rejected requests never silently promoted');
// Alternative admin policy moves all overlapping pending intervals to waiting.
$c = requestRoom('TEST-A', '12:00', '13:00');
$d = requestRoom('TEST-A', '12:30', '13:30', 14);
$e = requestRoom('TEST-A', '13:00', '14:00', 15);
$policy = reviewReservation($pdo, $c['reservation_id'], 'approve', 1, 'admin', '', 'Waiting List');
check(state($d['reservation_id']) === 'Waiting List' && $policy['affected_status'] === 'Waiting List', 'Waiting List admin policy supported');
check(state($e['reservation_id']) === 'Pending', 'Adjacent pending request untouched');
fails(fn() => reviewReservation($pdo, $c['reservation_id'], 'approve', 1, 'admin'), 'already Approved');
fails(fn() => reviewReservation($pdo, $e['reservation_id'], 'approve', 15, 'faculty'), 'Access denied');
reviewReservation($pdo, $c['reservation_id'], 'delete', 1, 'admin');
check(state($d['reservation_id']) === 'Pending', 'Deleting approved booking releases waiting requests');
reviewReservation($pdo, $d['reservation_id'], 'approve', 1, 'admin');
check(state($e['reservation_id']) === 'Rejected', 'Partial overlap is auto-rejected too');
check(!$pdo->inTransaction(), 'No transaction left open after checks');
echo "PASS: $checks reservation workflow checks. Production records untouched.\n";
