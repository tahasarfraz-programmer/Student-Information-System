<?php
$id = (int)($_GET['id'] ?? 0);
require_once __DIR__ . '/includes/bootstrap.php'; require_login();
$q = $pdo->prepare('SELECT s.*, c.name cname, c.section csec, c.teacher FROM students s LEFT JOIN classes c ON c.id=s.class_id WHERE s.id=?');
$q->execute([$id]); $s = $q->fetch(); if (!$s) redirect('students.php');
$a = $pdo->prepare('SELECT status, COUNT(*) c FROM attendance WHERE student_id=? GROUP BY status'); $a->execute([$id]);
$att = $a->fetchAll(PDO::FETCH_KEY_PAIR); $days = array_sum($att);
$pct = $days ? round((($att['present'] ?? 0) + ($att['late'] ?? 0)) / $days * 100) : 0;
$recent = $pdo->prepare('SELECT att_date, status FROM attendance WHERE student_id=? ORDER BY att_date DESC LIMIT 8'); $recent->execute([$id]);

$title = $s['first_name'] . ' ' . $s['last_name']; $active = 'students';
$actions = '<a class="btn alt" href="students.php">Back to list</a><a class="btn pencil" href="student_form.php?id=' . $id . '">Edit</a>';
require __DIR__ . '/includes/header.php';
$age = $s['dob'] ? (new DateTime($s['dob']))->diff(new DateTime())->y : null;
?>
<div class="grid g2">
  <section class="card">
    <div class="profile"><?= photo_or_initials($s, 'avatar avatar-lg') ?>
      <div><h2 style="margin:0"><?= e($title) ?></h2><span class="badge <?= e($s['status']) ?>"><?= e($s['status']) ?></span>
      <div class="hint">Roll <?= e($s['roll_no']) ?></div></div></div>
    <dl class="dl">
      <dt>Class</dt><dd><?= $s['cname'] ? e($s['cname'] . ' ' . $s['csec']) . ($s['teacher'] ? ' (' . e($s['teacher']) . ')' : '') : 'Unassigned' ?></dd>
      <dt>Gender</dt><dd><?= e($s['gender']) ?></dd>
      <dt>Date of birth</dt><dd><?= $s['dob'] ? e(date('j M Y', strtotime($s['dob']))) . " (age $age)" : 'Not recorded' ?></dd>
      <dt>Admitted</dt><dd><?= $s['admission_date'] ? e(date('j M Y', strtotime($s['admission_date']))) : 'Not recorded' ?></dd>
      <dt>Email</dt><dd><?= e($s['email'] ?: 'Not recorded') ?></dd>
      <dt>Phone</dt><dd><?= e($s['phone'] ?: 'Not recorded') ?></dd>
      <dt>Guardian</dt><dd><?= e($s['guardian_name'] ?: 'Not recorded') ?> <?= e($s['guardian_phone']) ?></dd>
      <dt>Address</dt><dd><?= nl2br(e($s['address'] ?: 'Not recorded')) ?></dd>
    </dl>
  </section>
  <section class="card"><h2>Attendance</h2>
    <?php if (!$days): ?><div class="empty">No attendance marked yet.</div><?php else: ?>
    <b style="font:700 32px var(--head)"><?= $pct ?>%</b> <span class="hint">attended over <?= $days ?> days</span>
    <div class="bar"><i style="width:<?= $pct ?>%"></i></div>
    <table><tbody><?php foreach ($recent as $r): ?><tr><td><?= e(date('D, j M', strtotime($r['att_date']))) ?></td><td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td></tr><?php endforeach; ?></tbody></table>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php';
