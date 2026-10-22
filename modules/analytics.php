<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);

requireLogin(['admin', 'faculty', 'vip', 'maintenance']);
header('Content-Type: application/json');

$pdo = getDB();
$role = getRole();
$uid  = getUserId();

function fillSeries(array $keys, array $map): array
{
    $data = [];
    foreach ($keys as $key) {
        $data[] = (int) ($map[$key] ?? 0);
    }
    return $data;
}

function dailyLabels(int $days): array
{
    $labels = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $labels[] = (new DateTimeImmutable('today'))->modify("-{$i} days")->format('M j');
    }
    return $labels;
}

function dailyKeys(int $days): array
{
    $keys = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $keys[] = (new DateTimeImmutable('today'))->modify("-{$i} days")->format('Y-m-d');
    }
    return $keys;
}

function monthlyLabels(int $months): array
{
    $labels = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $labels[] = (new DateTimeImmutable('first day of this month'))->modify("-{$i} months")->format('M Y');
    }
    return $labels;
}

function monthlyKeys(int $months): array
{
    $keys = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $keys[] = (new DateTimeImmutable('first day of this month'))->modify("-{$i} months")->format('Y-m');
    }
    return $keys;
}

function fetchRoomStatusCounts(PDO $pdo): array
{
    $counts = ['Available' => 0, 'Occupied' => 0, 'Under Maintenance' => 0, 'total' => 0];

    $stmt = $pdo->query("
        SELECT r.id, r.status,
               CASE WHEN EXISTS (
                   SELECT 1
                   FROM reservations res
                   WHERE (res.room_id = r.id OR res.room_code = r.room_code OR res.room_code = r.room_id)
                     AND res.status = 'Approved'
                     AND res.res_date = CURDATE()
                     AND CURTIME() BETWEEN res.time_start AND res.time_end
               ) THEN 1 ELSE 0 END AS occupied_now
        FROM rooms r
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['status'] ?? 'Available';
        if ($status === 'Under Maintenance') {
            $bucket = 'Under Maintenance';
        } elseif ($status === 'Occupied' || (int) ($row['occupied_now'] ?? 0) > 0) {
            $bucket = 'Occupied';
        } else {
            $bucket = 'Available';
        }

        $counts[$bucket]++;
        $counts['total']++;
    }

    return $counts;
}

function fetchEquipmentStatusCounts(PDO $pdo): array
{
    $counts = ['Available' => 0, 'Borrowed' => 0, 'Maintenance' => 0, 'total' => 0];
    $stmt = $pdo->query('SELECT status, COUNT(*) AS total FROM equipment GROUP BY status');

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['status'] ?? 'Available';
        $total  = (int) ($row['total'] ?? 0);

        if ($status === 'Available') {
            $counts['Available'] += $total;
        } elseif ($status === 'In Use' || $status === 'Borrowed') {
            $counts['Borrowed'] += $total;
        } else {
            $counts['Maintenance'] += $total;
        }

        $counts['total'] += $total;
    }

    return $counts;
}

function fetchReservationStatusCounts(PDO $pdo, ?int $facultyId = null): array
{
    $counts = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0, 'Cancelled' => 0, 'total' => 0];
    $sql = 'SELECT status, COUNT(*) AS total FROM reservations';
    $params = [];

    if ($facultyId !== null) {
        $sql .= ' WHERE faculty_id = ?';
        $params[] = $facultyId;
    }

    $sql .= ' GROUP BY status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['status'] ?? 'Pending';
        $total  = (int) ($row['total'] ?? 0);

        if ($status === 'Pending') {
            $counts['Pending'] += $total;
        } elseif ($status === 'Approved') {
            $counts['Approved'] += $total;
        } elseif ($status === 'Rejected' || $status === 'Cancelled') {
            $counts['Rejected'] += $total;
        } else {
            $counts['Rejected'] += $total;
        }

        $counts['total'] += $total;
    }

    return $counts;
}

function fetchDailyReservations(PDO $pdo, int $days = 7, ?int $facultyId = null): array
{
    $start = (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
    $sql = 'SELECT DATE(res_date) AS bucket, COUNT(*) AS total FROM reservations WHERE res_date >= ?';
    $params = [$start];

    if ($facultyId !== null) {
        $sql .= ' AND faculty_id = ?';
        $params[] = $facultyId;
    }

    $sql .= ' GROUP BY DATE(res_date)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['bucket']] = (int) ($row['total'] ?? 0);
    }

    $keys = dailyKeys($days);
    return [
        'labels' => dailyLabels($days),
        'data'   => fillSeries($keys, $map),
    ];
}

