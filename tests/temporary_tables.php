<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function createTestTables(PDO $pdo, array $tables): void {
    foreach ($tables as $table) {
        if (!preg_match('/^[a-z_]+$/', $table)) throw new InvalidArgumentException('Invalid test table name.');
        $ddl = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM)[1];
        $ddl = preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $ddl);
        $ddl = preg_replace('/^\s*CONSTRAINT .*\n?/m', '', $ddl);
        $ddl = preg_replace('/,\s*\) ENGINE/', "\n) ENGINE", $ddl);
        $pdo->exec($ddl);
    }
}
