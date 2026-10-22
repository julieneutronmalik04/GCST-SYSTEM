<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../services/damage_disposal.php';
requireLogin('admin');
$pdo = getDB();
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') jsonResponse(true, 'OK', ['pending' => disposalRecords($pdo, 'Pending'), 'approved' => disposalRecords($pdo)]);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) jsonResponse(false, 'A disposal request ID is required.', [], 422);
        reviewDisposal($pdo, $id, $_POST['decision'] ?? '', $_POST['note'] ?? '', getUserId(), getRole());
        logActivity($pdo, 'REVIEW_DISPOSAL', 'disposal:' . $id, $_POST['decision']);
        jsonResponse(true, 'Disposal request reviewed.');
    }
    jsonResponse(false, 'Method not allowed.', [], 405);
} catch (InvalidArgumentException $e) { jsonResponse(false, $e->getMessage(), [], 422);
} catch (Throwable $e) { error_log($e->getMessage()); jsonResponse(false, 'Unable to process disposal requests.', [], 500); }