function fetchMonthlyReservations(PDO $pdo, int $months = 6, ?int $facultyId = null): array
{
    $start = (new DateTimeImmutable('first day of this month'))->modify('-' . ($months - 1) . ' months')->format('Y-m-01');
    $sql = 'SELECT DATE_FORMAT(res_date, "%Y-%m") AS bucket, COUNT(*) AS total FROM reservations WHERE res_date >= ?';
    $params = [$start];

    if ($facultyId !== null) {
        $sql .= ' AND faculty_id = ?';
        $params[] = $facultyId;
    }

    $sql .= ' GROUP BY DATE_FORMAT(res_date, "%Y-%m")';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['bucket']] = (int) ($row['total'] ?? 0);
    }

    $keys = monthlyKeys($months);
    return [
        'labels' => monthlyLabels($months),
        'data'   => fillSeries($keys, $map),
    ];
}

function fetchTopBorrowedEquipment(PDO $pdo, int $limit = 5): array
{
    $sql = "
        SELECT e.equip_code, e.name, COUNT(*) AS total
        FROM borrowed b
        JOIN equipment e ON e.id = b.equipment_id
        GROUP BY e.id, e.equip_code, e.name
        ORDER BY total DESC, e.equip_code ASC
        LIMIT {$limit}
    ";
    $stmt = $pdo->query($sql);

    $labels = [];
    $data   = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = $row['equip_code'] . ' - ' . $row['name'];
        $data[]   = (int) ($row['total'] ?? 0);
    }

    if (!$labels) {
        $labels = ['No borrowings'];
        $data   = [0];
    }

    return compact('labels', 'data');
}

