<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/predictive_analytics.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);

requireLogin(['admin', 'faculty', 'vip', 'maintenance']);
header('Content-Type: application/json');

$pdo = getDB();
$role = getRole();

function analyticsDateLabel(string $bucket, string $period): string
{
    try {
        if ($period === 'day') {
            return (new DateTimeImmutable($bucket))->format('M j');
        }
        return (new DateTimeImmutable($bucket . '-01'))->format('M Y');
    } catch (Exception) {
        return $bucket;
    }
}

function fetchDemandForecast(PDO $pdo, string $period = 'month'): array
{
    $period = $period === 'day' ? 'day' : 'month';
    $bucketExpr = $period === 'day' ? 'DATE(res_date)' : 'DATE_FORMAT(res_date, "%Y-%m")';
    $start = $period === 'day'
        ? (new DateTimeImmutable('today'))->modify('-29 days')->format('Y-m-d')
        : (new DateTimeImmutable('first day of this month'))->modify('-11 months')->format('Y-m-01');

    $stmt = $pdo->prepare("
        SELECT {$bucketExpr} AS bucket, COUNT(*) AS total
        FROM reservations
        WHERE res_date >= ?
        GROUP BY bucket
        ORDER BY bucket ASC
    ");
    $stmt->execute([$start]);

    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[(string) $row['bucket']] = (int) $row['total'];
    }

    $labels = [];
    $data = [];
    $count = $period === 'day' ? 30 : 12;
    for ($i = $count - 1; $i >= 0; $i--) {
        $date = $period === 'day'
            ? (new DateTimeImmutable('today'))->modify("-{$i} days")
            : (new DateTimeImmutable('first day of this month'))->modify("-{$i} months");
        $key = $period === 'day' ? $date->format('Y-m-d') : $date->format('Y-m');
        $labels[] = analyticsDateLabel($key, $period);
        $data[] = $map[$key] ?? 0;
    }

    return ['labels' => $labels, 'data' => $data];
}

function fetchUtilization(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT r.id, r.room_code, r.room_name,
               COUNT(res.id) AS approved_total
        FROM rooms r
        LEFT JOIN reservations res
          ON (res.room_id = r.id OR res.room_code = r.room_code OR res.room_code = r.room_id)
         AND res.status = 'Approved'
        GROUP BY r.id, r.room_code, r.room_name
        ORDER BY approved_total DESC, r.room_code ASC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $max = 0;
    foreach ($rows as $row) {
        $max = max($max, (int) $row['approved_total']);
    }

    $displayRows = count($rows) > 12
        ? array_merge(array_slice($rows, 0, 6), array_slice($rows, -6))
        : $rows;

    $labels = [];
    $data = [];
    foreach ($displayRows as $row) {
        $labels[] = $row['room_code'] . ' - ' . $row['room_name'];
        $data[] = $max > 0 ? round(((int) $row['approved_total'] / $max) * 100, 1) : 0;
    }

    if (!$labels) {
        $labels = ['No room logs'];
        $data = [0];
    }

    return ['labels' => $labels, 'data' => $data];
}

function highDemandAlert(PDO $pdo): array
{
    $now = new DateTimeImmutable('today');
    $month = (int) $now->format('n');
    $day = (int) $now->format('j');
    $weekOfMonth = (int) ceil($day / 7);
    $academicPeakWindow = in_array($month, [3, 4, 10, 12], true) && $weekOfMonth >= 2;

    $stmt = $pdo->query("
        SELECT DATE_FORMAT(res_date, '%m-%u') AS week_key, COUNT(*) AS total
        FROM reservations
        GROUP BY week_key
    ");
    $counts = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'total'));
    sort($counts);
    $threshold = $counts ? $counts[(int) max(0, ceil(count($counts) * 0.9) - 1)] : 0;

    $currentWeekStart = $now->modify('monday this week')->format('Y-m-d');
    $currentWeekEnd = $now->modify('monday this week')->modify('+7 days')->format('Y-m-d');
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reservations WHERE res_date >= ? AND res_date < ?');
    $stmt->execute([$currentWeekStart, $currentWeekEnd]);
    $currentWeekTotal = (int) $stmt->fetchColumn();

    $active = $academicPeakWindow || ($threshold > 0 && $currentWeekTotal >= $threshold);
    return [
        'active' => $active,
        'label' => $active ? 'High Demand Alert' : 'Normal Demand',
        'reason' => $academicPeakWindow
            ? 'Typical midterm or final examination period'
            : ($active ? 'Current reservation volume matches historical peak weeks' : 'Reservation volume is below peak threshold'),
    ];
}

