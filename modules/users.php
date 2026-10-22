<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

requireLogin('admin');

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();

function nextUserId(PDO $pdo, string $role): string {
    $prefix = match($role) {
        'faculty'     => 'FAC',
        'vip'         => 'VIP',
        'maintenance' => 'MNT',
        default       => 'USR',
    };
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
    $stmt->execute([$role]);
    $count = (int)$stmt->fetchColumn() + 1;
    return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
}

function validateUserInput(array $d, bool $isNew = true): array {
    $errors = [];
    if ($isNew && empty($d['username']))       $errors[] = 'Username is required.';
    if ($isNew && strlen($d['password'] ?? '') < 6) $errors[] = 'Password must be at least 6 characters.';
    if (empty($d['full_name']))                $errors[] = 'Full name is required.';
    if (!in_array($d['role'] ?? '', ['admin','faculty','maintenance','vip'], true))
        $errors[] = 'Role must be admin, faculty, VIP, or maintenance.';
    return $errors;
}

if ($method === 'GET') {
    $id     = (int)get('id');
    $search = get('search');
    $role   = get('role');

    if ($id) {
        $stmt = $pdo->prepare('SELECT id,username,full_name,role,email,is_active,created_at FROM users WHERE id=?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) jsonResponse(false, 'User not found.', [], 404);
        jsonResponse(true, 'OK', ['user' => $user]);
    }

    $sql    = 'SELECT id,username,full_name,role,email,is_active,created_at,updated_at FROM users WHERE 1=1';
    $params = [];

    if ($search !== '') {
        $sql    .= ' AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)';
        $like    = "%$search%";
        $params  = [$like, $like, $like];
    }
    if ($role !== '') {
        $sql    .= ' AND role = ?';
        $params[] = $role;
    }
    $sql .= ' ORDER BY role, full_name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    $stats = ['total' => 0, 'admin' => 0, 'faculty' => 0, 'vip' => 0, 'maintenance' => 0, 'active' => 0];
    foreach ($users as $u) {
        $stats['total']++;
        $stats[$u['role']] = ($stats[$u['role']] ?? 0) + 1;
        if ($u['is_active']) $stats['active']++;
    }

    jsonResponse(true, 'OK', ['users' => $users, 'stats' => $stats]);
}

if ($method === 'POST') {
    $data = [
        'username'  => post('username'),
        'password'  => $_POST['password'] ?? '',
        'full_name' => post('full_name'),
        'role'      => post('role'),
        'email'     => post('email'),
    ];

    $errors = validateUserInput($data);
    if ($errors) jsonResponse(false, implode(' ', $errors));

    $chk = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $chk->execute([$data['username']]);
    if ($chk->fetch()) jsonResponse(false, "Username '{$data['username']}' is already taken.");

    if (!empty($data['email'])) {
        $chk2 = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $chk2->execute([$data['email']]);
        if ($chk2->fetch()) jsonResponse(false, 'Email address already in use.');
    }

    $hashed = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password, full_name, role, email, is_active)
         VALUES (?, ?, ?, ?, ?, 1)'
    );
    $stmt->execute([
        $data['username'],
        $hashed,
        $data['full_name'],
        $data['role'],
        $data['email'] ?: null,
    ]);
    $newId = (int)$pdo->lastInsertId();

    logActivity($pdo, 'CREATE_USER', "user:$newId", "Created user: {$data['username']} ({$data['role']})");
    jsonResponse(true, 'User created successfully.', ['user_id' => $newId]);
}

