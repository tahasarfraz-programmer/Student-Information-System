<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');

// First run: create the default admin account.
if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
    $pdo->prepare('INSERT INTO users (username,password,full_name) VALUES (?,?,?)')
        ->execute(['admin', password_hash('Admin@123', PASSWORD_DEFAULT), 'Administrator']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([trim($_POST['username'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'full_name' => $u['full_name']];
        redirect('dashboard.php');
    }
    $error = 'Username or password is wrong. Check both and try again.';
}
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in | <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css"></head><body>
<div class="auth">
  <section class="auth-art"><span class="brand-mark"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9L12 4 2 9l10 5 10-5zM6 11v5c3 2.5 9 2.5 12 0v-5"/></svg></span>
    <h1>Student records, made simple.</h1>
    <p>One secure place for admissions, classes, guardians and daily attendance.</p>
    <ul><li>Student profiles with photos and guardian details</li><li>Daily attendance in two clicks</li><li>Search, filter and export to CSV</li></ul></section>
  <section class="auth-form"><form method="post">
    <h2>Welcome back</h2><p>Sign in to open <?= e(APP_NAME) ?>.</p>
    <?php if ($error): ?><div class="toast err" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label for="u">Username</label><input id="u" name="username" autocomplete="username" required autofocus></div>
    <div class="field"><label for="p">Password</label><input id="p" type="password" name="password" autocomplete="current-password" required></div>
    <button class="btn pencil" style="width:100%;justify-content:center">Sign in</button>
    <p class="hint" style="margin-top:14px">First time? Sign in with admin / Admin@123, then change it.</p>
  </form></section>
</div></body></html>
