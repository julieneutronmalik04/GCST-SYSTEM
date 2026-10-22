<?php
/**
 * Predictive Analytics Module
 * 
 * Implements real predictive analytics techniques:
 * - Linear Regression: Models linear trend in historical data
 * - Moving Averages: Smooths noisy data to identify underlying trends
 * - Time-Series Forecasting: Extends trends into future periods
 * 
 * Academic Foundation:
 * - Linear regression (y = mx + b) fits historical data to predict future values
 * - Moving averages reduce noise while preserving trends
 * - Forecasting extends these models into future periods
 */

require_once __DIR__ . '/../config/db.php';

const PREDICTIVE_MIN_RESERVATION_DAILY_BUCKETS = 7;
const PREDICTIVE_MIN_RESERVATION_MONTHLY_BUCKETS = 3;
const PREDICTIVE_MIN_EQUIPMENT_DAILY_BUCKETS = 7;

function predictiveAnalyticsLog(string $event, array $context = []): void
{
    $encoded = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    error_log('[predictive_analytics] ' . $event . ($encoded ? ' ' . $encoded : ''));
}

function insufficientPredictionData(string $message, int $requiredRecords, int $availableRecords): array
{
    return [
        'success' => false,
        'status' => 'insufficient_data',
        'message' => $message,
        'data' => [],
        'forecast' => [],
        'regression' => null,
        'trend_direction' => 'insufficient_data',
        'required_records' => $requiredRecords,
        'available_records' => $availableRecords,
    ];
}

function predictionHasForecast(?array $prediction): bool
{
    return is_array($prediction)
        && ($prediction['status'] ?? '') === 'success'
        && !empty($prediction['forecast'])
        && is_array($prediction['forecast']);
}

/**
 * Linear Regression Analysis
 * Fits a line (y = mx + b) through historical data points
 * Returns slope (m), intercept (b), and R-squared (fit quality)
 * 
 * @param array $xValues X-axis values (e.g., day numbers 1, 2, 3...)
 * @param array $yValues Y-axis values (e.g., reservation counts)
 * @return array ['slope' => m, 'intercept' => b, 'r_squared' => R²]
 */
function linearRegression(array $xValues, array $yValues): array
{
    $n = count($xValues);
    if ($n < 2) {
        predictiveAnalyticsLog('linear_regression_skipped', [
            'reason' => 'fewer_than_two_points',
            'x_values' => $xValues,
            'y_values' => $yValues,
        ]);
        return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];
    }

    // Calculate means
    $xMean = array_sum($xValues) / $n;
    $yMean = array_sum($yValues) / $n;

    // Calculate slope: m = Σ((x - x̄)(y - ȳ)) / Σ((x - x̄)²)
    $numerator = 0;
    $denominator = 0;
    $ssTotal = 0;
    $ssResidual = 0;

    for ($i = 0; $i < $n; $i++) {
        $xDiff = $xValues[$i] - $xMean;
        $yDiff = $yValues[$i] - $yMean;
        $numerator += $xDiff * $yDiff;
        $denominator += $xDiff * $xDiff;
        $ssTotal += $yDiff * $yDiff;
    }

    $slope = $denominator != 0 ? $numerator / $denominator : 0;
    $intercept = $yMean - ($slope * $xMean);

    // Calculate R-squared: 1 - (SSRes / SSTotal)
    for ($i = 0; $i < $n; $i++) {
        $predicted = $slope * $xValues[$i] + $intercept;
        $ssResidual += pow($yValues[$i] - $predicted, 2);
    }

    $rSquared = $ssTotal > 0 ? 1 - ($ssResidual / $ssTotal) : 0;

    $result = [
        'slope' => round($slope, 4),
        'intercept' => round($intercept, 2),
        'r_squared' => round(max(0, $rSquared), 4),
    ];

    predictiveAnalyticsLog('linear_regression_completed', [
        'point_count' => $n,
        'x_values' => $xValues,
        'y_values' => $yValues,
        'x_mean' => round($xMean, 4),
        'y_mean' => round($yMean, 4),
        'numerator' => round($numerator, 4),
        'denominator' => round($denominator, 4),
        'result' => $result,
    ]);

    return $result;
}

/**
 * Simple Moving Average
 * Smooths data by averaging over a specified period
 * Useful for identifying underlying trends by removing noise
 * 
 * @param array $data Historical data points
 * @param int $period Window size for averaging
 * @return array Smoothed data
 */
