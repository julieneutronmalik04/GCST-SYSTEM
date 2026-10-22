<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$uid    = getUserId();
$pdo    = getDB();

if ($method === 'GET') {
    $view = $_GET['view'] ?? 'profile';
    
    if ($view === 'activities') {
        $limit = (int)get('limit', '10');
        
        $stmt = $pdo->prepare(
            "SELECT id, action, target, details, created_at 
             FROM activity_log 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$uid, $limit]);
        $activities = $stmt->fetchAll();
        
        jsonResponse(true, 'OK', ['activities' => $activities]);
    }
    
    $stmt = $pdo->prepare(
        'SELECT id, username, full_name, role, email, created_at FROM users WHERE id=?'
    );
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    if (!$user) jsonResponse(false, 'User not found.', [], 404);
    jsonResponse(true, 'OK', ['user' => $user]);
}

if ($method === 'PUT') {
    $b         = bodyForm();
    $full_name = clean($b['full_name'] ?? '');
    $email     = clean($b['email']     ?? '');

    if (empty($full_name)) jsonResponse(false, 'Full name is required.');

    if (!empty($email)) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email=? AND id!=?');
        $chk->execute([$email, $uid]);
        if ($chk->fetch()) jsonResponse(false, 'Email is already used by another account.');
    }

    $oldStmt = $pdo->prepare('SELECT full_name FROM users WHERE id=?');
    $oldStmt->execute([$uid]);
    $oldRow = $oldStmt->fetch();
    $old_fullname = $oldRow ? $oldRow['full_name'] : '';

    $pdo->prepare('UPDATE users SET full_name=?, email=? WHERE id=?')
        ->execute([$full_name, $email ?: null, $uid]);

    if ($full_name !== $old_fullname) {
        $pdo->prepare('UPDATE reservations SET faculty_name = ? WHERE faculty_id = ?')->execute([$full_name, $uid]);
    }

    $_SESSION['full_name'] = $full_name;

    logActivity($pdo, 'UPDATE_PROFILE', "user:$uid");
    jsonResponse(true, 'Profile updated successfully.', ['full_name' => $full_name]);
}

if ($method === 'PATCH') {
    $b               = bodyJson();
    $current_pw      = $b['current_password'] ?? '';
    $new_pw          = $b['new_password']      ?? '';
    $confirm_pw      = $b['confirm_password']  ?? '';

    if (empty($current_pw)) jsonResponse(false, 'Current password is required.');
    if (strlen($new_pw) < 6) jsonResponse(false, 'New password must be at least 6 characters.');
    if ($new_pw !== $confirm_pw) jsonResponse(false, 'New passwords do not match.');

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($current_pw, $row['password']))
        jsonResponse(false, 'Current password is incorrect.');

    $hashed = password_hash($new_pw, PASSWORD_BCRYPT);
    $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hashed, $uid]);

    logActivity($pdo, 'CHANGE_PASSWORD', "user:$uid");
    jsonResponse(true, 'Password changed successfully.');
}

jsonResponse(false, 'Method not allowed.', [], 405);