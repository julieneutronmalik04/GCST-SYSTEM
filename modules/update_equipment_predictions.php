<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/equipment_predictions.php';

header('Content-Type: application/json');

try {
    $pdo = getDB();
    updateEquipmentPredictions($pdo);
    echo json_encode([
        'success' => true,
        'message' => 'Equipment predictions updated.',
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to update equipment predictions.',
    ]);
}
