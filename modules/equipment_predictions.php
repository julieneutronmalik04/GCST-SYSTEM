<?php

require_once __DIR__ . '/../config/db.php';

function ensureEquipmentPredictionsTable(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS equipment_predictions (
            equipment_id INT NOT NULL PRIMARY KEY,
            usage_count INT NOT NULL DEFAULT 0,
            usage_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
            condition_reports INT NOT NULL DEFAULT 0,
            severe_reports INT NOT NULL DEFAULT 0,
            health_score INT NOT NULL DEFAULT 100,
            health_status VARCHAR(40) NOT NULL DEFAULT 'Healthy',
            lifecycle_percent INT NOT NULL DEFAULT 100,
            maintenance_prediction VARCHAR(80) DEFAULT NULL,
            is_consumable TINYINT(1) NOT NULL DEFAULT 0,
            burn_rate_days DECIMAL(10,2) DEFAULT NULL,
            reorder_within_days INT DEFAULT NULL,
            replenishment_message VARCHAR(255) DEFAULT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}

function isConsumableAsset(array $equipment): bool
{
    $text = strtolower(($equipment['name'] ?? '') . ' ' . ($equipment['description'] ?? ''));
    return (bool) preg_match('/\b(cable|hdmi|battery|batteries|marker|ink|toner|paper|chalk|adapter|connector|consumable)\b/', $text);
}

function notifyPredictionIfNeeded(PDO $pdo, array $equipment, string $prediction): void
{
    if ($prediction === '') {
        return;
    }

    $title = 'Maintenance Predicted';
    $body = "Maintenance Predicted: Next Month for {$equipment['equip_code']} - {$equipment['name']}.";
    $monthStart = date('Y-m-01 00:00:00');

    foreach (['admin_notifications', 'maintenance_notifications'] as $table) {
        try {
            $check = $pdo->prepare("SELECT id FROM {$table} WHERE title = ? AND body = ? AND created_at >= ? LIMIT 1");
            $check->execute([$title, $body, $monthStart]);
            if (!$check->fetch()) {
                $pdo->prepare("INSERT INTO {$table} (user_id, type, title, body, link_ref) VALUES (NULL, 'prediction', ?, ?, 'equipment')")
                    ->execute([$title, $body]);
            }
        } catch (Throwable) {
        }
    }
}

function updateEquipmentPredictions(PDO $pdo, ?int $equipmentId = null): void
{
    ensureEquipmentPredictionsTable($pdo);

    $sql = 'SELECT * FROM equipment';
    $params = [];
    if ($equipmentId !== null) {
        $sql .= ' WHERE id = ?';
        $params[] = $equipmentId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipmentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $upsert = $pdo->prepare("
        INSERT INTO equipment_predictions (
            equipment_id, usage_count, usage_hours, condition_reports, severe_reports,
            health_score, health_status, lifecycle_percent, maintenance_prediction,
            is_consumable, burn_rate_days, reorder_within_days, replenishment_message
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            usage_count = VALUES(usage_count),
            usage_hours = VALUES(usage_hours),
            condition_reports = VALUES(condition_reports),
            severe_reports = VALUES(severe_reports),
            health_score = VALUES(health_score),
            health_status = VALUES(health_status),
            lifecycle_percent = VALUES(lifecycle_percent),
            maintenance_prediction = VALUES(maintenance_prediction),
            is_consumable = VALUES(is_consumable),
            burn_rate_days = VALUES(burn_rate_days),
            reorder_within_days = VALUES(reorder_within_days),
            replenishment_message = VALUES(replenishment_message),
            updated_at = NOW()
    ");

    foreach ($equipmentRows as $equipment) {
        $id = (int) $equipment['id'];

        $usageStmt = $pdo->prepare("
            SELECT COUNT(*) AS usage_count,
                   COALESCE(SUM(
                       GREATEST(0, TIMESTAMPDIFF(MINUTE,
                           TIMESTAMP(borrow_date, time_out),
                           COALESCE(actual_time_in, TIMESTAMP(expected_return_date, expected_return_time))
                       )) / 60
                   ), 0) AS usage_hours
            FROM borrowed
            WHERE equipment_id = ?
        ");
        $usageStmt->execute([$id]);
        $usage = $usageStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $usageCount = (int) ($usage['usage_count'] ?? 0);
        $usageHours = round((float) ($usage['usage_hours'] ?? 0), 2);

        $reportStmt = $pdo->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN severity = 'High' OR action_needed IN ('Replace', 'Decommission') THEN 1 ELSE 0 END) AS severe
            FROM condition_reports
            WHERE equipment_id = ?
        ");
        $reportStmt->execute([$id]);
        $reports = $reportStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $reportCount = (int) ($reports['total'] ?? 0);
        $severeReports = (int) ($reports['severe'] ?? 0);

        $health = 100;
        $health -= min(35, (int) floor($usageHours / 200 * 30));
        $health -= min(35, ($reportCount * 8) + ($severeReports * 10));
        if (($equipment['status'] ?? '') === 'Damaged') {
            $health -= 20;
        } elseif (($equipment['status'] ?? '') === 'Under Repair') {
            $health -= 12;
        }
        $health = max(0, min(100, $health));

        if ($health < 45) {
            $healthStatus = 'Critical';
        } elseif ($usageHours >= 200 || $health < 65) {
            $healthStatus = 'Maintenance Predicted';
        } elseif ($health < 80) {
            $healthStatus = 'Watch';
        } else {
            $healthStatus = 'Healthy';
        }

        $purchaseDate = $equipment['purchased_date'] ?: ($equipment['created_at'] ?? date('Y-m-d'));
        $ageDays = max(0, (int) floor((time() - strtotime($purchaseDate)) / 86400));
        $expectedLifeDays = 5 * 365;
        $ageLifecycle = max(0, 100 - (int) floor(($ageDays / $expectedLifeDays) * 100));
        $lifecyclePercent = max(0, min(100, min($ageLifecycle, $health + 10)));

        $prediction = ($usageHours >= 200 || $health < 65) ? 'Maintenance Predicted: Next Month' : null;
        if ($prediction) {
            notifyPredictionIfNeeded($pdo, $equipment, $prediction);
        }

        $isConsumable = isConsumableAsset($equipment) ? 1 : 0;
        $burnRateDays = null;
        $reorderWithinDays = null;
        $replenishmentMessage = null;

        if ($isConsumable) {
            $burnStmt = $pdo->prepare("
                SELECT AVG(GREATEST(1, TIMESTAMPDIFF(DAY, TIMESTAMP(borrow_date, time_out), actual_time_in))) AS avg_days
                FROM borrowed
                WHERE equipment_id = ?
                  AND status = 'Returned (Damaged)'
                  AND actual_time_in IS NOT NULL
            ");
            $burnStmt->execute([$id]);
            $burnRateDays = $burnStmt->fetchColumn();
            $burnRateDays = $burnRateDays !== false && $burnRateDays !== null ? round((float) $burnRateDays, 1) : null;

            $currentStmt = $pdo->prepare("
                SELECT COUNT(*) FROM borrowed
                WHERE equipment_id = ? AND status = 'Returned (Damaged)'
                  AND actual_time_in >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
            ");
            $currentStmt->execute([$id]);
            $currentBroken = (int) $currentStmt->fetchColumn();

            $previousStmt = $pdo->prepare("
                SELECT COUNT(*) FROM borrowed
                WHERE equipment_id = ? AND status = 'Returned (Damaged)'
                  AND actual_time_in >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
                  AND actual_time_in < DATE_FORMAT(CURDATE(), '%Y-%m-01')
            ");
            $previousStmt->execute([$id]);
            $previousBroken = (int) $previousStmt->fetchColumn();

            $fasterPct = $previousBroken > 0 ? round((($currentBroken - $previousBroken) / $previousBroken) * 100) : ($currentBroken > 0 ? 100 : 0);
            if (($burnRateDays !== null && $burnRateDays <= 15) || $currentBroken > $previousBroken) {
                $reorderWithinDays = $burnRateDays !== null ? max(1, min(15, (int) ceil($burnRateDays))) : 15;
                $replenishmentMessage = "{$equipment['name']} should be reordered within {$reorderWithinDays} days.";
                if ($fasterPct > 0) {
                    $replenishmentMessage = "Inventory Alert: {$equipment['name']} are depleting {$fasterPct}% faster than last month. Consider increasing stock.";
                }
            }
        }

        $upsert->execute([
            $id,
            $usageCount,
            $usageHours,
            $reportCount,
            $severeReports,
            $health,
            $healthStatus,
            $lifecyclePercent,
            $prediction,
            $isConsumable,
            $burnRateDays,
            $reorderWithinDays,
            $replenishmentMessage,
        ]);
    }
}

function fetchEquipmentPredictions(PDO $pdo): array
{
    ensureEquipmentPredictionsTable($pdo);
    updateEquipmentPredictions($pdo);

    $stmt = $pdo->query("
        SELECT ep.*, e.equip_code, e.name, e.status
        FROM equipment_predictions ep
        JOIN equipment e ON e.id = ep.equipment_id
        ORDER BY ep.health_score ASC, ep.usage_hours DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (basename($_SERVER['SCRIPT_NAME'] ?? '') === basename(__FILE__)) {
    require_once __DIR__ . '/../config/session.php';
    requireLogin(['admin', 'maintenance']);
    header('Content-Type: application/json');

    try {
        $pdo = getDB();
        $predictions = fetchEquipmentPredictions($pdo);
        $replenishment = array_values(array_filter($predictions, fn($p) => (int) ($p['is_consumable'] ?? 0) === 1 && !empty($p['replenishment_message'])));
        jsonResponse(true, 'OK', [
            'predictions' => $predictions,
            'replenishment' => array_slice($replenishment, 0, 6),
            'summary' => $replenishment[0]['replenishment_message'] ?? 'Inventory levels are stable based on current QR scan history.',
        ]);
    } catch (Throwable $e) {
        jsonResponse(false, 'Unable to update equipment predictions.');
    }
}
