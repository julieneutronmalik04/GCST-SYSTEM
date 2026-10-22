<?php

function damageCost($value): string {
    if (!is_scalar($value) || !preg_match('/^\d{1,10}(\.\d{1,2})?$/', trim((string) $value))) {
        throw new InvalidArgumentException('Enter a nonnegative repair/replacement cost with at most two decimal places. Enter 0 only when no damage fee applies.');
    }
    return number_format((float) $value, 2, '.', '');
}

/** Caller owns the transaction. Equipment row locking serializes assessments and approvals. */
function assessEquipmentDamage(PDO $pdo, int $equipmentId, string $level, string $reason, $cost, int $actor, ?int $loanId = null, ?int $reportId = null): array {
    if (!$pdo->inTransaction()) throw new LogicException('A damage assessment requires a transaction.');
    if (!in_array($level, ['Not Critical', 'Critical'], true) || trim($reason) === '') throw new InvalidArgumentException('Damage level and reason are required.');
    $amount = damageCost($cost);
    $stmt = $pdo->prepare('SELECT * FROM equipment WHERE id = ? FOR UPDATE');
    $stmt->execute([$equipmentId]);
    $equipment = $stmt->fetch();
    if (!$equipment || $equipment['status'] === 'Disposed') throw new InvalidArgumentException('Disposed or missing equipment cannot be assessed.');
    $pending = $pdo->prepare("SELECT id FROM disposal_requests WHERE equipment_id = ? AND disposal_status = 'Pending' FOR UPDATE");
    $pending->execute([$equipmentId]);
    $pendingId = $pending->fetchColumn();
    if ($pendingId && $level !== 'Critical') throw new InvalidArgumentException('This item is awaiting disposal review; its critical condition cannot be downgraded.');
    $studentId = null;
    if ($loanId) {
        $loan = $pdo->prepare('SELECT * FROM borrowed WHERE id = ? AND equipment_id = ? FOR UPDATE');
        $loan->execute([$loanId, $equipmentId]);
        $borrowing = $loan->fetch();
        if (!$borrowing) throw new InvalidArgumentException('The borrowing transaction does not match this equipment.');
        $studentId = $borrowing['student_faculty_id'];
        if (!$studentId) throw new InvalidArgumentException('Link a registered borrower before attaching a damage fee.');
        $duplicate = $pdo->prepare('SELECT id FROM damage_assessments WHERE borrowed_id = ?');
        $duplicate->execute([$loanId]);
        if ($duplicate->fetchColumn()) throw new InvalidArgumentException('This borrowing already has a damage assessment and fee.');
    }
    if (!$reportId) {
        $code = 'RET-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $pdo->prepare("INSERT INTO condition_reports (report_code, equipment_id, issue, severity, action_needed, reported_by, damage_level, assessed_cost)
            VALUES (?, ?, ?, 'High', ?, ?, ?, ?)")
            ->execute([$code, $equipmentId, $reason, $level === 'Critical' ? 'Decommission' : 'Repair', $actor, $level, $amount]);
        $reportId = (int) $pdo->lastInsertId();
    }
    $pdo->prepare('INSERT INTO damage_assessments (equipment_id, borrowed_id, condition_report_id, student_faculty_id, damage_level, reason, assessed_cost, assessed_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([$equipmentId, $loanId, $reportId, $studentId, $level, $reason, $amount, $actor]);
    $assessmentId = (int) $pdo->lastInsertId();
    $status = $level === 'Critical' ? 'For Disposal' : 'Under Repair';
    $pdo->prepare('UPDATE equipment SET status = ?, damage_level = ? WHERE id = ?')->execute([$status, $level, $equipmentId]);
    if ($loanId) $pdo->prepare('UPDATE borrowed SET damage_level = ?, damage_penalty_amount = ? WHERE id = ?')->execute([$level, $amount, $loanId]);
    if ($level === 'Critical') {
        if ($pendingId) {
            // A return can attach its transaction to a request opened during inspection.
            if ($loanId) $pdo->prepare('UPDATE disposal_requests SET borrowed_id = ?, assessed_cost = ?, reason = ? WHERE id = ?')
                ->execute([$loanId, $amount, $reason, $pendingId]);
        } else {
            $pdo->prepare('INSERT INTO disposal_requests (equipment_id, assessment_id, borrowed_id, reason, assessed_cost, requested_by) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$equipmentId, $assessmentId, $loanId, $reason, $amount, $actor]);
            $pendingId = (int) $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO admin_notifications (type, title, body, link_ref) VALUES ('disposal', 'Disposal approval required', ?, 'disposal')")
                ->execute([$equipment['equip_code'] . ' was assessed as Critical and is For Disposal.']);
        }
    }
    return ['equipment_status' => $status, 'damage_penalty_amount' => $loanId ? $amount : '0.00', 'disposal_request_id' => $pendingId ?: null, 'condition_report_id' => $reportId];
}

function processEquipmentReturn(PDO $pdo, int $id, string $condition, array $assessment, int $actor): array {
    if (!in_array($condition, ['good', 'damaged'], true)) throw new InvalidArgumentException('Invalid return condition.');
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT b.*, e.equip_code, e.name AS equip_name, e.status AS equipment_status, e.damage_level AS equipment_damage_level
            FROM borrowed b JOIN equipment e ON e.id = b.equipment_id WHERE b.id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $loan = $stmt->fetch();
        if (!$loan || !in_array($loan['status'], ['Borrowed', 'Overdue'], true)) throw new InvalidArgumentException('This borrowing is missing or has already been returned.');
        if ($loan['equipment_status'] === 'Disposed') throw new InvalidArgumentException('Disposed equipment cannot be returned through this workflow.');
        // An existing assessment cannot be bypassed by clicking Good Condition.
        if ($condition === 'good' && ($loan['equipment_damage_level'] !== 'None' || in_array($loan['equipment_status'], ['Damaged', 'Under Repair', 'For Disposal'], true))) {
            if ($loan['equipment_damage_level'] === 'None') throw new InvalidArgumentException('Choose Damaged and enter a current assessment for this item.');
            $prior = $pdo->prepare('SELECT * FROM damage_assessments WHERE equipment_id = ? ORDER BY id DESC LIMIT 1');
            $prior->execute([$loan['equipment_id']]);
            $recorded = $prior->fetch();
            if (!$recorded) throw new InvalidArgumentException('Recorded damage must be assessed before returning this item.');
            $condition = 'damaged';
            $assessment = ['damage_level' => $recorded['damage_level'], 'reason' => $recorded['reason'], 'assessed_cost' => $recorded['assessed_cost'], 'student_faculty_id' => $assessment['student_faculty_id'] ?? ''];
        }
        $result = ['equipment_status' => $loan['equipment_status'], 'damage_penalty_amount' => '0.00'];
        if ($condition === 'damaged') {
            if (!$loan['student_faculty_id']) {
                $borrower = $pdo->prepare('SELECT * FROM borrowers WHERE student_faculty_id = ?');
                $borrower->execute([trim($assessment['student_faculty_id'] ?? '')]);
                $registered = $borrower->fetch();
                if (!$registered) throw new InvalidArgumentException('Enter the registered Student/Faculty ID to link this legacy loan before assigning a damage fee.');
                $pdo->prepare('UPDATE borrowed SET student_faculty_id = ?, borrower_name = ?, department = ? WHERE id = ?')
                    ->execute([$registered['student_faculty_id'], trim($registered['first_name'] . ' ' . $registered['last_name']), $registered['department_course'], $id]);
            }
            $result = assessEquipmentDamage($pdo, (int) $loan['equipment_id'], $assessment['damage_level'] ?? '', trim($assessment['reason'] ?? ''), $assessment['assessed_cost'] ?? null, $actor, $id);
        } else {
            $active = $pdo->prepare("SELECT COUNT(*) FROM borrowed WHERE equipment_id = ? AND id <> ? AND status IN ('Borrowed','Overdue')");
            $active->execute([$loan['equipment_id'], $id]);
            // Returning one loan never clears damage or a disposal flag raised elsewhere.
            if (in_array($loan['equipment_status'], ['In Use', 'Available'], true)) {
                $result['equipment_status'] = $active->fetchColumn() ? 'In Use' : 'Available';
                $pdo->prepare('UPDATE equipment SET status = ? WHERE id = ?')->execute([$result['equipment_status'], $loan['equipment_id']]);
            }
        }
        $returned = $condition === 'damaged' ? 'Returned (Damaged)' : 'Returned';
        $pdo->prepare('UPDATE borrowed SET status = ?, actual_time_in = NOW() WHERE id = ?')->execute([$returned, $id]);
        $pdo->commit();
        return $result + ['borrowing_status' => $returned, 'equipment' => ['id' => (int) $loan['equipment_id'], 'equip_code' => $loan['equip_code'], 'name' => $loan['equip_name']]];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function reviewDisposal(PDO $pdo, int $id, string $decision, string $note, int $actor, string $role): void {
    if ($role !== 'admin') throw new DomainException('Only admins can approve or reject disposal.');
    if (!in_array($decision, ['Approved', 'Rejected'], true)) throw new InvalidArgumentException('Choose Approved or Rejected.');
    if ($decision === 'Rejected' && trim($note) === '') throw new InvalidArgumentException('A rejection reason is required.');
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT * FROM disposal_requests WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $request = $stmt->fetch();
        if (!$request || $request['disposal_status'] !== 'Pending') throw new InvalidArgumentException('Only pending requests can be reviewed.');
        $stmt = $pdo->prepare('SELECT status FROM equipment WHERE id = ? FOR UPDATE');
        $stmt->execute([$request['equipment_id']]);
        if ($stmt->fetchColumn() !== 'For Disposal') throw new InvalidArgumentException('This item is no longer For Disposal.');
        $active = $pdo->prepare("SELECT COUNT(*) FROM borrowed WHERE equipment_id = ? AND status IN ('Borrowed','Overdue')");
        $active->execute([$request['equipment_id']]);
        if ($decision === 'Approved' && $active->fetchColumn()) throw new InvalidArgumentException('Process all outstanding returns before approving disposal.');
        $pdo->prepare('UPDATE disposal_requests SET disposal_status = ?, reviewed_by = ?, reviewed_at = NOW(), review_note = ? WHERE id = ?')
            ->execute([$decision, $actor, trim($note), $id]);
        $pdo->prepare('UPDATE equipment SET status = ? WHERE id = ?')->execute([$decision === 'Approved' ? 'Disposed' : 'Under Repair', $request['equipment_id']]);
        if ($decision === 'Approved') {
            $pdo->prepare("UPDATE condition_reports SET status = 'Resolved', resolved_at = NOW(), resolution_note = ?
                WHERE equipment_id = ? AND status <> 'Resolved'")
                ->execute(['Equipment disposed under approved request #' . $id, $request['equipment_id']]);
        }
        $pdo->prepare("INSERT INTO maintenance_notifications (type, title, body, link_ref) VALUES ('disposal', 'Disposal reviewed', ?, 'disposal')")
            ->execute(['Disposal request #' . $id . ' was ' . strtolower($decision) . '. ' . trim($note)]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function disposalRecords(PDO $pdo, string $status = 'Approved', ?string $from = null, ?string $to = null): array {
    $sql = "SELECT d.*, e.equip_code, e.name AS equipment_name, b.borrower_name, b.student_faculty_id,
        COALESCE(b.damage_penalty_amount,0) AS damage_penalty_amount,
        COALESCE(b.penalty_amount,0) AS overdue_penalty_amount,
        COALESCE(b.damage_penalty_amount,0) + COALESCE(b.penalty_amount,0) AS total_penalty_amount
        FROM disposal_requests d JOIN equipment e ON e.id = d.equipment_id
        LEFT JOIN borrowed b ON b.id = d.borrowed_id WHERE d.disposal_status = ?";
    $params = [$status];
    if ($from) { $sql .= ' AND DATE(d.reviewed_at) >= ?'; $params[] = $from; }
    if ($to) { $sql .= ' AND DATE(d.reviewed_at) <= ?'; $params[] = $to; }
    $stmt = $pdo->prepare($sql . ' ORDER BY d.requested_at DESC, d.id DESC');
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function assertEquipmentStatusEdit(PDO $pdo, int $id, string $next): void {
    if (!in_array($next, ['Available', 'In Use', 'Damaged', 'Under Repair'], true)) throw new InvalidArgumentException('Disposal statuses can only be assigned through assessment and admin review.');
    $stmt = $pdo->prepare('SELECT status, damage_level FROM equipment WHERE id = ?');
    $stmt->execute([$id]);
    $equipment = $stmt->fetch();
    if (!$equipment) throw new InvalidArgumentException('Equipment not found.');
    if (in_array($equipment['status'], ['For Disposal', 'Disposed'], true)) throw new InvalidArgumentException('Use the disposal approval workflow for this item.');
    if ($equipment['damage_level'] !== 'None' && in_array($next, ['Available', 'In Use'], true)) throw new InvalidArgumentException('Resolve the damage reports before making this equipment available.');
}
