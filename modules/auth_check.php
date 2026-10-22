<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

if (!isLoggedIn()) jsonResponse(false, 'Not authenticated.', [], 401);

// Reflect admin role changes on the next page/session check.
$stmt = getDB()->prepare('SELECT username, full_name, role, is_active FROM users WHERE id = ?');
$stmt->execute([getUserId()]);
$user = $stmt->fetch();
if (!$user || !$user['is_active']) {
    $_SESSION = [];
    jsonResponse(false, 'Your account is unavailable. Please sign in again.', [], 401);
}
foreach (['username', 'full_name', 'role'] as $field) $_SESSION[$field] = $user[$field];

jsonResponse(true, 'OK', [
    'user_id'   => getUserId(),
    'username'  => $_SESSION['username']  ?? '',
    'full_name' => getUserName(),
    'role'      => getRole(),
]);
