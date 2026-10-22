<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../config/session.php';
    require_once __DIR__ . '/../services/reservations.php';

    requireLogin();

    $method = $_SERVER['REQUEST_METHOD'];
    $role = getRole();
    $uid = getUserId();
    $pdo = getDB();


    function nextResCode(PDO $pdo): string
    {
        $cols = $pdo->query("SHOW COLUMNS FROM reservations")->fetchAll(PDO::FETCH_COLUMN);
        $col = in_array('res_code', $cols, true) ? 'res_code' : 'res_id';
        $stmt = $pdo->query("SELECT $col FROM reservations ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if (!$last)
            return 'RES-001';
        preg_match('/(\d+)$/', $last, $m);
        return 'RES-' . str_pad((int) ($m[1] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    }

    function notifyReservation(PDO $pdo, string $type, string $title, string $body, int $targetUserId = 0, string $targetRole = ''): void
    {
        try {
            // Notify all admins
            try {
                $pdo->prepare(
                    'INSERT INTO admin_notifications (user_id, type, title, body, link_ref) VALUES (NULL, ?, ?, ?, ?)'
                )->execute([$type, $title, $body, 'reservations']);
            } catch (PDOException) {
                // Fallback to single notifications table if role-specific doesn't exist
                $pdo->prepare(
                    'INSERT INTO notifications (user_id, type, title, body, link_ref) VALUES (NULL, ?, ?, ?, ?)'
                )->execute([$type, $title, $body, 'reservations']);
            }

            // Notify specific target user
            if ($targetUserId > 0 && !empty($targetRole)) {
                $table = $targetRole === 'faculty' ? 'faculty_notifications' :
                         ($targetRole === 'maintenance' ? 'maintenance_notifications' : 'notifications');
                try {
                    $pdo->prepare(
                        "INSERT INTO $table (user_id, type, title, body, link_ref) VALUES (?, ?, ?, ?, ?)"
                    )->execute([$targetUserId, $type, $title, $body, 'my-reservations']);
                } catch (PDOException) {
                    // Fallback if table doesn't exist
                    $pdo->prepare(
                        'INSERT INTO notifications (user_id, type, title, body, link_ref) VALUES (?, ?, ?, ?, ?)'
                    )->execute([$targetUserId, $type, $title, $body, 'my-reservations']);
                }
            }
        } catch (PDOException $e) {
            error_log('[GCST] notifyReservation failed: ' . $e->getMessage());
        }
    }

    function ensureClassSchedulesTableForConflicts(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS class_schedules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                year_section VARCHAR(80) NOT NULL,
                subject_name VARCHAR(160) NOT NULL,
                day_of_week VARCHAR(3) NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                room_code VARCHAR(50) NOT NULL,
                room_name VARCHAR(160) NULL,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_schedule_lookup (room_code, day_of_week, start_time, end_time),
                INDEX idx_schedule_section (year_section)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    function classScheduleDayFromDate(string $date): string
    {
        return [
            'Mon' => 'M',
            'Tue' => 'T',
            'Wed' => 'W',
            'Thu' => 'Th',
            'Fri' => 'F',
            'Sat' => 'S',
        ][date('D', strtotime($date))] ?? '';
    }

    function attachClassScheduleConflicts(PDO $pdo, array $reservations): array
    {
        if (!$reservations) {
            return $reservations;
        }

        ensureClassSchedulesTableForConflicts($pdo);

        $roomCols = $pdo->query("SHOW COLUMNS FROM rooms")->fetchAll(PDO::FETCH_COLUMN);
        $roomCodeCol = in_array('room_code', $roomCols, true) ? 'room_code' : 'room_id';
        $roomLookup = $pdo->prepare("SELECT $roomCodeCol AS room_code FROM rooms WHERE id = ? OR $roomCodeCol = ? LIMIT 1");

        $stmt = $pdo->prepare(
            "SELECT year_section, subject_name, start_time, end_time, room_code
             FROM class_schedules
             WHERE room_code = ? AND day_of_week = ?
               AND start_time < ? AND end_time > ?
             ORDER BY start_time ASC
             LIMIT 1"
        );

        foreach ($reservations as &$reservation) {
            $day = classScheduleDayFromDate((string) ($reservation['res_date'] ?? ''));
            $roomRef = (string) ($reservation['display_room_id'] ?? $reservation['room_code'] ?? $reservation['room_id'] ?? '');
            if (!$day || empty($reservation['time_start']) || empty($reservation['time_end'])) {
                $reservation['schedule_conflict'] = null;
                continue;
            }

            $roomCandidates = array_filter(array_unique([
                $roomRef,
                (string) ($reservation['room_code'] ?? ''),
                (string) ($reservation['room_id'] ?? ''),
            ]));

            if ($roomRef !== '') {
                $roomLookup->execute([(int) $roomRef, $roomRef]);
                $canonicalRoomCode = $roomLookup->fetchColumn();
                if ($canonicalRoomCode) {
                    $roomCandidates[] = (string) $canonicalRoomCode;
                }
            }

            $conflict = null;
            foreach (array_unique($roomCandidates) as $candidate) {
                $stmt->execute([$candidate, $day, $reservation['time_end'], $reservation['time_start']]);
                $conflict = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($conflict) {
                    break;
                }
            }

            $reservation['schedule_conflict'] = $conflict;
            $reservation['schedule_conflict_label'] = $conflict
                ? 'Conflict with ' . $conflict['year_section'] . ' Regular Class'
                : null;
        }
        unset($reservation);

        return $reservations;
    }

    if ($method === 'GET') {
        $cols = $pdo->query("SHOW COLUMNS FROM reservations")->fetchAll(PDO::FETCH_COLUMN);
        $resIdCol = in_array('res_code', $cols, true) ? 'res_code' : 'res_id';
        $roomCodeCol = in_array('room_code', $cols, true) ? 'room_code' : 'room_id';

        $id = (int) get('id');
        $search = get('search');
        $status = get('status');
        $date = get('date');

        if ($id) {
            $stmt = $pdo->prepare(
                "SELECT *, $resIdCol AS display_res_id, $roomCodeCol AS display_room_id FROM reservations WHERE id = ? LIMIT 1"
            );
            $stmt->execute([$id]);
            $r = $stmt->fetch();
            if (!$r)
                jsonResponse(false, 'Reservation not found.', [], 404);
            if (in_array($role, ['faculty', 'vip'], true) && (int) $r['faculty_id'] !== $uid) {
                jsonResponse(false, 'Access denied.', [], 403);
            }
            if ($role === 'admin') $r = attachReservationPriorityConflicts($pdo, [$r])[0];
            jsonResponse(true, 'OK', ['reservation' => $r]);
            exit;
        }

        $upcoming = get('upcoming');
        if ($upcoming) {
            $sql = "SELECT *, $resIdCol AS display_res_id, $roomCodeCol AS display_room_id FROM reservations WHERE res_date >= CURRENT_DATE AND status = 'Approved'";
            if (in_array($role, ['faculty', 'vip'], true)) {
                $sql .= " AND faculty_id = " . (int)$uid;
            }
            $sql .= " ORDER BY res_date ASC, time_start ASC LIMIT 5";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $reservations = $stmt->fetchAll();
            if ($role === 'admin') {
                $reservations = attachReservationPriorityConflicts($pdo, attachClassScheduleConflicts($pdo, $reservations));
            }
            
            // For admin dashboard stats, we might still want the stats even in upcoming preview, but typically preview doesn't use stats. 
            // We'll return them just in case.
            $stats = null;
            if ($role === 'admin') {
                $raw = $pdo->query("SELECT status, COUNT(*) c FROM reservations GROUP BY status")->fetchAll();
                $stats = ['total' => 0, 'Pending' => 0, 'Waiting List' => 0, 'Approved' => 0, 'Rejected' => 0, 'Cancelled' => 0];
                foreach ($raw as $r) {
                    $stats[$r['status']] = (int) $r['c'];
                    $stats['total'] += (int) $r['c'];
                }
            }
            jsonResponse(true, 'OK', ['reservations' => $reservations, 'stats' => $stats]);
            exit;
        }

        $sql = "SELECT *, $resIdCol AS display_res_id, $roomCodeCol AS display_room_id FROM reservations WHERE 1=1";
        $params = [];

        if (in_array($role, ['faculty', 'vip'], true)) {
            $sql .= ' AND faculty_id = ?';
            $params[] = $uid;
        }
        if ($search !== '') {
            $sql .= " AND ($resIdCol LIKE ? OR faculty_name LIKE ? OR $roomCodeCol LIKE ? OR room_name LIKE ? OR purpose LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($status !== '') {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if ($date !== '') {
            $sql .= ' AND res_date = ?';
            $params[] = $date;
        }
        $sql .= $role === 'admin'
            ? " ORDER BY CASE WHEN status IN ('Pending','Waiting List') THEN 0 ELSE 1 END, CASE WHEN priority = 'VIP' THEN 0 ELSE 1 END, created_at ASC, id ASC"
            : ' ORDER BY created_at DESC, id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $reservations = $stmt->fetchAll();
        if ($role === 'admin') {
            $reservations = attachReservationPriorityConflicts($pdo, attachClassScheduleConflicts($pdo, $reservations));
        }

        $stats = null;
        if ($role === 'admin') {
            $raw = $pdo->query("SELECT status, COUNT(*) c FROM reservations GROUP BY status")->fetchAll();
            $stats = ['total' => 0, 'Pending' => 0, 'Waiting List' => 0, 'Approved' => 0, 'Rejected' => 0, 'Cancelled' => 0];
            foreach ($raw as $r) {
                $stats[$r['status']] = (int) $r['c'];
                $stats['total'] += (int) $r['c'];
            }
        }

        jsonResponse(true, 'OK', ['reservations' => $reservations, 'stats' => $stats]);
        exit;
    }

    if ($method === 'POST') {
        requireLogin(['faculty', 'vip']);
        $body = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') ? bodyJson() : $_POST;
        $input = [
            'room_id' => $body['room_id'] ?? $body['room_code'] ?? $body['room'] ?? '',
            'res_date' => $body['res_date'] ?? $body['reservation_date'] ?? '',
            'time_start' => $body['time_start'] ?? $body['start_time'] ?? '',
            'time_end' => $body['time_end'] ?? $body['end_time'] ?? '',
            'purpose' => $body['purpose'] ?? '', 'attendees' => $body['attendees'] ?? 1,
        ];
        $result = createReservation($pdo, $input, $uid, getUserName());
        notifyReservation($pdo, 'reservation_new', 'New Reservation Request',
            getUserName() . ' submitted a request. Priority: ' . $result['priority'] . '. Status: ' . $result['reservation_status']);
        logActivity($pdo, 'SUBMIT_RESERVATION', 'res:' . $result['reservation_id'], $result['reservation_status']);
        jsonResponse(true, $result['message'], $result);
    }

    if ($method === 'PATCH' || $method === 'DELETE') {
        requireLogin(['admin', 'faculty', 'vip']);
        if ($method === 'DELETE') {
            requireLogin('admin');
            parse_str(file_get_contents('php://input'), $b);
            $action = 'delete';
        } else {
            $b = bodyJson();
            $action = (string) ($b['action'] ?? '');
        }
        $id = (int) ($b['id'] ?? 0);
        if (!$id) throw new InvalidArgumentException('Reservation ID required.');
        $note = trim((string) ($b['admin_note'] ?? ''));
        if (strlen($note) > 255) throw new InvalidArgumentException('Admin note must be 255 characters or fewer.');
        if ($action === 'archive') {
            $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) throw new InvalidArgumentException('Reservation not found.');
            if (in_array($role, ['faculty', 'vip'], true) && (int) $row['faculty_id'] !== $uid) jsonResponse(false, 'Access denied.', [], 403);
            if (!in_array($row['status'], ['Approved', 'Completed'], true)) throw new InvalidArgumentException('Only approved or completed reservations can be archived.');
            if (in_array('archived', reservationColumns($pdo), true)) {
                $pdo->prepare('UPDATE reservations SET archived = 1, updated_at = NOW() WHERE id = ?')->execute([$id]);
            } else {
                $pdo->prepare("UPDATE reservations SET status = 'Archived', updated_at = NOW() WHERE id = ?")->execute([$id]);
            }
            logActivity($pdo, 'ARCHIVE_RESERVATION', "res:$id");
            jsonResponse(true, 'Reservation archived.');
        }
        $expectedIds = $b['expected_conflict_ids'] ?? null;
        if ($expectedIds !== null && (!is_array($expectedIds) || array_filter($expectedIds, fn($value) => !is_int($value) || $value < 1))) {
            throw new InvalidArgumentException('Invalid priority conflict confirmation.');
        }
        $result = reviewReservation($pdo, $id, $action, $uid, $role, $note, null, ($b['priority_override'] ?? false) === true, $expectedIds);
        $row = $result['reservation'];
        $newStatus = $result['reservation_status'];
        notifyReservation($pdo, 'reservation_' . $action, 'Reservation ' . $newStatus,
            'Reservation for ' . $row['room_name'] . ' has been ' . $newStatus . ($note ? '. Note: ' . $note : ''),
            (int) $row['faculty_id'], 'faculty');
        foreach ($result['affected'] as $other) {
            $otherStatus = $other['new_status'] ?? $result['affected_status'];
            notifyReservation($pdo, 'reservation_conflict', 'Reservation ' . $otherStatus,
                $other['room_name'] . ': ' . ($other['transition_reason'] ?? 'An approved reservation overlaps your request.') . ' Status: ' . $otherStatus . '.',
                (int) $other['faculty_id'], 'faculty');
        }
        foreach ($result['promoted'] as $other) {
            notifyReservation($pdo, 'reservation_pending', 'Waiting List Slot Released',
                'Your request for ' . $other['room_name'] . ' is now Pending admin review because the conflicting booking was released.',
                (int) $other['faculty_id'], 'faculty');
        }
        logActivity($pdo, strtoupper('RES_' . $action), "res:$id", $newStatus);
        jsonResponse(true, "Reservation $newStatus." . (count($result['affected']) ? ' Conflicting requests were updated and their requesters notified.' : ''), [
            'reservation_status' => $newStatus, 'conflicting_requests_updated' => count($result['affected']),
            'waiting_requests_promoted' => count($result['promoted']),
            'regular_bookings_waitlisted' => count($result['displaced']),
        ]);
    }

    jsonResponse(false, 'Method not allowed.', [], 405);

} catch (InvalidArgumentException | DomainException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(false, $e->getMessage(), [], in_array($e->getCode(), [403, 409], true) ? $e->getCode() : 422);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log("[GCST Error] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'success' => false,
        'message' => 'Unable to process the reservation. Please try again.'
    ]);
    exit;
}
