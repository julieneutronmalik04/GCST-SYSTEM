<?php

/** Minimal Office Open XML workbook, using stored ZIP entries (no PHP zip extension required). */
function buildReportXlsx(array $report, ?string $from = null, ?string $to = null): string {
    $xml = fn($value) => htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) ($value ?? '')), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $rows = [[$report['title']], ['Generated: ' . date('Y-m-d H:i:s')], ['Date range: ' . ($from ?: 'All') . ' to ' . ($to ?: 'All')], [], $report['headers'], ...$report['rows']];
    $sheet = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
    foreach ($rows as $rowIndex => $row) {
        $sheet .= '<row r="' . ($rowIndex + 1) . '">';
        foreach (array_values($row) as $column => $value) {
            $letters = ''; $n = $column + 1;
            while ($n > 0) { $letters = chr(65 + ($n - 1) % 26) . $letters; $n = intdiv($n - 1, 26); }
            $ref = $letters . ($rowIndex + 1);
            $sheet .= is_int($value) || is_float($value) ? '<c r="' . $ref . '"><v>' . $xml($value) . '</v></c>'
                : '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . $xml($value) . '</t></is></c>';
        }
        $sheet .= '</row>';
    }
    $sheet .= '</sheetData></worksheet>';
    $files = [
        '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
        '_rels/.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
        'xl/workbook.xml' => '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>',
        'xl/_rels/workbook.xml.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
        'xl/worksheets/sheet1.xml' => $sheet,
    ];
    $body = ''; $directory = '';
    foreach ($files as $name => $content) {
        $size = strlen($content); $crc = crc32($content); $offset = strlen($body); $length = strlen($name);
        $body .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 33, $crc, $size, $size, $length, 0) . $name . $content;
        $directory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 33, $crc, $size, $size, $length, 0, 0, 0, 0, 0, $offset) . $name;
    }
    return $body . $directory . pack('VvvvvVVv', 0x06054b50, 0, 0, count($files), count($files), strlen($directory), strlen($body), 0);
}