try {
    $payload = [
        'role' => $role,
        'stats' => [],
        'charts' => [],
    ];

    if ($role === 'admin') {
        $period = $_GET['period'] ?? 'day';

        $roomCounts = fetchRoomStatusCounts($pdo);
        $equipmentCounts = fetchEquipmentStatusCounts($pdo);
        $reservationCounts = fetchReservationStatusCounts($pdo);

        if ($period === 'month') {
            $start = (new DateTimeImmutable('first day of this month'))->modify('-5 months')->format('Y-m-01');
            $end = (new DateTimeImmutable('first day of next month'))->format('Y-m-01');
            $stmt = $pdo->prepare('SELECT DATE_FORMAT(res_date, "%Y-%m") AS bucket, COUNT(*) AS total FROM reservations WHERE res_date >= ? AND res_date < ? GROUP BY DATE_FORMAT(res_date, "%Y-%m")');
            $stmt->execute([$start, $end]);
            $map = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[$row['bucket']] = (int) ($row['total'] ?? 0);
            }
            $labels = [];
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = (new DateTimeImmutable('first day of this month'))->modify("-{$i} months");
                $bucket = $date->format('Y-m');
                $labels[] = $date->format('M');
                $data[] = $map[$bucket] ?? 0;
            }
            $reservationsBar = ['labels' => $labels, 'data' => $data];
        } elseif ($period === 'week') {
            $start = (new DateTimeImmutable('monday this week'))->modify('-3 weeks')->format('Y-m-d');
            $end = (new DateTimeImmutable('monday this week'))->modify('+1 week')->format('Y-m-d');
            $stmt = $pdo->prepare('SELECT YEARWEEK(res_date, 1) AS bucket, COUNT(*) AS total FROM reservations WHERE res_date >= ? AND res_date < ? GROUP BY bucket');
            $stmt->execute([$start, $end]);
            $map = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[$row['bucket']] = (int) ($row['total'] ?? 0);
            }
            $labels = [];
            $data = [];
            for ($i = 3; $i >= 0; $i--) {
                $date = (new DateTimeImmutable('monday this week'))->modify("-{$i} weeks");
                $bucket = $date->format('oW'); 
                $labels[] = 'Week ' . $date->format('W');
                $data[] = $map[$bucket] ?? 0;
            }
            $reservationsBar = ['labels' => $labels, 'data' => $data];
        } else {
            $start = (new DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d');
            $end = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
            $stmt = $pdo->prepare('SELECT DATE(res_date) AS bucket, COUNT(*) AS total FROM reservations WHERE res_date >= ? AND res_date < ? GROUP BY DATE(res_date)');
            $stmt->execute([$start, $end]);
            $map = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[$row['bucket']] = (int) ($row['total'] ?? 0);
            }
            $labels = [];
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = (new DateTimeImmutable('today'))->modify("-{$i} days");
                $bucket = $date->format('Y-m-d');
                $labels[] = $date->format('M j');
                $data[] = $map[$bucket] ?? 0;
            }
            $reservationsBar = ['labels' => $labels, 'data' => $data];
        }
        
        $trendPeriod = $_GET['trend_period'] ?? 'month';
        
        if ($trendPeriod === 'month') {
            $stmt = $pdo->query('SELECT DATE_FORMAT(res_date, "%Y-%m") AS bucket, COUNT(*) AS total FROM reservations GROUP BY bucket ORDER BY bucket ASC');
            $map = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[$row['bucket']] = (int) ($row['total'] ?? 0);
            }
            $labels = [];
            $data = [];
            foreach ($map as $bucket => $total) {
                $date = DateTimeImmutable::createFromFormat('Y-m', $bucket);
                $labels[] = $date ? $date->format('M Y') : $bucket;
                $data[] = $total;
            }
            if (empty($labels)) {
                $labels = ['No data'];
                $data = [0];
            }
            $reservationTrend = ['labels' => $labels, 'data' => $data];
        } elseif ($trendPeriod === 'week') {
            $stmt = $pdo->query('SELECT YEARWEEK(res_date, 1) AS bucket, COUNT(*) AS total, MIN(res_date) as first_date FROM reservations GROUP BY bucket ORDER BY bucket ASC');
            $map = [];
            $dateMap = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[$row['bucket']] = (int) ($row['total'] ?? 0);
                $dateMap[$row['bucket']] = $row['first_date'];
            }
            $labels = [];
            $data = [];
            foreach ($map as $bucket => $total) {
                $date = new DateTimeImmutable($dateMap[$bucket]);
                $labels[] = 'Week ' . $date->format('W') . ' (' . $date->format('M Y') . ')';
                $data[] = $total;
            }
            if (empty($labels)) {
                $labels = ['No data'];
                $data = [0];
            }
            $reservationTrend = ['labels' => $labels, 'data' => $data];
        } else {
            // Day
            $stmt = $pdo->query('SELECT DATE(res_date) AS bucket, COUNT(*) AS total FROM reservations GROUP BY bucket ORDER BY bucket ASC');
            $map = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[$row['bucket']] = (int) ($row['total'] ?? 0);
            }
            $labels = [];
            $data = [];
            foreach ($map as $bucket => $total) {
                $date = new DateTimeImmutable($bucket);
                $labels[] = $date->format('M j, Y');
                $data[] = $total;
            }
            if (empty($labels)) {
                $labels = ['No data'];
                $data = [0];
            }
            $reservationTrend = ['labels' => $labels, 'data' => $data];
        }

        $payload['stats'] = [
            'total_rooms'          => $roomCounts['total'],
            'total_equipment'      => $equipmentCounts['total'],
            'pending_reservations'  => $reservationCounts['Pending'],
            'borrowed_equipment'    => $equipmentCounts['Borrowed'],
            'total_users'          => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        ];

        $payload['charts']['room_status'] = [
            'labels' => ['Available Rooms', 'Occupied Rooms', 'Under Maintenance'],
            'data'   => [$roomCounts['Available'], $roomCounts['Occupied'], $roomCounts['Under Maintenance']],
        ];
        $payload['charts']['equipment_status'] = [
            'labels' => ['Available Equipment', 'In-Use Equipment', 'Under Maintenance'],
            'data'   => [$equipmentCounts['Available'], $equipmentCounts['Borrowed'], $equipmentCounts['Maintenance']],
        ];
        $payload['charts']['reservations_bar'] = [
            'labels' => $reservationsBar['labels'],
            'data'   => $reservationsBar['data'],
        ];
        $payload['charts']['reservation_trend'] = [
            'labels' => $reservationTrend['labels'],
            'data'   => $reservationTrend['data'],
        ];
    } elseif (in_array($role, ['faculty', 'vip'], true)) {
        $facultyReservations = fetchReservationStatusCounts($pdo, $uid);
        $payload['charts']['my_reservations'] = [
            'labels' => ['Pending', 'Approved', 'Rejected'],
            'data'   => [
                $facultyReservations['Pending'],
                $facultyReservations['Approved'],
                $facultyReservations['Rejected'],
            ],
        ];
        $roomCounts = fetchRoomStatusCounts($pdo);
        $payload['charts']['room_status'] = [
            'labels' => ['Available', 'Occupied', 'Under Maintenance'],
            'data'   => [$roomCounts['Available'], $roomCounts['Occupied'], $roomCounts['Under Maintenance']],
        ];
    } else {
        $equipmentCounts = fetchEquipmentStatusCounts($pdo);
        $conditionCounts = [
            'Good'          => 0,
            'Damaged'       => 0,
            'Repair Needed' => 0,
        ];

        $stmt = $pdo->query('SELECT status, COUNT(*) AS total FROM equipment GROUP BY status');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status'] ?? 'Available';
            $total  = (int) ($row['total'] ?? 0);
            if ($status === 'Available' || $status === 'In Use') {
                $conditionCounts['Good'] += $total;
            } elseif ($status === 'Damaged') {
                $conditionCounts['Damaged'] += $total;
            } elseif ($status === 'Under Repair' || $status === 'For Disposal') {
                $conditionCounts['Repair Needed'] += $total;
            }
        }

        $payload['charts']['condition_overview'] = [
            'labels' => ['Good', 'Damaged', 'Repair Needed'],
            'data'   => [$conditionCounts['Good'], $conditionCounts['Damaged'], $conditionCounts['Repair Needed']],
        ];
        $payload['charts']['equipment_status'] = [
            'labels' => ['Available', 'In-use', 'Maintenance'],
            'data'   => [$equipmentCounts['Available'], $equipmentCounts['Borrowed'], $equipmentCounts['Maintenance']],
        ];
        $payload['charts']['borrowed_top'] = fetchTopBorrowedEquipment($pdo, 5);
    }

    jsonResponse(true, 'OK', $payload);
} catch (Throwable $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
