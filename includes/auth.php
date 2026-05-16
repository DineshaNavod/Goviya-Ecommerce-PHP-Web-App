<?php

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => false,   // set true in production (HTTPS)
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}


function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}


function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}
function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}


function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
function showFlash(): void {
    $f = getFlash();
    if (!$f) return;
    $cls = $f['type'] === 'success' ? 'alert-success' : ($f['type'] === 'error' ? 'alert-danger' : 'alert-info');
    echo "<div class='alert {$cls} alert-dismissible fade show' role='alert'>
            " . htmlspecialchars($f['msg']) . "
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
}


function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}


function cartCount(): int {
    if (!isLoggedIn()) return 0;
    $db  = getDB();
    $stmt = $db->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}


function generateOrderNumber(): string {
    return 'GVY-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Y');
}
