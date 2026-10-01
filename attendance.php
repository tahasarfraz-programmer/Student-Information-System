<?php
require_once __DIR__ . '/includes/bootstrap.php'; require_login();
$cid = (int)($_REQUEST['class'] ?? 0);
$date = $_REQUEST['date'] ?? date('Y-m-d');
if (!strtotime($date)) $date = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cid) {
    csrf_check();
    $st = $pdo->prepare('INSERT INTO attendance (student_id, att_date, status) VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)');
    $ok = $pdo->prepare('SELECT id FROM students WHERE id=? AND class_id=?');
    foreach ((array)($_POST['st'] ?? []) as $sid => $val) {
        if (!in_array($val, ['present','absent','late'], true)) continue;
        $ok->execute([(int)$sid, $cid]);
        if ($ok->fetch()) $st->execute([(int)$sid, $date, $val]);
    }
    flash('Attendance saved for ' . date('j M Y', strtotime($date)) . '.');
    redirect('attendance.php?class=' . $cid . '&date=' . $date);
}
$title = 'Attendance'; $active = 'attendance';
require __DIR__ . '/includes/header.php';
$classes = $pdo->query('SELECT id,name,section FROM classes ORDER BY name')->fetchAll();
$students = [];
if ($cid) {
    $q = $pdo->prepare("SELECT s.*, a.status att FROM students s LEFT JOIN attendance a ON a.student_id=s.id AND a.att_date=? WHERE s.class_id=? AND s.status='active' ORDER BY s.last_name, s.first_name");
    $q->execute([$date, $cid]); $students = $q->fetchAll();
}
?>
<form class="filters" method="get">
  <select name="class" aria-label="Class" required><option value="">Choose a class</option>
    <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $cid == $c['id'] ? 'selected' : '' ?>><?= e($c['name'] . ' ' . $c['section']) ?></option><?php endforeach; ?></select>
  <input type="date" name="date" value="<?= e($date) ?>" max="<?= date('Y-m-d') ?>" aria-label="Date">
  <button class="btn">Open register</button>
</form>
<div class="card">
<?php if (!$cid): ?><div class="empty"><b>Pick a class and date</b>The register for that day opens here.</div>
<?php elseif (!$students): ?><div class="empty"><b>No active students in this class</b><a href="students.php">Assign students to it</a> first.</div>
<?php else: ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="class" value="<?= $cid ?>"><input type="hidden" name="date" value="<?= e($date) ?>">
  <div class="form-bar" style="margin:0 0 12px"><button type="button" class="btn alt sm" data-mark-all="present">Mark all present</button></div>
  <div class="tbl"><table><thead><tr><th>Student</th><th>Roll no</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($students as $s): $cur = $s['att'] ?: ''; $n = 'st[' . $s['id'] . ']'; ?><tr>
    <td><div class="person"><?= photo_or_initials($s) ?><strong><?= e($s['first_name'] . ' ' . $s['last_name']) ?></strong></div></td>
    <td><?= e($s['roll_no']) ?></td>
    <td><div class="seg" role="radiogroup" aria-label="Attendance for <?= e($s['first_name']) ?>">
      <label><input type="radio" name="<?= $n ?>" value="present" <?= $cur === 'present' ? 'checked' : '' ?>><span class="p">Present</span></label>
      <label><input type="radio" name="<?= $n ?>" value="late" <?= $cur === 'late' ? 'checked' : '' ?>><span class="l">Late</span></label>
      <label><input type="radio" name="<?= $n ?>" value="absent" <?= $cur === 'absent' ? 'checked' : '' ?>><span class="a">Absent</span></label>
    </div></td></tr><?php endforeach; ?></tbody></table></div>
  <div class="form-bar"><button class="btn pencil">Save attendance</button></div>
</form><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php';
