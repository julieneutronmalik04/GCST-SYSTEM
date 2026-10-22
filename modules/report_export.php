<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/equipment_predictions.php';
require_once __DIR__ . '/../services/damage_disposal.php';
require_once __DIR__ . '/../services/report_xlsx.php';

requireLogin('admin');

$pdo = getDB();

function reportParam(string $key, string $default = ''): string
{
    return trim((string)($_GET[$key] ?? $default));
}

function reportDateOrNull(string $value): ?string
{
    if ($value === '') {
        return null;
    }
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date ? $date->format('Y-m-d') : null;
}

function reportRangeSql(string $column, ?string $from, ?string $to, array &$params): string
{
    $sql = '';
    if ($from !== null) {
        $sql .= " AND $column >= ?";
        $params[] = $from;
    }
    if ($to !== null) {
        $sql .= " AND $column <= ?";
        $params[] = $to;
    }
    return $sql;
}

function fetchReportData(PDO $pdo, string $type, ?string $from, ?string $to): array
{
    if ($type === 'disposal') {
        $records = disposalRecords($pdo, 'Approved', $from, $to);
        return [
            'title' => 'Disposal Reports', 'subtitle' => 'Approved disposed equipment, assessment costs, and borrower penalties', 'filename' => 'disposal-report',
            'headers' => ['Equipment', 'Requested', 'Disposed / Approved', 'Reason', 'Assessed Cost', 'Borrower / ID', 'Damage Fee', 'Overdue Fee', 'Total Penalty', 'Admin Note'],
            'rows' => array_map(fn($r) => [
                $r['equip_code'] . ' - ' . $r['equipment_name'], $r['requested_at'], $r['reviewed_at'], $r['reason'], (float) $r['assessed_cost'],
                $r['borrower_name'] ? $r['borrower_name'] . ' (' . ($r['student_faculty_id'] ?: 'legacy') . ')' : 'Inspection; no borrower charged',
                (float) $r['damage_penalty_amount'], (float) $r['overdue_penalty_amount'], (float) $r['total_penalty_amount'], $r['review_note'],
            ], $records),
        ];
    }
    try {
        updateEquipmentPredictions($pdo);
    } catch (Throwable) {
        ensureEquipmentPredictionsTable($pdo);
    }

    switch ($type) {
        case 'room_schedule':
            if ($from === null && $to === null) {
                $from = date('Y-m-d', strtotime('monday this week'));
                $to = date('Y-m-d', strtotime('sunday this week'));
            }
            $params = [];
            $sql = "
                SELECT COALESCE(res_code, res_id, CONCAT('RES-', id)) AS reservation_code,
                       faculty_name, room_code, room_name, res_date, time_start, time_end,
                       attendees, purpose, status
                FROM reservations
                WHERE status = 'Approved'
            ";
            $sql .= reportRangeSql('res_date', $from, $to, $params);
            $sql .= ' ORDER BY res_date ASC, time_start ASC, room_code ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return [
                'title' => 'Room Schedule Report',
                'subtitle' => 'Approved reservations for the selected week or date range',
                'filename' => 'room-schedule-report',
                'headers' => ['Reservation', 'Faculty', 'Room', 'Date', 'Time', 'Attendees', 'Purpose'],
                'rows' => array_map(fn($r) => [
                    $r['reservation_code'],
                    $r['faculty_name'],
                    trim(($r['room_code'] ?? '') . ' ' . ($r['room_name'] ?? '')),
                    $r['res_date'],
                    substr((string)$r['time_start'], 0, 5) . ' - ' . substr((string)$r['time_end'], 0, 5),
                    $r['attendees'],
                    $r['purpose'],
                ], $stmt->fetchAll(PDO::FETCH_ASSOC)),
            ];

        case 'maintenance_predictor':
            $params = [];
            $sql = "
                SELECT e.equip_code, e.name, e.status, e.assigned_room,
                       ep.health_status, ep.health_score, ep.lifecycle_percent,
                       ep.usage_count, ep.usage_hours, ep.condition_reports,
                       ep.maintenance_prediction, ep.updated_at
                FROM equipment e
                LEFT JOIN equipment_predictions ep ON ep.equipment_id = e.id
                WHERE (
                    e.status IN ('Damaged', 'Under Repair')
                    OR ep.health_status IN ('Critical', 'Maintenance Predicted')
                    OR ep.health_score <= 65
                    OR ep.maintenance_prediction IS NOT NULL
                )
            ";
            $sql .= reportRangeSql('DATE(ep.updated_at)', $from, $to, $params);
            $sql .= ' ORDER BY ep.health_score ASC, ep.usage_hours DESC, e.equip_code ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return [
                'title' => 'Maintenance Predictor Report',
                'subtitle' => 'High-risk equipment and items due for maintenance',
                'filename' => 'maintenance-predictor-report',
                'headers' => ['Code', 'Equipment', 'Room', 'Status', 'Health', 'Score', 'Usage Hours', 'Suggestion'],
                'rows' => array_map(fn($r) => [
                    $r['equip_code'],
                    $r['name'],
                    $r['assigned_room'],
                    $r['status'],
                    $r['health_status'] ?: 'Not evaluated',
                    $r['health_score'] ?? '',
                    $r['usage_hours'] ?? '0',
                    $r['maintenance_prediction'] ?: 'Inspect and monitor',
                ], $stmt->fetchAll(PDO::FETCH_ASSOC)),
            ];

        case 'inventory':
            $stmt = $pdo->query("
                SELECT e.equip_code, e.name, e.description, e.status, e.assigned_room,
                       e.serial_number, e.purchased_date,
                       ep.is_consumable, ep.burn_rate_days, ep.reorder_within_days,
                       ep.replenishment_message, ep.health_status
                FROM equipment e
                LEFT JOIN equipment_predictions ep ON ep.equipment_id = e.id
                ORDER BY e.equip_code ASC
            ");
            return [
                'title' => 'Inventory Report',
                'subtitle' => 'Current stock status and consumable burn-rate forecast',
                'filename' => 'inventory-report',
                'headers' => ['Code', 'Item', 'Status', 'Room', 'Consumable', 'Burn Rate', 'Reorder', 'Remarks'],
                'rows' => array_map(fn($r) => [
                    $r['equip_code'],
                    $r['name'],
                    $r['status'],
                    $r['assigned_room'],
                    (int)($r['is_consumable'] ?? 0) === 1 ? 'Yes' : 'No',
                    $r['burn_rate_days'] ? $r['burn_rate_days'] . ' days' : 'N/A',
                    $r['reorder_within_days'] ? 'Within ' . $r['reorder_within_days'] . ' days' : 'Stable',
                    $r['replenishment_message'] ?: ($r['health_status'] ?: 'No alert'),
                ], $stmt->fetchAll(PDO::FETCH_ASSOC)),
            ];

        case 'faculty_reservation_logs':
            $params = [];
            $sql = "
                SELECT COALESCE(res_code, res_id, CONCAT('RES-', id)) AS reservation_code,
                       faculty_name, room_code, room_name, res_date, time_start, time_end,
                       status, reviewed_at, admin_note
                FROM reservations
                WHERE 1=1
            ";
            $sql .= reportRangeSql('res_date', $from, $to, $params);
            $sql .= ' ORDER BY res_date DESC, time_start DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return [
                'title' => 'Faculty Reservation Logs',
                'subtitle' => 'Reservation request history by faculty member',
                'filename' => 'faculty-reservation-logs',
                'headers' => ['Reservation', 'Faculty', 'Room', 'Date', 'Time', 'Status', 'Admin Note'],
                'rows' => array_map(fn($r) => [
                    $r['reservation_code'],
                    $r['faculty_name'],
                    trim(($r['room_code'] ?? '') . ' ' . ($r['room_name'] ?? '')),
                    $r['res_date'],
                    substr((string)$r['time_start'], 0, 5) . ' - ' . substr((string)$r['time_end'], 0, 5),
                    $r['status'],
                    $r['admin_note'],
                ], $stmt->fetchAll(PDO::FETCH_ASSOC)),
            ];

        case 'room_utilization':
        default:
            $params = [];
            $sql = "
                SELECT room_code, room_name,
                       COUNT(*) AS approved_count,
                       COUNT(DISTINCT res_date) AS active_days,
                       COALESCE(SUM(TIMESTAMPDIFF(MINUTE, time_start, time_end)) / 60, 0) AS reserved_hours,
                       SUM(attendees) AS total_attendees
                FROM reservations
                WHERE status = 'Approved'
            ";
            $sql .= reportRangeSql('res_date', $from, $to, $params);
            $sql .= ' GROUP BY room_code, room_name ORDER BY approved_count DESC, reserved_hours DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return [
                'title' => 'Room Utilization Report',
                'subtitle' => 'Approved reservation volume and reserved hours per room',
                'filename' => 'room-utilization-report',
                'headers' => ['Room Code', 'Room Name', 'Reservations', 'Active Days', 'Reserved Hours', 'Total Attendees'],
                'rows' => array_map(fn($r) => [
                    $r['room_code'],
                    $r['room_name'],
                    $r['approved_count'],
                    $r['active_days'],
                    number_format((float)$r['reserved_hours'], 2),
                    $r['total_attendees'],
                ], $stmt->fetchAll(PDO::FETCH_ASSOC)),
            ];
    }
}

