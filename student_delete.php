<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('students.php');
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$s = $pdo->prepare('SELECT photo FROM students WHERE id=?'); $s->execute([$id]); $row = $s->fetch();
if ($row) {
    if ($row['photo'] && is_file(__DIR__ . '/uploads/' . $row['photo'])) unlink(__DIR__ . '/uploads/' . $row['photo']);
    $pdo->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
    flash('Student deleted.');
}
redirect('students.php');
