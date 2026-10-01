<?php
$title = 'Dashboard'; $active = 'dashboard';
$actions = '<a class="btn pencil" href="student_form.php">Add student</a>';
require __DIR__ . '/includes/header.php';

$total   = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$active_n= (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetchColumn();
$classes = (int)$pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn();
$today   = $pdo->query("SELECT status, COUNT(*) c FROM attendance WHERE att_date = CURDATE() GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$marked  = array_sum($today);
$pct     = $marked ? round((($today['present'] ?? 0) + ($today['late'] ?? 0)) / $marked * 100) . '%' : 'Not marked';
$byClass = $pdo->query("SELECT c.name, c.section, COUNT(s.id) n FROM classes c LEFT JOIN students s ON s.class_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
$max     = max(1, ...array_column($byClass ?: [['n'=>1]], 'n'));
$recent  = $pdo->query("SELECT s.*, c.name cname FROM students s LEFT JOIN classes c ON c.id=s.class_id ORDER BY s.id DESC LIMIT 6")->fetchAll();
?>
<div class="grid g4" style="margin-bottom:18px">
  <div class="card stat"><span class="ic c1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a4 4 0 11-8 0 4 4 0 018 0zM4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span><div><b><?= $total ?></b><span class="l">Students enrolled</span></div></div>
  <div class="card stat"><span class="ic c2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg></span><div><b><?= $active_n ?></b><span class="l">Currently active</span></div></div>
  <div class="card stat"><span class="ic c3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v14H4zM4 10h16M9 5v14"/></svg></span><div><b><?= $classes ?></b><span class="l">Classes</span></div></div>
  <div class="card stat"><span class="ic c4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span><div><b><?= e($pct) ?></b><span class="l">Present today</span></div></div>
</div>
<div class="grid g2">
  <section class="card"><h2>Recently added</h2>
    <?php if (!$recent): ?><div class="empty"><b>No students yet</b>Add the first student to start the register.</div><?php else: ?>
    <div class="tbl"><table><tbody>
    <?php foreach ($recent as $s): ?><tr>
      <td><div class="person"><?= photo_or_initials($s) ?><div><a href="student_view.php?id=<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></a><small>Roll <?= e($s['roll_no']) ?></small></div></div></td>
      <td><?= e($s['cname'] ?? 'No class') ?></td><td><span class="badge <?= e($s['status']) ?>"><?= e($s['status']) ?></span></td></tr>
    <?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section>
  <section class="card"><h2>Students per class</h2>
    <?php if (!$byClass): ?><div class="empty">Create a class to see the split.</div><?php endif; ?>
    <?php foreach ($byClass as $c): ?>
      <div><?= e($c['name'] . ($c['section'] ? ' ' . $c['section'] : '')) ?> <strong style="float:right"><?= $c['n'] ?></strong></div>
      <div class="bar"><i style="width:<?= round($c['n'] / $max * 100) ?>%"></i></div>
    <?php endforeach; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php';