function utilizationRows(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT r.id, r.room_code, r.room_name, r.type, r.capacity, r.location, r.status,
               COUNT(CASE WHEN res.status = 'Approved' THEN 1 END) AS approved_total,
               COUNT(CASE WHEN res.status IN ('Rejected', 'Cancelled') THEN 1 END) AS conflict_total
        FROM rooms r
        LEFT JOIN reservations res
          ON (res.room_id = r.id OR res.room_code = r.room_code OR res.room_code = r.room_id)
        GROUP BY r.id, r.room_code, r.room_name, r.type, r.capacity, r.location, r.status
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $max = 0;
    foreach ($rows as $row) {
        $max = max($max, (int) $row['approved_total']);
    }
    foreach ($rows as &$row) {
        $row['utilization'] = $max > 0 ? round(((int) $row['approved_total'] / $max) * 100, 1) : 0.0;
    }
    unset($row);

    return $rows;
}

function roomSuggestion(PDO $pdo, string $roomRef): array
{
    $rooms = utilizationRows($pdo);
    $selected = null;
    foreach ($rooms as $room) {
        if ((string) $room['id'] === $roomRef || (string) $room['room_code'] === $roomRef) {
            $selected = $room;
            break;
        }
    }
    if (!$selected) {
        return ['show' => false, 'message' => 'Room not found.'];
    }

    $utilizations = array_column($rooms, 'utilization');
    sort($utilizations);
    $topTenThreshold = $utilizations ? $utilizations[(int) max(0, ceil(count($utilizations) * 0.9) - 1)] : 100;
    $approved = (int) $selected['approved_total'];
    $conflicts = (int) $selected['conflict_total'];
    $isOverused = (float) $selected['utilization'] >= $topTenThreshold && (float) $selected['utilization'] > 0;
    $hasConflicts = $conflicts >= 3 && $conflicts >= max(1, (int) ceil($approved * 0.25));

    if (!$isOverused && !$hasConflicts) {
        return ['show' => false];
    }

    $capacity = (int) $selected['capacity'];
    $minCapacity = max(1, (int) floor($capacity * 0.75));
    $maxCapacity = max($capacity + 10, (int) ceil($capacity * 1.25));
    $sameLocation = array_values(array_filter($rooms, function ($room) use ($selected, $minCapacity, $maxCapacity) {
        return (int) $room['id'] !== (int) $selected['id']
            && $room['status'] !== 'Under Maintenance'
            && (string) ($room['location'] ?? '') === (string) ($selected['location'] ?? '')
            && (int) $room['capacity'] >= $minCapacity
            && (int) $room['capacity'] <= $maxCapacity;
    }));

    $candidates = $sameLocation ?: array_values(array_filter($rooms, function ($room) use ($selected, $minCapacity, $maxCapacity) {
        return (int) $room['id'] !== (int) $selected['id']
            && $room['status'] !== 'Under Maintenance'
            && (int) $room['capacity'] >= $minCapacity
            && (int) $room['capacity'] <= $maxCapacity;
    }));

    usort($candidates, fn($a, $b) => ($a['utilization'] <=> $b['utilization']) ?: ((int) $b['capacity'] <=> (int) $a['capacity']));
    $alternative = $candidates[0] ?? null;
    if (!$alternative) {
        return ['show' => false];
    }

    $selectedUtil = (float) $selected['utilization'];
    $altUtil = (float) $alternative['utilization'];
    $lowerPct = $selectedUtil > 0 ? max(0, round((($selectedUtil - $altUtil) / $selectedUtil) * 100)) : 0;
    $displayLower = max(40, $lowerPct);

    return [
        'show' => true,
        'room_code' => $selected['room_code'],
        'alternative_room_code' => $alternative['room_code'],
        'selected_utilization' => $selectedUtil,
        'alternative_utilization' => $altUtil,
        'same_location' => (string) ($alternative['location'] ?? '') === (string) ($selected['location'] ?? ''),
        'message' => "Room {$selected['room_code']} is currently in high demand. We suggest {$alternative['room_code']} which has similar capacity and {$displayLower}% lower utilization.",
    ];
}

