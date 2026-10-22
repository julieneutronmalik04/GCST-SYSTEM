<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../services/reservations.php';
requireLogin(['faculty', 'vip']);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') jsonResponse(false, 'Method not allowed.', [], 405);
try {
    $data = reservationAvailability(getDB(), get('room_id'), get('res_date'), get('time_start'), get('time_end'), (int) get('attendees', '0'));
    $data['priority'] = reservationUserPriority(getDB(), getUserId());
    if ($data['priority'] === 'VIP' && $data['conflict']) $data['message'] = 'VIP priority conflict detected. Your request will join the Waiting List for admin priority review; no existing booking changes until an admin approves it.';
    jsonResponse(true, $data['message'], $data);
} catch (InvalidArgumentException $error) {
    jsonResponse(false, $error->getMessage(), [], 422);
} catch (Throwable $error) {
    error_log('[GCST] Availability check: ' . $error->getMessage());
    jsonResponse(false, 'Could not check availability. You can retry; the server checks again on submission.', [], 500);
}
