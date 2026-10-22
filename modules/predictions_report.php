<?php
/**
 * Predictive Analytics Report Endpoint
 * Returns predictive analytics data for dashboard visualization
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../config/session.php';
    require_once __DIR__ . '/predictive_analytics.php';

    requireLogin(['admin', 'faculty', 'vip', 'maintenance']);

    $pdo = getDB();
    $role = getRole();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        jsonResponse(false, 'Method not allowed.', [], 405);
    }

    $report = $_GET['report'] ?? 'all';

    if ($report === 'reservation_forecast' || $report === 'all') {
        $reservationForecast = forecastReservationDemand($pdo, 30, 'month');
        $summary = generateForecastSummary($pdo);
    }

    if ($report === 'equipment_forecast' || $report === 'all') {
        $equipmentForecast = forecastEquipmentDemand($pdo, 30);
    }

    if ($report === 'top_items' || $report === 'all') {
        $topRooms = predictMostUsedRooms($pdo, 90);
        $topEquipment = predictMostBorrowedEquipment($pdo, 90);
    }

    if ($report === 'peak_periods' || $report === 'all') {
        $peakPeriods = calculatePeakPeriods($pdo);
    }

    $response = [];

    if ($report === 'reservation_forecast' || $report === 'all') {
        $response['reservation_forecast'] = $reservationForecast ?? null;
        $response['forecast_summary'] = $summary ?? null;
    }

    if ($report === 'equipment_forecast' || $report === 'all') {
        $response['equipment_forecast'] = $equipmentForecast ?? null;
    }

    if ($report === 'top_items' || $report === 'all') {
        $response['top_rooms'] = $topRooms ?? null;
        $response['top_equipment'] = $topEquipment ?? null;
    }

    if ($report === 'peak_periods' || $report === 'all') {
        $response['peak_periods'] = $peakPeriods ?? null;
    }

    $hasReservationForecast = isset($response['reservation_forecast'])
        && predictionHasForecast($response['reservation_forecast']);
    $hasEquipmentForecast = isset($response['equipment_forecast'])
        && predictionHasForecast($response['equipment_forecast']);

    $apiPayload = [
        'data' => $response,
        'predictions' => $response,
        'forecast' => [
            'reservation' => $response['reservation_forecast']['forecast'] ?? [],
            'equipment' => $response['equipment_forecast']['forecast'] ?? [],
        ],
    ] + $response;

    predictiveAnalyticsLog('predictions_report_response', [
        'role' => $role,
        'report' => $report,
        'success' => $hasReservationForecast || $hasEquipmentForecast || $report !== 'all',
        'reservation_forecast_count' => isset($response['reservation_forecast']['forecast']) && is_array($response['reservation_forecast']['forecast'])
            ? count($response['reservation_forecast']['forecast'])
            : 0,
        'equipment_forecast_count' => isset($response['equipment_forecast']['forecast']) && is_array($response['equipment_forecast']['forecast'])
            ? count($response['equipment_forecast']['forecast'])
            : 0,
        'top_rooms_count' => isset($response['top_rooms']) && is_array($response['top_rooms']) ? count($response['top_rooms']) : 0,
        'top_equipment_count' => isset($response['top_equipment']) && is_array($response['top_equipment']) ? count($response['top_equipment']) : 0,
        'response' => $response,
        'api_payload_keys' => array_keys($apiPayload),
    ]);

    jsonResponse(true, 'OK', $apiPayload);

} catch (Throwable $e) {
    http_response_code(500);
    jsonResponse(false, 'Error generating predictions: ' . $e->getMessage());
}