function movingAverage(array $data, int $period = 3): array
{
    if ($period < 1) $period = 1;
    $smoothed = [];

    for ($i = 0; $i < count($data); $i++) {
        $start = max(0, $i - $period + 1);
        $window = array_slice($data, $start, $i - $start + 1);
        $smoothed[] = round(array_sum($window) / count($window), 2);
    }

    return $smoothed;
}

/**
 * Forecast Reservation Demand
 * Uses linear regression on historical reservation data to predict future demand
 * 
 * Academic Method:
 * 1. Aggregate historical daily/monthly reservations from database
 * 2. Apply linear regression to identify trend (slope)
 * 3. Project trend forward into future periods
 * 4. Return forecasted values with confidence indicators
 * 
 * @param PDO $pdo Database connection
 * @param int $daysAhead Number of days to forecast
 * @param string $period 'day' or 'month'
 * @return array ['forecast' => [...], 'regression' => [...], 'trend_direction' => 'increasing|decreasing|stable']
 */
function forecastReservationDemand(PDO $pdo, int $daysAhead = 30, string $period = 'month'): array
{
    $period = $period === 'day' ? 'day' : 'month';
    $bucketExpr = $period === 'day' ? 'DATE(res_date)' : 'DATE_FORMAT(res_date, "%Y-%m")';
    
    // Get historical data: last 12 months or 90 days
    $historySize = $period === 'day' ? 90 : 12;
    $start = $period === 'day'
        ? (new DateTimeImmutable('today'))->modify('-' . ($historySize - 1) . ' days')->format('Y-m-d')
        : (new DateTimeImmutable('first day of this month'))->modify('-' . ($historySize - 1) . ' months')->format('Y-m-01');

    $stmt = $pdo->prepare("
        SELECT {$bucketExpr} AS bucket, COUNT(*) AS total
        FROM reservations
        WHERE res_date >= ? AND res_date <= CURDATE() AND status = 'Approved'
        GROUP BY bucket
        ORDER BY bucket ASC
    ");
    $stmt->execute([$start]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $requiredRecords = $period === 'day'
        ? PREDICTIVE_MIN_RESERVATION_DAILY_BUCKETS
        : PREDICTIVE_MIN_RESERVATION_MONTHLY_BUCKETS;
    $availableRecords = count($data);

    predictiveAnalyticsLog('reservation_history_loaded', [
        'period' => $period,
        'start' => $start,
        'required_records' => $requiredRecords,
        'available_records' => $availableRecords,
        'rows' => $data,
    ]);

    if ($availableRecords < $requiredRecords) {
        return insufficientPredictionData(
            'Not enough historical reservation data for prediction generation.',
            $requiredRecords,
            $availableRecords
        );
    }

    // Prepare data for regression
    $xValues = array_values(array_map('intval', array_keys($data)));  // 0, 1, 2, 3...
    $yValues = array_values(array_map('intval', array_column($data, 'total')));

    predictiveAnalyticsLog('reservation_regression_inputs', [
        'period' => $period,
        'grouped_dataset_count' => count($data),
        'x_values' => $xValues,
        'y_values' => $yValues,
        'buckets' => array_column($data, 'bucket'),
    ]);

    // Perform linear regression
    $regression = linearRegression($xValues, $yValues);

    // Determine trend direction
    $trendDirection = 'stable';
    if ($regression['slope'] > 0.1) {
        $trendDirection = 'increasing';
    } elseif ($regression['slope'] < -0.1) {
        $trendDirection = 'decreasing';
    }

    // Generate forecast for future periods
    $forecast = [];
    $currentIndex = count($data);

    for ($i = 1; $i <= $daysAhead; $i++) {
        $futureIndex = $currentIndex + $i - 1;
        $predictedValue = round($regression['slope'] * $futureIndex + $regression['intercept']);
        $predictedValue = max(0, $predictedValue);  // Prevent negative predictions

        // Calculate date for forecast
        if ($period === 'day') {
            $date = (new DateTimeImmutable('today'))->modify('+' . $i . ' days');
            $label = $date->format('M j');
        } else {
            $date = (new DateTimeImmutable('first day of this month'))->modify('+' . ($i - 1) . ' months');
            $label = $date->format('M Y');
        }

        $forecast[] = [
            'label' => $label,
            'predicted' => $predictedValue,
            'confidence' => round($regression['r_squared'] * 100),  // R² as confidence indicator
        ];
    }

    $result = [
        'success' => true,
        'status' => 'success',
        'message' => 'Reservation forecast generated successfully.',
        'forecast' => array_slice($forecast, 0, min($daysAhead, 12)),
        'regression' => $regression,
        'trend_direction' => $trendDirection,
        'historical_data' => array_map(fn($d) => (int) $d['total'], $data),
        'required_records' => $requiredRecords,
        'available_records' => $availableRecords,
    ];

    predictiveAnalyticsLog('reservation_forecast_generated', [
        'period' => $period,
        'forecast_count' => count($result['forecast']),
        'regression' => $regression,
        'trend_direction' => $trendDirection,
        'forecast' => $result['forecast'],
    ]);

    return $result;
}

/**
 * Forecast Equipment Demand
 * Predicts future equipment borrowing patterns based on historical usage
 * 
 * @param PDO $pdo Database connection
 * @param int $daysAhead Number of days to forecast
 * @return array Equipment demand predictions
 */
function forecastEquipmentDemand(PDO $pdo, int $daysAhead = 30): array
{
    // Get daily equipment borrowing counts for last 90 days
    $start = (new DateTimeImmutable('today'))->modify('-89 days')->format('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT DATE(borrow_date) AS bucket, COUNT(*) AS total
        FROM borrowed
        WHERE borrow_date >= ? AND borrow_date <= CURDATE() AND status IN ('Borrowed', 'Overdue', 'Returned', 'Returned (Damaged)')
        GROUP BY bucket
        ORDER BY bucket ASC
    ");
    $stmt->execute([$start]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $availableRecords = count($data);

    predictiveAnalyticsLog('equipment_history_loaded', [
        'start' => $start,
        'required_records' => PREDICTIVE_MIN_EQUIPMENT_DAILY_BUCKETS,
        'available_records' => $availableRecords,
        'rows' => $data,
    ]);

    if ($availableRecords < PREDICTIVE_MIN_EQUIPMENT_DAILY_BUCKETS) {
        return insufficientPredictionData(
            'Not enough historical equipment borrowing data for prediction generation.',
            PREDICTIVE_MIN_EQUIPMENT_DAILY_BUCKETS,
            $availableRecords
        );
    }

    // Apply moving average to smooth noise
    $rawValues = array_values(array_map('intval', array_column($data, 'total')));
    $smoothedValues = movingAverage($rawValues, 7);  // 7-day moving average

    // Perform regression on smoothed data
    $xValues = array_values(array_map('intval', array_keys($smoothedValues)));

    predictiveAnalyticsLog('equipment_regression_inputs', [
        'grouped_dataset_count' => count($data),
        'x_values' => $xValues,
        'raw_y_values' => $rawValues,
        'smoothed_y_values' => $smoothedValues,
        'buckets' => array_column($data, 'bucket'),
    ]);

    $regression = linearRegression($xValues, $smoothedValues);

    // Determine trend
    $trendDirection = 'stable';
    if ($regression['slope'] > 0.05) {
        $trendDirection = 'increasing';
    } elseif ($regression['slope'] < -0.05) {
        $trendDirection = 'decreasing';
    }

    // Generate forecast
    $forecast = [];
    $currentIndex = count($smoothedValues);

    for ($i = 1; $i <= $daysAhead; $i++) {
        $futureIndex = $currentIndex + $i - 1;
        $predictedValue = round($regression['slope'] * $futureIndex + $regression['intercept']);
        $predictedValue = max(0, $predictedValue);

        $date = (new DateTimeImmutable('today'))->modify('+' . $i . ' days');

        $forecast[] = [
            'label' => $date->format('M j'),
            'predicted' => $predictedValue,
            'confidence' => round($regression['r_squared'] * 100),
        ];
    }

    $result = [
        'success' => true,
        'status' => 'success',
        'message' => 'Equipment demand forecast generated successfully.',
        'forecast' => array_slice($forecast, 0, min($daysAhead, 30)),
        'regression' => $regression,
        'trend_direction' => $trendDirection,
        'historical_smoothed' => $smoothedValues,
        'required_records' => PREDICTIVE_MIN_EQUIPMENT_DAILY_BUCKETS,
        'available_records' => $availableRecords,
    ];

    predictiveAnalyticsLog('equipment_forecast_generated', [
        'forecast_count' => count($result['forecast']),
        'regression' => $regression,
        'trend_direction' => $trendDirection,
        'forecast' => $result['forecast'],
    ]);

    return $result;
}

/**
 * Predict Most Frequently Used Rooms
 * Analyzes historical reservation patterns to identify peak-use rooms
 * 
 * @param PDO $pdo Database connection
 * @param int $lookbackDays Days of history to analyze
 * @return array Top rooms with usage frequency and trend
 */
function predictMostUsedRooms(PDO $pdo, int $lookbackDays = 90): array
{
    $start = (new DateTimeImmutable('today'))->modify('-' . ($lookbackDays - 1) . ' days')->format('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT r.room_code, r.room_name, COUNT(*) AS total_reservations,
               COUNT(DISTINCT DATE(res.res_date)) AS days_used,
               COUNT(*) / COUNT(DISTINCT DATE(res.res_date)) AS avg_per_day
        FROM reservations res
        JOIN rooms r ON (res.room_id = r.id OR res.room_code = r.room_code OR res.room_code = r.room_id)
        WHERE res.res_date >= ? AND res.res_date <= CURDATE() AND res.status = 'Approved'
        GROUP BY r.id, r.room_code, r.room_name
        ORDER BY total_reservations DESC
        LIMIT 10
    ");
    $stmt->execute([$start]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Predict Most Frequently Borrowed Equipment
 * Analyzes equipment usage patterns to forecast high-demand items
 * 
 * @param PDO $pdo Database connection
 * @param int $lookbackDays Days of history to analyze
 * @return array Top equipment with usage patterns
 */
function predictMostBorrowedEquipment(PDO $pdo, int $lookbackDays = 90): array
{
    $start = (new DateTimeImmutable('today'))->modify('-' . ($lookbackDays - 1) . ' days')->format('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT e.equip_code, e.name, COUNT(*) AS total_borrows,
               COUNT(DISTINCT DATE(b.borrow_date)) AS days_borrowed,
               COUNT(*) / COUNT(DISTINCT DATE(b.borrow_date)) AS avg_per_day
        FROM borrowed b
        JOIN equipment e ON b.equipment_id = e.id
        WHERE DATE(b.borrow_date) >= ? AND DATE(b.borrow_date) <= CURDATE() AND b.status IN ('Borrowed', 'Overdue', 'Returned', 'Returned (Damaged)')
        GROUP BY e.id, e.equip_code, e.name
        ORDER BY total_borrows DESC
        LIMIT 10
    ");
    $stmt->execute([$start]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate Peak Usage Periods
 * Identifies which days of week and times have highest demand
 * 
 * @param PDO $pdo Database connection
 * @return array Peak periods by day of week
 */
function calculatePeakPeriods(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT DAYNAME(res_date) AS day_name,
               HOUR(time_start) AS hour,
               COUNT(*) AS reservation_count
        FROM reservations
        WHERE res_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
          AND res_date <= CURDATE()
          AND status = 'Approved'
        GROUP BY DAYNAME(res_date), HOUR(time_start)
        ORDER BY reservation_count DESC
        LIMIT 15
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Generate Reservation Forecast Summary
 * Provides actionable insights about upcoming demand
 * 
 * @param PDO $pdo Database connection
 * @return array Forecast summary with alerts
 */
function generateForecastSummary(PDO $pdo): array
{
    $forecast = forecastReservationDemand($pdo, 30, 'day');
    
    if (!predictionHasForecast($forecast)) {
        return [
            'success' => false,
            'status' => 'insufficient_data',
            'message' => $forecast['message'] ?? 'Not enough historical data for forecast summary.',
            'data' => [],
            'required_records' => $forecast['required_records'] ?? PREDICTIVE_MIN_RESERVATION_DAILY_BUCKETS,
            'available_records' => $forecast['available_records'] ?? 0,
        ];
    }

    $predictions = array_column($forecast['forecast'], 'predicted');
    $avgPredicted = round(array_sum($predictions) / count($predictions));
    $maxPredicted = max($predictions);
    $minPredicted = min($predictions);

    // Identify peak day
    $peakDay = null;
    $peakValue = 0;
    foreach ($forecast['forecast'] as $item) {
        if ($item['predicted'] > $peakValue) {
            $peakValue = $item['predicted'];
            $peakDay = $item['label'];
        }
    }

    return [
        'status' => 'success',
        'trend' => $forecast['trend_direction'],
        'average_daily_forecast' => $avgPredicted,
        'predicted_peak_day' => $peakDay,
        'predicted_peak_value' => $peakValue,
        'forecast_range' => ['min' => $minPredicted, 'max' => $maxPredicted],
        'model_confidence' => $forecast['regression']['r_squared'] > 0.5 ? 'High' : 'Moderate',
        'regression' => $forecast['regression'],
    ];
}
