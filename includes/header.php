<?php
require_once __DIR__ . '/bootstrap.php';
require_login();
$active = $active ?? '';
$nav = [
  'dashboard'  => ['dashboard.php',  'Dashboard',  'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
  'students'   => ['students.php',   'Students',   'M16 8a4 4 0 11-8 0 4 4 0 018 0zM4 21c0-4 4-6 8-6s8 2 8 6'],
  'classes'    => ['classes.php',    'Classes',    'M4 5h16v14H4zM4 10h16M9 5v14'],
  'attendance' => ['attendance.php', 'Attendance', 'M5 13l4 4L19 7'],
];
$f = flash();
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Roll Book') ?> | <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<a class="skip" href="#main">Skip to content</a>
<aside class="side" id="side">
  <div class="brand"><span class="brand-mark"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9L12 4 2 9l10 5 10-5zM6 11v5c3 2.5 9 2.5 12 0v-5"/></svg></span><?= e(APP_NAME) ?></div>
  <nav aria-label="Main">
    <?php foreach ($nav as $k => [$href, $label, $d]): ?>
      <a href="<?= $href ?>" class="<?= $active === $k ? 'on' : '' ?>" <?= $active === $k ? 'aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $d ?>"/></svg><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side-foot">
    <div class="who"><?= e($_SESSION['user']['full_name']) ?></div>
    <a href="logout.php">Sign out</a>
  </div>
</aside>
<div class="wrap">
  <header class="top">
    <button class="burger" id="burger" aria-label="Toggle menu" aria-controls="side">Menu</button>
    <h1><?= e($title ?? '') ?></h1>
    <div class="top-actions"><?= $actions ?? '' ?></div>
  </header>
  <main id="main">
  <?php if ($f): ?><div class="toast <?= e($f[1]) ?>" role="status"><?= e($f[0]) ?></div><?php endif; ?>