function csvCell($value): string
{
    $value = (string)($value ?? '');
    if (preg_match('/^[\s]*[=+@-]/u', $value) && !is_numeric($value)) $value = "'" . $value;
    return '"' . str_replace('"', '""', $value) . '"';
}

function outputCsv(array $report, ?string $from, ?string $to): never
{
    $filename = $report['filename'] . '-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache');

    echo "Granby Colleges of Science and Technology\n";
    echo csvCell($report['title']) . "\n";
    echo csvCell('Generated: ' . date('F d, Y h:i A')) . "\n";
    echo csvCell('Date Range: ' . (($from ?: 'All') . ' to ' . ($to ?: 'All'))) . "\n\n";
    echo implode(',', array_map('csvCell', $report['headers'])) . "\n";
    foreach ($report['rows'] as $row) {
        echo implode(',', array_map('csvCell', $row)) . "\n";
    }
    exit;
}

function pdfEscape(string $text): string
{
    $text = str_replace(["\r", "\n", "\t"], ' ', $text);
    $encoded = function_exists('iconv') ? iconv('UTF-8', 'Windows-1252//TRANSLIT', $text) : false;
    $text = $encoded !== false ? $encoded : $text;
    $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function pdfText(float $x, float $y, string $text, int $size = 9, string $color = '0 0 0'): string
{
    return "BT /F1 $size Tf $color rg 1 0 0 1 $x $y Tm (" . pdfEscape($text) . ") Tj ET\n";
}

function renderSimplePdf(array $report, ?string $from, ?string $to): string
{
    $pages = [];
    $headers = $report['headers'];
    $rows = $report['rows'] ?: [['No records found for the selected filters.']];
    $colCount = max(1, count($headers));
    $tableX = 40;
    $pageW = 595;
    $pageH = 842;
    $tableW = $pageW - 80;
    $colW = $tableW / $colCount;
    $rowH = 22;
    $maxRows = 27;
    $chunks = array_chunk($rows, $maxRows);

    foreach ($chunks as $pageIndex => $chunk) {
        $c = "q 0.96 0.98 1 rg 0 0 $pageW $pageH re f Q\n";
        $c .= "q 0.05 0.22 0.48 rg 40 774 515 42 re f Q\n";
        $c .= "q 1 1 1 rg 50 784 30 22 re f Q\n";
        $c .= pdfText(55, 792, 'GCST', 8, '0.05 0.22 0.48');
        $c .= pdfText(92, 799, 'Granby Colleges of Science and Technology', 13, '1 1 1');
        $c .= pdfText(92, 784, $report['title'], 10, '1 1 1');
        $c .= pdfText(40, 748, $report['subtitle'], 10, '0.09 0.12 0.20');
        $c .= pdfText(40, 732, 'Generated: ' . date('F d, Y h:i A'), 8, '0.32 0.38 0.47');
        $c .= pdfText(310, 732, 'Date Range: ' . (($from ?: 'All') . ' to ' . ($to ?: 'All')), 8, '0.32 0.38 0.47');

        $y = 700;
        $c .= "q 0.10 0.32 0.68 rg $tableX $y $tableW 24 re f Q\n";
        foreach ($headers as $i => $header) {
            $c .= pdfText($tableX + ($i * $colW) + 4, $y + 8, substr((string)$header, 0, 18), 7, '1 1 1');
        }
        $y -= $rowH;

        foreach ($chunk as $row) {
            $fill = ($y / $rowH) % 2 === 0 ? '1 1 1' : '0.94 0.97 1';
            $c .= "q $fill rg $tableX $y $tableW $rowH re f Q\n";
            $c .= "q 0.80 0.87 0.96 RG 0.4 w $tableX $y $tableW $rowH re S Q\n";
            foreach (array_values($row) as $i => $cell) {
                if ($i >= $colCount) {
                    break;
                }
                $limit = max(8, (int)floor($colW / 4.1));
                $c .= pdfText($tableX + ($i * $colW) + 4, $y + 8, substr((string)($cell ?? ''), 0, $limit), 7, '0.12 0.16 0.24');
            }
            $y -= $rowH;
        }

        $c .= pdfText(40, 34, 'GCST Resource Management and Equipment Inventory System', 8, '0.32 0.38 0.47');
        $c .= pdfText(500, 34, 'Page ' . ($pageIndex + 1) . ' of ' . count($chunks), 8, '0.32 0.38 0.47');
        $pages[] = $c;
    }

    return assembleReportPdf($pages);
}

function assembleReportPdf(array $pages): string
{
    $objects = [];
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $kids = [];
    $contentObjectStart = 3 + count($pages);
    foreach ($pages as $i => $_) {
        $kids[] = (3 + $i) . ' 0 R';
    }
    $objects[] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($pages) . " >>";
    foreach ($pages as $i => $content) {
        $contentId = $contentObjectStart + $i;
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 " . ($contentObjectStart + count($pages)) . " 0 R >> >> /Contents $contentId 0 R >>";
    }
    foreach ($pages as $content) {
        $objects[] = "<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream";
    }
    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $i => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n$object\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    return $pdf;
}

function renderDisposalPdf(array $report, ?string $from, ?string $to): string
{
    $lines = ['Granby Colleges of Science and Technology', $report['title'],
        'Date range: ' . ($from ?: 'All') . ' to ' . ($to ?: 'All'), ''];
    foreach ($report['rows'] as $row) {
        foreach ($report['headers'] as $i => $header) {
            foreach (explode("\n", wordwrap($header . ': ' . ($row[$i] ?? ''), 90, "\n", true)) as $line) $lines[] = $line;
        }
        $lines[] = str_repeat('-', 90);
    }
    if (!$report['rows']) $lines[] = 'No approved disposal records in this date range.';
    $pages = [];
    foreach (array_chunk($lines, 45) as $index => $chunk) {
        $content = pdfText(40, 810, 'Disposal Reports - Page ' . ($index + 1), 12);
        foreach ($chunk as $line => $value) $content .= pdfText(40, 780 - $line * 16, $value, 10);
        $pages[] = $content;
    }
    return assembleReportPdf($pages);
}

function outputPdf(array $report, ?string $from, ?string $to): never
{
    $filename = $report['filename'] . '-' . date('Ymd-His') . '.pdf';
    $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';

    if (is_file($autoload)) {
        require_once $autoload;
    }

    if (class_exists('\\Dompdf\\Dompdf')) {
        $rows = '';
        foreach ($report['rows'] as $row) {
            $rows .= '<tr>';
            foreach ($row as $cell) {
                $rows .= '<td>' . htmlspecialchars((string)($cell ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $rows .= '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="' . count($report['headers']) . '">No records found for the selected filters.</td></tr>';
        }
        $headers = implode('', array_map(fn($h) => '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>', $report['headers']));
        $logo = realpath(dirname(__DIR__, 2) . '/frontend/img/granby_img.jpg');
        $logoHtml = $logo ? '<img src="' . $logo . '" class="logo">' : '<div class="logo-text">GCST</div>';
        $html = '<html><head><style>
            body{font-family:DejaVu Sans,Arial,sans-serif;color:#0f172a;font-size:11px}
            .header{background:#0f3b7a;color:white;padding:16px;display:flex;align-items:center;gap:12px}
            .logo{width:54px;height:54px;object-fit:cover;background:white;border-radius:6px}
            .logo-text{background:white;color:#0f3b7a;font-weight:bold;padding:14px 10px;border-radius:6px}
            h1{font-size:20px;margin:0}.meta{margin:12px 0 18px;color:#475569}
            table{width:100%;border-collapse:collapse}th{background:#2563eb;color:white;text-align:left;padding:8px}
            td{border:1px solid #dbeafe;padding:7px}tr:nth-child(even){background:#eff6ff}
        </style></head><body><div class="header">' . $logoHtml . '<div><h1>' . htmlspecialchars($report['title'], ENT_QUOTES, 'UTF-8') . '</h1><div>' . htmlspecialchars($report['subtitle'], ENT_QUOTES, 'UTF-8') . '</div></div></div>
        <div class="meta">Generated: ' . date('F d, Y h:i A') . '<br>Date Range: ' . htmlspecialchars(($from ?: 'All') . ' to ' . ($to ?: 'All'), ENT_QUOTES, 'UTF-8') . '</div>
        <table><thead><tr>' . $headers . '</tr></thead><tbody>' . $rows . '</tbody></table></body></html>';
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache');
    echo $report['filename'] === 'disposal-report' ? renderDisposalPdf($report, $from, $to) : renderSimplePdf($report, $from, $to);
    exit;
}

$type = reportParam('type', 'room_utilization');
$format = strtolower(reportParam('format', 'pdf'));
$from = reportDateOrNull(reportParam('from'));
$to = reportDateOrNull(reportParam('to'));

if ($from !== null && $to !== null && $from > $to) {
    [$from, $to] = [$to, $from];
}

$allowedTypes = ['room_utilization', 'room_schedule', 'maintenance_predictor', 'inventory', 'faculty_reservation_logs', 'disposal'];
if (!in_array($type, $allowedTypes, true)) {
    $type = 'room_utilization';
}

$report = fetchReportData($pdo, $type, $from, $to);

if (in_array($format, ['excel', 'xlsx'], true)) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $report['filename'] . '-' . date('Ymd-His') . '.xlsx"');
    header('Cache-Control: no-store');
    echo buildReportXlsx($report, $from, $to);
    exit;
}
if ($format === 'csv') {
    outputCsv($report, $from, $to);
}

outputPdf($report, $from, $to);
