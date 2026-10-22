<?php

function reservationColumns(PDO $pdo): array
{
    return $pdo->query('SHOW COLUMNS FROM reservations')->fetchAll(PDO::FETCH_COLUMN);
}

function reservationClock(PDO $pdo): array
{
    return $pdo->query('SELECT CURDATE() AS today, CURTIME() AS clock_time')->fetch();
}

function reservationUserPriority(PDO $pdo, int $userId): string
{
    $stmt = $pdo->prepare('SELECT role, is_active FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user || !$user['is_active'] || !in_array($user['role'], ['faculty', 'vip'], true)) {
        throw new DomainException('This account cannot submit reservations.', 403);
    }
    return $user['role'] === 'vip' ? 'VIP' : 'Regular';
}

function reservationApprovedConflicts(PDO $pdo, array $room, string $date, string $start, string $end, int $exclude = 0): array
{
    [$column, $ref] = reservationRoomMatch($room, reservationColumns($pdo));
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE $column = ? AND res_date = ? AND status = 'Approved'
        AND id <> ? AND time_start < ? AND time_end > ? ORDER BY id");
    $stmt->execute([$ref, $date, $exclude, $end, $start]);
    return $stmt->fetchAll();
}

function reservationManualOccupancyConflict(PDO $pdo, array $room, string $date, string $start, string $end = ''): bool
{
    if ($room['status'] !== 'Occupied') return false;
    $clock = reservationClock($pdo);
    $coversNow = $end === '' ? substr($start, 0, 5) === substr($clock['clock_time'], 0, 5)
        : $start <= $clock['clock_time'] && $end > $clock['clock_time'];
    return $date === $clock['today'] && $coversNow;
}

/** Admin-only projection, also used by the confirmation step before preemption. */
function attachReservationPriorityConflicts(PDO $pdo, array $rows): array
{
    foreach ($rows as &$row) {
        $row['priority_conflicts'] = [];
        $row['vip_conflict_count'] = 0;
        if (($row['priority'] ?? 'Regular') !== 'VIP' || !in_array($row['status'], ['Pending', 'Waiting List'], true)) continue;
        $room = reservationRoom($pdo, (string) ($row['room_code'] ?? $row['display_room_id'] ?? ''));
        foreach (reservationApprovedConflicts($pdo, $room, $row['res_date'], $row['time_start'], $row['time_end'], (int) $row['id']) as $conflict) {
            if ($conflict['priority'] === 'VIP') { $row['vip_conflict_count']++; continue; }
            $row['priority_conflicts'][] = ['id' => (int) $conflict['id'], 'code' => $conflict['res_code'] ?? $conflict['res_id'],
                'name' => $conflict['faculty_name'], 'start' => $conflict['time_start'], 'end' => $conflict['time_end']];
        }
    }
    unset($row);
    return $rows;
}

function reservationTime(string $time): string
{
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', $time)) {
        throw new InvalidArgumentException('Enter a valid time.');
    }
    return strlen($time) === 5 ? "$time:00" : $time;
}

function reservationSlot(string $date, string $start, string $end = ''): array
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date) throw new InvalidArgumentException('Enter a valid reservation date.');
    $start = reservationTime($start);
    $end = $end === '' ? '' : reservationTime($end);
    if ($end !== '' && $end <= $start) throw new InvalidArgumentException('End time must be after start time.');
    return [$date, $start, $end];
}