if ($method === 'PUT') {
    $b = bodyForm();
    $id        = (int)($b['id'] ?? 0);
    $username  = clean($b['username'] ?? '');
    $full_name = clean($b['full_name'] ?? '');
    $role      = clean($b['role']      ?? '');
    $email     = clean($b['email']     ?? '');

    if (!$id) jsonResponse(false, 'User ID is required.');
    $errors = validateUserInput(['full_name' => $full_name, 'role' => $role], false);
    if ($errors) jsonResponse(false, implode(' ', $errors));

    if ($id === getUserId() && $role !== 'admin') {
        jsonResponse(false, 'You cannot change your own role.');
    }

    $oldStmt = $pdo->prepare('SELECT username, full_name FROM users WHERE id = ?');
    $oldStmt->execute([$id]);
    $oldUser = $oldStmt->fetch();
    if (!$oldUser) jsonResponse(false, 'User not found.');

    $old_username = $oldUser['username'];
    $old_fullname = $oldUser['full_name'];

    if ($username && $username !== $old_username) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $chk->execute([$username, $id]);
        if ($chk->fetch()) jsonResponse(false, "Username '$username' is already taken.");
    } else {
        $username = $old_username;
    }

    if (!empty($email)) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $chk->execute([$email, $id]);
        if ($chk->fetch()) jsonResponse(false, 'Email already used by another account.');
    }

    $stmt = $pdo->prepare('UPDATE users SET username=?, full_name=?, role=?, email=? WHERE id=?');
    $stmt->execute([$username, $full_name, $role, $email ?: null, $id]);

    if ($full_name !== $old_fullname) {
        $pdo->prepare('UPDATE reservations SET faculty_name = ? WHERE faculty_id = ?')->execute([$full_name, $id]);
    }

    if ($stmt->rowCount() === 0 && $full_name === $old_fullname) {
        jsonResponse(false, 'User not found or nothing changed.');
    }

    logActivity($pdo, 'UPDATE_USER', "user:$id", "Updated: username=$username role=$role");
    jsonResponse(true, 'User updated successfully.');
}

if ($method === 'PATCH') {
    $b      = bodyJson();
    $id     = (int)($b['id']     ?? 0);
    $action = $b['action'] ?? '';

    if (!$id) jsonResponse(false, 'User ID is required.');

    if ($action === 'reset_password') {
        $new_password = $b['new_password'] ?? '';
        if (strlen($new_password) < 6) {
            jsonResponse(false, 'New password must be at least 6 characters.');
        }

        if ($id === getUserId()) {
            $current = $b['current_password'] ?? '';
            $stmt    = $pdo->prepare('SELECT password FROM users WHERE id=?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($current, $row['password'])) {
                jsonResponse(false, 'Current password is incorrect.');
            }
        }
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hashed, $id]);
        logActivity($pdo, 'RESET_PASSWORD', "user:$id");
        jsonResponse(true, 'Password reset successfully.');
    }

    if ($action === 'toggle_active') {
        if ($id === getUserId()) jsonResponse(false, 'You cannot deactivate your own account.');
        $stmt = $pdo->prepare('UPDATE users SET is_active = IF(is_active=1, 0, 1) WHERE id=?');
        $stmt->execute([$id]);
        $row  = $pdo->prepare('SELECT is_active FROM users WHERE id=?');
        $row->execute([$id]);
        $cur  = $row->fetchColumn();
        $msg  = $cur ? 'User activated.' : 'User deactivated.';
        logActivity($pdo, 'TOGGLE_ACTIVE', "user:$id", $msg);
        jsonResponse(true, $msg, ['is_active' => (int)$cur]);
    }
    if ($action === 'update_profile') {
        $uid       = getUserId();
        $full_name = clean($b['full_name'] ?? '');
        $email     = clean($b['email']     ?? '');
        if (empty($full_name)) jsonResponse(false, 'Full name is required.');
        $pdo->prepare('UPDATE users SET full_name=?, email=? WHERE id=?')
            ->execute([$full_name, $email ?: null, $uid]);
        $_SESSION['full_name'] = $full_name;
        logActivity($pdo, 'UPDATE_PROFILE', "user:$uid");
        jsonResponse(true, 'Profile updated.', ['full_name' => $full_name]);
    }

    jsonResponse(false, "Unknown action '$action'.");
}

if ($method === 'DELETE') {
    $b  = bodyForm();
    $id = (int)($b['id'] ?? 0);
    if (!$id) jsonResponse(false, 'User ID is required.');
    if ($id === getUserId()) jsonResponse(false, 'You cannot delete your own account.');

    $nameStmt = $pdo->prepare('SELECT username, role FROM users WHERE id=?');
    $nameStmt->execute([$id]);
    $target = $nameStmt->fetch();
    if (!$target) jsonResponse(false, 'User not found.');
    if ($target['role'] === 'admin') {
        $cnt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
        if ($cnt <= 1) jsonResponse(false, 'Cannot delete the only admin account.');
    }

    $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    logActivity($pdo, 'DELETE_USER', "user:$id", "Deleted: {$target['username']}");
    jsonResponse(true, 'User deleted successfully.');
}

jsonResponse(false, 'Method not allowed.', [], 405);