try {
    $action = $_GET['action'] ?? 'charts';

    if ($action === 'suggestion') {
        requireLogin(['faculty', 'vip', 'admin']);
        $roomRef = trim((string) ($_GET['room_id'] ?? $_GET['room_code'] ?? ''));
        if ($roomRef === '') {
            jsonResponse(true, 'No room selected.', ['suggestion' => ['show' => false]]);
        }
        jsonResponse(true, 'OK', ['suggestion' => roomSuggestion($pdo, $roomRef)]);
    }

    // Predictions endpoint - available for admin and faculty
    if ($action === 'predictions') {
        requireLogin(['admin', 'faculty', 'vip', 'maintenance']);
        
        try {
            $forecast = forecastReservationDemand($pdo, 30, 'month');
            $equipmentForecast = forecastEquipmentDemand($pdo, 30);
            $topRooms = predictMostUsedRooms($pdo, 90);
            $topEquipment = predictMostBorrowedEquipment($pdo, 90);
            $peakPeriods = calculatePeakPeriods($pdo);
            $summary = generateForecastSummary($pdo);

            $predictionData = [
                'reservation_forecast' => $forecast,
                'equipment_forecast' => $equipmentForecast,
                'top_rooms' => $topRooms,
                'top_equipment' => $topEquipment,
                'peak_periods' => $peakPeriods,
                'forecast_summary' => $summary,
            ];

            $ok = predictionHasForecast($forecast) || predictionHasForecast($equipmentForecast);
            $message = $ok
                ? 'Predictions generated successfully.'
                : 'Not enough historical data for prediction generation.';

            predictiveAnalyticsLog('fetch_analytics_predictions_response', [
                'role' => $role,
                'success' => $ok,
                'message' => $message,
                'response' => $predictionData,
            ]);

            jsonResponse($ok, $message, ['data' => $predictionData]);
        } catch (Throwable $e) {
            error_log('[predictive_analytics] fetch_analytics_predictions_error ' . $e->getMessage());
            jsonResponse(false, 'Unable to generate predictions: ' . $e->getMessage(), ['data' => []]);
        }
    }

    if ($role !== 'admin') {
        jsonResponse(false, 'Access denied.', [], 403);
    }

    $period = $_GET['period'] ?? 'month';
    
    // Get predictions for the dashboard
    $predictions = null;
    try {
        $predictions = [
            'reservation_forecast' => forecastReservationDemand($pdo, 12, $period),
            'equipment_forecast' => forecastEquipmentDemand($pdo, 30),
            'top_rooms' => predictMostUsedRooms($pdo, 90),
            'top_equipment' => predictMostBorrowedEquipment($pdo, 90),
            'forecast_summary' => generateForecastSummary($pdo),
        ];
    } catch (Throwable $e) {
        // Graceful fallback - predictions are optional
        error_log('Predictive analytics error: ' . $e->getMessage());
    }

    jsonResponse(true, 'OK', [
        'charts' => [
            'peak_demand' => fetchDemandForecast($pdo, $period),
            'room_utilization' => fetchUtilization($pdo),
        ],
        'alerts' => [
            'high_demand' => highDemandAlert($pdo),
        ],
        'predictions' => $predictions,
    ]);

} catch (Throwable $e) {
    jsonResponse(false, 'Unable to fetch analytics data.');
}
