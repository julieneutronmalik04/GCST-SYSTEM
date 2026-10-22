<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/public_room_status.php';
$pdo = getDB();
// Connection-local tables shadow production tables; no real records are changed.
$pdo->exec("CREATE TEMPORARY TABLE rooms (
    id INT PRIMARY KEY, room_code VARCHAR(20), room_name VARCHAR(100),
    type VARCHAR(50), capacity INT, location VARCHAR(100), status VARCHAR(30)
)");
$pdo->exec("CREATE TEMPORARY TABLE reservations (
    room_id INT, res_date DATE, time_start TIME, time_end TIME,
    status VARCHAR(30), purpose VARCHAR(100), faculty_id INT
)");
$pdo->exec("INSERT INTO rooms VALUES
 (1,'TEST-1','Classroom','Classroom',30,'Main Building','Available'),
 (2,'TEST-2','Laboratory','Laboratory',20,'Annex','Available'),
 (3,'TEST-3','Repair room','Classroom',10,NULL,'Under Maintenance'),
 (4,'TEST-4','Occupied room','Classroom',15,'Main Building','Occupied')");
$pdo->exec("INSERT INTO reservations VALUES
 (1,CURDATE(),'00:00:00','23:59:59','Pending','PRIVATE PURPOSE',123),
 (1,CURDATE(),'00:00:00','23:59:59','Rejected','PRIVATE PURPOSE',123),
 (1,CURDATE() + INTERVAL 1 DAY,'00:00:00','23:59:59','Approved','PRIVATE PURPOSE',123),
 (1,CURDATE() - INTERVAL 1 DAY,'00:00:00','23:59:59','Approved','PRIVATE PURPOSE',123),
 (2,CURDATE(),'00:00:00','23:59:59','Approved','PRIVATE PURPOSE',123),
 (3,CURDATE(),'00:00:00','23:59:59','Approved','PRIVATE PURPOSE',123)");
$checks = 0;
function verify(bool $passed, string $label): void {
    global $checks;
    if (!$passed) throw new RuntimeException($label);
    $checks++;
}
$data = publicRoomStatus($pdo);
verify(count($data['rooms']) === 4, 'All rooms are listed');
[$available, $occupied, $maintenance, $manualOccupied] = $data['rooms'];
verify($available['status'] === 'Available', 'Pending, rejected and other dates do not occupy room');
verify($available['schedule'] === [] && $available['current_reservation'] === null, 'Only approved slots today are public');
verify($occupied['status'] === 'Occupied', 'Approved reservation in progress dynamically occupies room');
verify($occupied['current_reservation']['is_current'] === true, 'Current slot is identified');
verify(count($occupied['schedule']) === 1, 'Approved slot is displayed');
verify($maintenance['status'] === 'Under Maintenance', 'Maintenance status wins over reservation');
verify($manualOccupied['status'] === 'Occupied', 'Manually occupied room remains occupied');
verify($available['capacity'] === 30 && $available['location'] === 'Main Building', 'Room details and numeric capacity returned');
verify(isset($data['date'], $data['checked_at']), 'Database date and timestamp returned');
verify(!str_contains(json_encode($data), 'PRIVATE PURPOSE') && !str_contains(json_encode($data), 'faculty_id'), 'Private reservation data excluded');
echo "PASS: $checks public room status checks. No production records changed.\n";