function reservationRoom(PDO $pdo, string $ref, bool $lock = false): array
{
    $columns = $pdo->query('SHOW COLUMNS FROM rooms')->fetchAll(PDO::FETCH_COLUMN);
    $code = in_array('room_code', $columns, true) ? 'room_code' : 'room_id';
    $stmt = $pdo->prepare("SELECT *, $code AS canonical_code FROM rooms WHERE $code = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : ''));
    $stmt->execute([$ref]);
    $room = $stmt->fetch();
    if (!$room) throw new InvalidArgumentException('Room not found.');
    return $room;
}

function reservationRoomMatch(array $room, array $columns): array
{
    return in_array('room_code', $columns, true)
        ? ['room_code', $room['canonical_code']]
        : ['room_id', (int) $room['id']];
}

/** Half-open intervals allow adjacent bookings; pending requests never hold a slot. */
function reservationHasConflict(PDO $pdo, array $room, string $date, string $start, string $end = '', int $exclude = 0): bool
{
    // A manual Occupied flag describes now, not every future slot in this room.
    if (reservationManualOccupancyConflict($pdo, $room, $date, $start, $end)) return true;
    [$column, $ref] = reservationRoomMatch($room, reservationColumns($pdo));
    $condition = $end === '' ? 'time_start <= ? AND time_end > ?' : 'time_start < ? AND time_end > ?';
    $stmt = $pdo->prepare("SELECT id FROM reservations WHERE $column = ? AND res_date = ?
        AND status = 'Approved' AND id <> ? AND $condition LIMIT 1");
    $stmt->execute([$ref, $date, $exclude, $end === '' ? $start : $end, $start]);
    return (bool) $stmt->fetchColumn();
}

function reservationAvailability(PDO $pdo, string $ref, string $date, string $start, string $end, int $attendees): array
{
    $room = reservationRoom($pdo, $ref);
    $capacityExceeded = $attendees > (int) $room['capacity'];
    $maintenance = $room['status'] === 'Under Maintenance';
    $checked = $date !== '' && $start !== '';
    $conflict = false;
    if ($checked) {
        [$date, $start, $end] = reservationSlot($date, $start, $end);
        if ($date < reservationClock($pdo)['today']) throw new InvalidArgumentException('Reservation date cannot be in the past.');
        $conflict = reservationHasConflict($pdo, $room, $date, $start, $end);
    }
    return [
        'room' => ['id' => (int) $room['id'], 'room_code' => $room['canonical_code'],
            'room_name' => $room['room_name'], 'capacity' => (int) $room['capacity'], 'status' => $room['status']],
        'capacity' => (int) $room['capacity'], 'capacity_exceeded' => $capacityExceeded,
        'maintenance' => $maintenance, 'conflict' => $conflict, 'checked' => $checked,
        'complete' => $checked && $end !== '',
        'request_status' => $conflict ? 'Waiting List' : 'Pending',
        'message' => $maintenance ? 'This room is under maintenance.' : (!$checked ? 'Select a room, date and start time to check availability.'
            : ($conflict ? 'Warning: This room has a conflict at this time. Your request will be placed on the Waiting List.'
                : 'This room is available this time. Your request will be Pending admin approval.')),
    ];
}

function createReservation(PDO $pdo, array $input, int $userId, string $name): array
{
    $priority = reservationUserPriority($pdo, $userId);
    [$date, $start, $end] = reservationSlot((string) ($input['res_date'] ?? ''), (string) ($input['time_start'] ?? ''), (string) ($input['time_end'] ?? ''));
    if ($end === '') throw new InvalidArgumentException('End time is required.');
    $attendees = filter_var($input['attendees'] ?? 1, FILTER_VALIDATE_INT);
    if ($attendees === false || $attendees < 1) throw new InvalidArgumentException('Number of students must be a positive whole number.');
    $columns = reservationColumns($pdo);
    $pdo->beginTransaction();
    try {
        // Creation, approval and cancellation all serialize on the same room row.
        $room = reservationRoom($pdo, trim((string) ($input['room_id'] ?? '')), true);
        if ($date < reservationClock($pdo)['today']) throw new InvalidArgumentException('Reservation date cannot be in the past.');
        if ($room['status'] === 'Under Maintenance') throw new InvalidArgumentException('Selected room is currently under maintenance.');
        if ($attendees > (int) $room['capacity']) throw new InvalidArgumentException("Room capacity is {$room['capacity']}; attendees exceed capacity.");
        $conflict = reservationHasConflict($pdo, $room, $date, $start, $end);
        $status = $conflict ? 'Waiting List' : 'Pending';
        $values = ['faculty_id' => $userId, 'faculty_name' => $name, 'room_name' => $room['room_name'],
            'res_date' => $date, 'time_start' => $start, 'time_end' => $end,
            'purpose' => trim((string) ($input['purpose'] ?? '')) ?: null, 'attendees' => $attendees, 'status' => $status, 'priority' => $priority];
        // Both columns exist in some installations and both can be NOT NULL.
        if (in_array('room_id', $columns, true)) $values['room_id'] = (int) $room['id'];
        if (in_array('room_code', $columns, true)) $values['room_code'] = $room['canonical_code'];
        $codeColumn = in_array('res_code', $columns, true) ? 'res_code' : 'res_id';
        $values[$codeColumn] = 'RES-' . strtoupper(bin2hex(random_bytes(7)));
        $stmt = $pdo->prepare('INSERT INTO reservations (' . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')');
        $stmt->execute(array_values($values));
        $id = (int) $pdo->lastInsertId();
        // Keep the existing sequential display style without a MAX(code) race.
        $displayCode = 'RES-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
        $exists = $pdo->prepare("SELECT id FROM reservations WHERE $codeColumn = ?");
        $exists->execute([$displayCode]);
        if (!$exists->fetchColumn()) {
            try { $pdo->prepare("UPDATE reservations SET $codeColumn = ? WHERE id = ?")->execute([$displayCode, $id]); }
            catch (PDOException $error) { if ($error->getCode() !== '23000') throw $error; }
        }
        $pdo->commit();
        return ['reservation_id' => $id, 'reservation_status' => $status, 'conflict' => $conflict, 'priority' => $priority,
            'message' => $conflict ? 'Conflict detected — Added to Waiting List.' : 'This room is available this time. Reservation submitted — Pending admin approval.'];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}

function promoteReservationWaiters(PDO $pdo, array $room): array
{
    [$column, $ref] = reservationRoomMatch($room, reservationColumns($pdo));
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE $column = ? AND status = 'Waiting List'
        AND (res_date > CURDATE() OR (res_date = CURDATE() AND time_end > CURTIME())) ORDER BY created_at, id FOR UPDATE");
    $stmt->execute([$ref]);
    $promoted = [];
    foreach ($stmt->fetchAll() as $row) {
        if ($room['status'] === 'Under Maintenance' || reservationHasConflict($pdo, $room, $row['res_date'], $row['time_start'], $row['time_end'], (int) $row['id'])) continue;
        $pdo->prepare("UPDATE reservations SET status = 'Pending', admin_note = ?, reviewed_by = NULL, reviewed_at = NULL, updated_at = NOW() WHERE id = ?")
            ->execute(['The conflicting booking was released. This request is available for admin review.', $row['id']]);
        $promoted[] = $row;
    }
    return $promoted;
}

function reviewReservation(PDO $pdo, int $id, string $action, int $userId, string $role, string $note = '', ?string $policy = null, bool $priorityOverride = false, ?array $expectedConflictIds = null): array
{
    if (!in_array($action, ['approve', 'reject', 'cancel', 'delete'], true)) throw new InvalidArgumentException('Invalid reservation action.');
    if ($role !== 'admin' && !(in_array($role, ['faculty', 'vip'], true) && $action === 'cancel')) throw new DomainException('Access denied.', 403);
    $config = require __DIR__ . '/../config/reservations.php';
    $policy ??= $config['conflicting_pending_status'];
    if (!in_array($policy, ['Rejected', 'Waiting List'], true)) throw new RuntimeException('Invalid reservation conflict policy.');
    $lookup = $pdo->prepare('SELECT * FROM reservations WHERE id = ?');
    $lookup->execute([$id]);
    $initial = $lookup->fetch();
    if (!$initial) throw new InvalidArgumentException('Reservation not found.');
    $roomColumns = $pdo->query('SHOW COLUMNS FROM rooms')->fetchAll(PDO::FETCH_COLUMN);
    $roomCodeColumn = in_array('room_code', $roomColumns, true) ? 'room_code' : 'room_id';
    if (!empty($initial['room_code'])) $roomRef = $initial['room_code'];
    else {
        $stmt = $pdo->prepare("SELECT $roomCodeColumn FROM rooms WHERE id = ?");
        $stmt->execute([$initial['room_id']]);
        $roomRef = (string) $stmt->fetchColumn();
    }
    $pdo->beginTransaction();
    try {
        $room = reservationRoom($pdo, (string) $roomRef, true);
        $lookup = $pdo->prepare('SELECT * FROM reservations WHERE id = ? FOR UPDATE');
        $lookup->execute([$id]);
        $row = $lookup->fetch();
        if (!$row) throw new InvalidArgumentException('Reservation not found.');
        if (in_array($role, ['faculty', 'vip'], true) && (int) $row['faculty_id'] !== $userId) throw new DomainException('Access denied.', 403);
        $affected = [];
        $displaced = [];
        if (in_array($action, ['approve', 'reject'], true) && !in_array($row['status'], ['Pending', 'Waiting List'], true)) {
            throw new InvalidArgumentException("Reservation is already {$row['status']}.");
        }
        if ($action === 'approve') {
            reservationSlot($row['res_date'], $row['time_start'], $row['time_end']);
            if ($room['status'] === 'Under Maintenance') throw new InvalidArgumentException('Room is under maintenance.');
            if ((int) $row['attendees'] > (int) $room['capacity']) throw new InvalidArgumentException('Request exceeds current room capacity.');
            $clock = reservationClock($pdo);
            if ($row['res_date'] < $clock['today'] || ($row['res_date'] === $clock['today'] && $row['time_end'] <= $clock['clock_time'])) throw new InvalidArgumentException('Cannot approve an expired reservation.');
            $isVip = $row['priority'] === 'VIP';
            if ($priorityOverride && !$isVip) throw new InvalidArgumentException('Only VIP requests support priority approval.');
            $approvedConflicts = reservationApprovedConflicts($pdo, $room, $row['res_date'], $row['time_start'], $row['time_end'], $id);
            foreach ($approvedConflicts as $conflict) {
                if ($isVip && $conflict['priority'] === 'VIP') throw new DomainException('Another approved VIP reservation overlaps this slot. VIP reservations cannot displace each other.', 409);
            }
            if ($priorityOverride) {
                $actualIds = array_map(fn($conflict) => (int) $conflict['id'], $approvedConflicts);
                $expectedIds = $expectedConflictIds ?? [];
                sort($actualIds);
                sort($expectedIds);
                if ($expectedConflictIds === null || $actualIds !== $expectedIds) {
                    throw new DomainException('Priority conflicts changed. Refresh the request and confirm the current affected bookings before approval.', 409);
                }
            }
            $currentApprovedConflict = false;
            foreach ($approvedConflicts as $conflict) {
                if ($row['res_date'] === $clock['today'] && $conflict['time_start'] <= $clock['clock_time'] && $conflict['time_end'] > $clock['clock_time']) $currentApprovedConflict = true;
            }
            if (reservationManualOccupancyConflict($pdo, $room, $row['res_date'], $row['time_start'], $row['time_end'])
                && !($isVip && $priorityOverride && $currentApprovedConflict)) {
                throw new DomainException('The room is currently marked Occupied. Release its occupancy before approving a request covering the current time.', 409);
            }
            if ($approvedConflicts && !($isVip && $priorityOverride)) {
                throw new DomainException('Another approved reservation overlaps this slot. The request remains on its current list; choose a different time or release the approved booking.', 409);
            }
            foreach ($approvedConflicts as $conflict) {
                $automaticNote = 'Moved to Waiting List: an overlapping VIP priority request was approved by the admin.';
                $pdo->prepare("UPDATE reservations SET status = 'Waiting List', admin_note = ?, reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?")
                    ->execute([$automaticNote, $userId, $conflict['id']]);
                $conflict['new_status'] = 'Waiting List';
                $conflict['transition_reason'] = $automaticNote;
                $affected[] = $conflict;
                $displaced[] = $conflict;
            }
        }
        $status = ['approve' => 'Approved', 'reject' => 'Rejected', 'cancel' => 'Cancelled', 'delete' => 'Deleted'][$action];
        if ($action === 'delete') $pdo->prepare('DELETE FROM reservations WHERE id = ?')->execute([$id]);
        else {
            if ($action === 'cancel' && !in_array($row['status'], ['Pending', 'Waiting List', 'Approved'], true)) throw new InvalidArgumentException('Only active requests can be cancelled.');
            $pdo->prepare('UPDATE reservations SET status = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?')
                ->execute([$status, $note ?: $row['admin_note'], $userId, $id]);
        }
        if ($action === 'approve') {
            [$column, $ref] = reservationRoomMatch($room, reservationColumns($pdo));
            $stmt = $pdo->prepare("SELECT * FROM reservations WHERE $column = ? AND res_date = ? AND status = 'Pending'
                AND id <> ? AND time_start < ? AND time_end > ? ORDER BY id FOR UPDATE");
            $stmt->execute([$ref, $row['res_date'], $id, $row['time_end'], $row['time_start']]);
            foreach ($stmt->fetchAll() as $other) {
                // VIP requests are retained for priority review, never auto-rejected.
                $otherStatus = $row['priority'] === 'VIP' || $other['priority'] === 'VIP' ? 'Waiting List' : $policy;
                $automaticNote = $otherStatus === 'Rejected' ? 'Automatically rejected: an approved reservation overlaps this time slot.' : 'Conflict detected — Added to Waiting List after another request was approved.';
                $pdo->prepare('UPDATE reservations SET status = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?')
                    ->execute([$otherStatus, $automaticNote, $userId, $other['id']]);
                $other['new_status'] = $otherStatus;
                $other['transition_reason'] = $automaticNote;
                $affected[] = $other;
            }
        }
        $promoted = in_array($action, ['cancel', 'reject', 'delete'], true) ? promoteReservationWaiters($pdo, $room) : [];
        $pdo->commit();
        return ['reservation' => $row, 'reservation_status' => $status, 'affected' => $affected,
            'affected_status' => $row['priority'] === 'VIP' ? 'Waiting List' : $policy, 'promoted' => $promoted, 'displaced' => $displaced];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}
