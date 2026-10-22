<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../services/room_qr.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

// Allow public access to get_locations for dropdown population
if ($method === 'GET' && get('action') === 'get_locations') {
    try {
        $stmt = $pdo->query("SELECT * FROM locations ORDER BY location_name ASC");
        jsonResponse(true, 'OK', ['locations' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        jsonResponse(false, 'Locations table not found or error fetching.', ['locations' => []]);
    }
}

// Require login for all other operations
requireLogin();

function getRoomCodeColumn(PDO $pdo): string
{
    static $col = null;
    if ($col !== null)
        return $col;
    $cols = $pdo->query("SHOW COLUMNS FROM rooms")->fetchAll(PDO::FETCH_COLUMN);
    $col = in_array('room_code', $cols, true) ? 'room_code' : 'room_id';
    return $col;
}

function nextRoomCode(PDO $pdo): string
{
    $col = getRoomCodeColumn($pdo);
    $last = $pdo->query("SELECT $col FROM rooms ORDER BY id DESC LIMIT 1")->fetchColumn();
    if (!$last)
        return 'RM-101';
    preg_match('/(\d+)$/', $last, $m);
    return 'RM-' . str_pad((int) ($m[1] ?? 100) + 1, 3, '0', STR_PAD_LEFT);
}

if ($method === 'GET') {
    if (get('action') === 'get_locations') {
        // This is already handled above
    }

    $col = getRoomCodeColumn($pdo);
    $id = (int) get('id');

    if ($id) {
        $isQr = get('action') === 'qr';
        if ($isQr) { requireLogin('admin'); $pdo->beginTransaction(); }
        $lock = $isQr ? ' FOR UPDATE' : '';
        $stmt = $pdo->prepare("SELECT *, $col AS room_id FROM rooms WHERE id = ? LIMIT 1$lock");
        $stmt->execute([$id]);
        $room = $stmt->fetch();
        if (!$room) {
            jsonResponse(false, 'Room not found.', [], 404);
        }
        if (get('action') === 'qr') {
            requireLogin('admin');
            try { $room['qr_code_path'] = saveRoomQr($pdo, $id, $room['room_id']); $pdo->commit(); }
            catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($e->getMessage()); jsonResponse(false, 'Unable to generate this room QR code.', [], 500); }
        }
        jsonResponse(true, 'OK', ['room' => $room]);
    }

    $sql = "SELECT r.*, r.$col AS room_id,
            (SELECT COUNT(*) FROM reservations res 
             WHERE res.room_id = r.id AND res.status = 'Approved' 
               AND res.res_date = CURDATE() 
               AND CURTIME() BETWEEN res.time_start AND res.time_end) AS is_dynamic_occupied
            FROM rooms r WHERE 1=1";
    $params = [];
    $search = get('search');
    $status = get('status');
    $type = get('type');
    $location = get('location');

    if ($search !== '') {
        $sql .= " AND ($col LIKE ? OR room_name LIKE ? OR type LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like);
    }
    if ($status !== '') {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }
    if ($type !== '') {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    if ($location !== '') {
        $sql .= " AND (r.location = ? OR ? = 'All')";
        array_push($params, $location, $location);
    }
    $sql .= " ORDER BY $col ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = [];
    $stats = ['total' => 0, 'Available' => 0, 'Occupied' => 0, 'Under Maintenance' => 0];

    while ($r = $stmt->fetch()) {
        if ($r['status'] === 'Available' && (int) $r['is_dynamic_occupied'] > 0) {
            $r['status'] = 'Occupied';
        }
        $rooms[] = $r;
        if (isset($stats[$r['status']])) {
            $stats[$r['status']]++;
        }
        $stats['total']++;
    }

    jsonResponse(true, 'OK', ['rooms' => $rooms, 'stats' => $stats]);

}

if ($method === 'POST') {
    requireLogin('admin');

    if (post('action') === 'add_location') {
        $locName = trim(post('location_name'));
        if (!$locName) {
            jsonResponse(false, 'Location name is required.');
        }

        // Check if location already exists
        $chkStmt = $pdo->prepare("SELECT id FROM locations WHERE location_name = ?");
        $chkStmt->execute([$locName]);
        if ($chkStmt->fetch()) {
            jsonResponse(false, 'Location Already Exists');
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO locations (location_name) VALUES (?)");
            $stmt->execute([$locName]);
            jsonResponse(true, 'Location added successfully.');
        } catch (PDOException $e) {
            jsonResponse(false, 'Error adding location.');
        }
    }

    $col = getRoomCodeColumn($pdo);
    $room_id = post('room_id') ?: nextRoomCode($pdo);
    $room_name = post('room_name');
    $type = post('type');
    $capacity = (int) post('capacity');
    $location = post('location');
    $status = post('status') ?: 'Available';

    if (!$room_name || !$type || $capacity < 1) {
        jsonResponse(false, 'Room name, type, and capacity are required.');
    }

    $valid_types = ['Classroom', 'Laboratory', 'Conference Room', 'Lecture Hall', 'Office', 'Comfort Room', 'Canteen'];
    $valid_statuses = ['Available', 'Occupied', 'Under Maintenance'];
    if (!in_array($type, $valid_types, true))
        jsonResponse(false, 'Invalid room type.');
    if (!in_array($status, $valid_statuses, true))
        jsonResponse(false, 'Invalid status.');

    $chk = $pdo->prepare("SELECT id FROM rooms WHERE $col = ? LIMIT 1");
    $chk->execute([$room_id]);
    if ($chk->fetch()) {
        jsonResponse(false, "Room ID '$room_id' already exists.");
    }

    $hasLocation = in_array('location', $pdo->query("SHOW COLUMNS FROM rooms")->fetchAll(PDO::FETCH_COLUMN), true);

    try {
    $pdo->beginTransaction();
    if ($hasLocation) {
        $stmt = $pdo->prepare("INSERT INTO rooms ($col, room_name, type, capacity, location, status) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$room_id, $room_name, $type, $capacity, $location ?: null, $status]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO rooms ($col, room_name, type, capacity, status) VALUES (?,?,?,?,?)");
        $stmt->execute([$room_id, $room_name, $type, $capacity, $status]);
    }

    $newId = (int) $pdo->lastInsertId();
    $qrPath = saveRoomQr($pdo, $newId, $room_id);
    $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Room creation failed: ' . $e->getMessage());
        jsonResponse(false, 'Unable to save the room and generate its QR code.', [], 500);
    }
    logActivity($pdo, 'CREATE_ROOM', "room:$newId", "$room_id — $room_name");
    jsonResponse(true, 'Room added and QR code generated.', ['room_id' => $room_id, 'id' => $newId, 'qr_code_path' => $qrPath]);
}

if ($method === 'PUT') {
    requireLogin('admin');

    parse_str(file_get_contents('php://input'), $b);
    $id = (int) ($b['id'] ?? 0);
    $new_code = clean($b['room_id'] ?? '');
    $room_name = clean($b['room_name'] ?? '');
    $type = clean($b['type'] ?? '');
    $capacity = (int) ($b['capacity'] ?? 0);
    $location = clean($b['location'] ?? '');
    $status = clean($b['status'] ?? '');

    if (!$id || !$new_code || !$room_name || !$type || $capacity < 1 || !$status) {
        jsonResponse(false, 'All fields are required.');
    }

    $col = getRoomCodeColumn($pdo);
    try {
    $pdo->beginTransaction();
    $oldStmt = $pdo->prepare("SELECT $col AS room_code, room_name FROM rooms WHERE id = ? FOR UPDATE");
    $oldStmt->execute([$id]);
    $oldRoom = $oldStmt->fetch();
    if (!$oldRoom) {
        jsonResponse(false, 'Room not found.');
    }
    $old_code = $oldRoom['room_code'];
    $old_name = $oldRoom['room_name'];

    if ($new_code !== $old_code) {
        $chk = $pdo->prepare("SELECT id FROM rooms WHERE $col = ? AND id != ?");
        $chk->execute([$new_code, $id]);
        if ($chk->fetch()) {
            jsonResponse(false, "Room code '$new_code' is already taken.");
        }
    }

    $hasLocation = in_array('location', $pdo->query("SHOW COLUMNS FROM rooms")->fetchAll(PDO::FETCH_COLUMN), true);

    if ($hasLocation) {
        $stmt = $pdo->prepare("UPDATE rooms SET $col=?, room_name=?, type=?, capacity=?, location=?, status=? WHERE id=?");
        $stmt->execute([$new_code, $room_name, $type, $capacity, $location ?: null, $status, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE rooms SET $col=?, room_name=?, type=?, capacity=?, status=? WHERE id=?");
        $stmt->execute([$new_code, $room_name, $type, $capacity, $status, $id]);
    }

    // Cascade updates
    if ($new_code !== $old_code) {
        $pdo->prepare("UPDATE equipment SET assigned_room = ? WHERE assigned_room = ?")->execute([$new_code, $old_code]);
    }

    if ($new_code !== $old_code || $room_name !== $old_name) {
        $resCols = $pdo->query("SHOW COLUMNS FROM reservations")->fetchAll(PDO::FETCH_COLUMN);
        $resRoomCol = in_array('room_code', $resCols, true) ? 'room_code' : 'room_id';
        $pdo->prepare("UPDATE reservations SET $resRoomCol=?, room_name=? WHERE $resRoomCol=?")->execute([$new_code, $room_name, $old_code]);
    }

    $qrPath = saveRoomQr($pdo, $id, $new_code);
    $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Room update failed: ' . $e->getMessage());
        jsonResponse(false, 'Unable to update the room and its QR code.', [], 500);
    }

    logActivity($pdo, 'UPDATE_ROOM', "room:$id", "Updated: $room_name");
    jsonResponse(true, 'Room updated and QR code generated.', ['id' => $id, 'room_id' => $new_code, 'qr_code_path' => $qrPath]);
}

if ($method === 'DELETE') {
    requireLogin('admin');

    parse_str(file_get_contents('php://input'), $b);
    $id = (int) ($b['id'] ?? 0);
    if (!$id)
        jsonResponse(false, 'Room ID required.');

    $resCols = $pdo->query("SHOW COLUMNS FROM reservations")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('room_id', $resCols, true)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE room_id=? AND status='Approved' AND res_date>=CURDATE()");
        $chk->execute([$id]);
        if ((int) $chk->fetchColumn() > 0) {
            jsonResponse(false, 'Cannot delete: room has upcoming approved reservations.');
        }
    }

    $pdo->prepare('DELETE FROM rooms WHERE id=?')->execute([$id]);
    logActivity($pdo, 'DELETE_ROOM', "room:$id");
    jsonResponse(true, 'Room deleted successfully.');
}

jsonResponse(false, 'Method not allowed.', [], 405);
