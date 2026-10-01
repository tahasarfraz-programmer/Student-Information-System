<?php
require_once __DIR__ . '/../config.php';
session_start();

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Cannot connect to the database. Check the values in config.php and import database.sql.');
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Session expired. Go back, refresh the page and try again.');
    }
}
function require_login() { if (empty($_SESSION['user'])) redirect('login.php'); }
function initials($f, $l) { return strtoupper(mb_substr($f, 0, 1) . mb_substr($l, 0, 1)); }
function photo_or_initials($s, $cls = 'avatar') {
    if (!empty($s['photo'])) return '<img class="' . $cls . '" src="uploads/' . e($s['photo']) . '" alt="">';
    return '<span class="' . $cls . ' ' . $cls . '-i">' . e(initials($s['first_name'], $s['last_name'])) . '</span>';
}
