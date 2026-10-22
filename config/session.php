<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,      
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Auth 

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function getRole(): string {
    return $_SESSION['role'] ?? '';
}

function getUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function getUserName(): string {
    return $_SESSION['full_name'] ?? 'User';
}

/**
 * Enforce login + role 
 * @param string|string[] $roles 
 */
function requireLogin($roles = []): void {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthenticated. Please log in.', [], 401);
    }
    if (!empty($roles)) {
        $allowed = (array)$roles;
        if (!in_array(getRole(), $allowed, true)) {
            jsonResponse(false, 'Access denied.', [], 403);
        }
    }
}

// Response
function jsonResponse(bool $ok, string $msg, array $data = [], int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store');
    
    $payload = array_merge([
        'success' => $ok,
        'status'  => $ok ? 'success' : 'error',
        'message' => $msg
    ], $data);
    
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Input helpers 

function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function post(string $k, string $default = ''): string {
    return clean($_POST[$k] ?? $default);
}

function get(string $k, string $default = ''): string {
    return clean($_GET[$k] ?? $default);
}

function bodyJson(): array {
    static $body = null;
    if ($body === null) {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?? [];
    }
    return $body;
}

function bodyForm(): array {
    $raw = file_get_contents('php://input');
    parse_str($raw, $out);
    return array_map(fn($v) => clean((string)$v), $out);
}

// ── Activity logger ──

function logActivity(PDO $pdo, string $action, string $target = '', string $details = ''): void {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO activity_log (user_id, action, target, details, ip_address)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            getUserId() ?: null,
            $action,
            $target ?: null,
            $details ?: null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (PDOException) {
        // Non-critical; swallow
    }
}

// ── CORS / JSON header ──

header('Content-Type: application/json');