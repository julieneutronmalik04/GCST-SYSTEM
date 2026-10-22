<?php
// Private localhost router for HTTP integration checks; never serves through Apache.
if (PHP_SAPI !== 'cli-server' || !getenv('RESERVATION_TEST_TOKEN') || !hash_equals(getenv('RESERVATION_TEST_TOKEN'), $_SERVER['HTTP_X_TEST_TOKEN'] ?? '')) {
    http_response_code(404); exit;
}
session_save_path(getenv('RESERVATION_TEST_DIR'));
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
$_SESSION = [];
$role = $_SERVER['HTTP_X_TEST_ROLE'] ?? 'faculty';
if ($role !== 'guest') {
    $_SESSION['user_id'] = $role === 'admin' ? 1 : ($role === 'vip' ? 20 : 10);
    $_SESSION['role'] = $role;
    $_SESSION['full_name'] = 'Test Faculty';
}
$pdo = getDB();
foreach (['users', 'rooms', 'reservations'] as $table) {
    $ddl = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM)[1];
    $ddl = preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl);
    $ddl = preg_replace('/^\s*CONSTRAINT .*\n?/m', '', $ddl);
    $ddl = preg_replace('/,\s*\) ENGINE/', "\n) ENGINE", $ddl);
    $pdo->exec($ddl);
}
foreach (['admin_notifications', 'faculty_notifications', 'notifications'] as $table) {
    $pdo->exec("CREATE TEMPORARY TABLE $table (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, type VARCHAR(50), title VARCHAR(200), body TEXT, link_ref VARCHAR(50), is_read TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
}
$hash = password_hash('Fixture-Pass-123', PASSWORD_BCRYPT);
foreach ([1 => 'admin', 10 => 'faculty', 11 => 'faculty', 20 => 'vip', 21 => 'vip'] as $userId => $userRole) {
    $pdo->prepare('INSERT INTO users(id,username,password,full_name,role,is_active) VALUES(?,?,?,?,?,1)')
        ->execute([$userId, 'fixture-' . $userId, $hash, 'Fixture User ' . $userId, $userRole]);
}
if (($_SERVER['HTTP_X_TEST_SCENARIO'] ?? '') === 'promoted-user') $pdo->exec("UPDATE users SET role = 'vip' WHERE id = 10");
$pdo->exec('CREATE TEMPORARY TABLE class_schedules (year_section VARCHAR(80), subject_name VARCHAR(160), start_time TIME, end_time TIME, room_code VARCHAR(50), day_of_week VARCHAR(3))');
$pdo->exec('CREATE TEMPORARY TABLE activity_log (user_id INT, action VARCHAR(100), target VARCHAR(100), details TEXT, ip_address VARCHAR(100))');
$pdo->exec("INSERT INTO rooms(id,room_code,room_name,type,capacity,status) VALUES(7001,'TEST-ROOM','Fixture classroom','Classroom',30,'Available')");
$pdo->exec("INSERT INTO reservations(id,res_code,faculty_id,faculty_name,room_id,room_code,room_name,res_date,time_start,time_end,attendees,status) VALUES
 (1,'RES-001',10,'Faculty One',7001,'TEST-ROOM','Fixture classroom','2099-01-02','09:00','10:00',10,'Pending'),
 (2,'RES-002',11,'Faculty Two',7001,'TEST-ROOM','Fixture classroom','2099-01-02','09:00','10:00',10,'Pending')");
if (($_SERVER['HTTP_X_TEST_SCENARIO'] ?? '') === 'occupied') $pdo->exec("UPDATE reservations SET status = 'Approved' WHERE id = 1");
if (in_array($_SERVER['HTTP_X_TEST_SCENARIO'] ?? '', ['vip', 'vip-blocked'], true)) {
    $pdo->exec("UPDATE reservations SET status = 'Approved' WHERE id = 1");
    if ($_SERVER['HTTP_X_TEST_SCENARIO'] === 'vip-blocked') $pdo->exec("UPDATE reservations SET priority = 'VIP', faculty_id = 21 WHERE id = 1");
    $pdo->exec("INSERT INTO reservations(id,res_code,faculty_id,faculty_name,room_id,room_code,room_name,res_date,time_start,time_end,attendees,status,priority) VALUES
     (3,'RES-003',20,'VIP Fixture',7001,'TEST-ROOM','Fixture classroom','2099-01-02','09:00','10:00',10,'Waiting List','VIP')");
}
register_shutdown_function(function () use ($pdo): void {
    file_put_contents(getenv('RESERVATION_TEST_DIR') . '/state.json', json_encode([
        'reservations' => $pdo->query('SELECT id,room_id,room_code,status,priority,admin_note FROM reservations ORDER BY id')->fetchAll(),
        'faculty_notifications' => $pdo->query('SELECT * FROM faculty_notifications')->fetchAll(),
        'users' => $pdo->query('SELECT id,username,role FROM users ORDER BY id')->fetchAll(),
    ]));
    session_destroy();
});
$endpoint = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($endpoint === 'check_availability.php') {
    require __DIR__ . '/../modules/check_availability.php';
} elseif ($endpoint === 'login.php') {
    require __DIR__ . '/../login.php';
} elseif (in_array($endpoint, ['users.php', 'notifications.php', 'analytics.php', 'auth_check.php'], true)) {
    require __DIR__ . '/../modules/' . $endpoint;
} else {
    require __DIR__ . '/../modules/reservations.php';
}
