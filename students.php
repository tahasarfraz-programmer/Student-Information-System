<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$q = trim($_GET['q'] ?? ''); $cid = (int)($_GET['class'] ?? 0); $st = $_GET['status'] ?? '';
$where = ['1=1']; $args = [];
if ($q !== '') { $where[] = '(s.first_name LIKE ? OR s.last_name LIKE ? OR s.roll_no LIKE ? OR s.email LIKE ? OR s.guardian_name LIKE ?)'; array_push($args, ...array_fill(0, 5, "%$q%")); }
if ($cid) { $where[] = 's.class_id = ?'; $args[] = $cid; }
if (in_array($st, ['active','inactive','graduated'], true)) { $where[] = 's.status = ?'; $args[] = $st; }
$W = implode(' AND ', $where);
$base = "FROM students s LEFT JOIN classes c ON c.id = s.class_id WHERE $W";

if (isset($_GET['export'])) {   // CSV download of the current filter
    $rows = $pdo->prepare("SELECT s.roll_no,s.first_name,s.last_name,s.gender,s.dob,s.email,s.phone,s.guardian_name,s.guardian_phone,c.name class_name,s.status,s.admission_date $base ORDER BY s.roll_no");
    $rows->execute($args);
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="students.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
    fputcsv($o, ['Roll no','First name','Last name','Gender','Date of birth','Email','Phone','Guardian','Guardian phone','Class','Status','Admitted']);
    while ($r = $rows->fetch(PDO::FETCH_NUM)) fputcsv($o, $r);
    exit;
}

$title = 'Students'; $active = 'students';
$qs = http_build_query(array_filter(['q' => $q, 'class' => $cid, 'status' => $st]));
$actions = '<a class="btn alt" href="?export=1&' . e($qs) . '">Export CSV</a><a class="btn pencil" href="student_form.php">Add student</a>';
require __DIR__ . '/includes/header.php';

$per = 10; $page = max(1, (int)($_GET['page'] ?? 1));
$cnt = $pdo->prepare("SELECT COUNT(*) $base"); $cnt->execute($args); $total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages);
$list = $pdo->prepare("SELECT s.*, c.name cname, c.section csec $base ORDER BY s.last_name, s.first_name LIMIT $per OFFSET " . (($page - 1) * $per));
$list->execute($args); $rows = $list->fetchAll();
$classes = $pdo->query('SELECT id,name,section FROM classes ORDER BY name')->fetchAll();
?>
<form class="filters" method="get" role="search">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, roll no, email or guardian" aria-label="Search students">
  <select name="class" aria-label="Class"><option value="0">All classes</option>
    <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $cid == $c['id'] ? 'selected' : '' ?>><?= e($c['name'] . ' ' . $c['section']) ?></option><?php endforeach; ?></select>
  <select name="status" aria-label="Status"><option value="">Any status</option>
    <?php foreach (['active','inactive','graduated'] as $s): ?><option <?= $st === $s ? 'selected' : '' ?> value="<?= $s ?>"><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  <button class="btn">Search</button>
  <?php if ($q || $cid || $st): ?><a class="btn alt" href="students.php">Clear</a><?php endif; ?>
</form>
<div class="card">
<?php if (!$rows): ?>
  <div class="empty"><b>No students found</b><?= ($q || $cid || $st) ? 'Try a different search or clear the filters.' : 'Add your first student to start the register.' ?></div>
<?php else: ?>
<div class="tbl"><table>
  <thead><tr><th>Student</th><th>Roll no</th><th>Class</th><th>Guardian</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $s): ?><tr>
    <td><div class="person"><?= photo_or_initials($s) ?><div><a href="student_view.php?id=<?= $s['id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></a><small><?= e($s['email']) ?></small></div></div></td>
    <td><?= e($s['roll_no']) ?></td>
    <td><?= $s['cname'] ? e($s['cname'] . ' ' . $s['csec']) : '<span class="hint">Unassigned</span>' ?></td>
    <td><?= e($s['guardian_name']) ?><small style="display:block;color:var(--muted)"><?= e($s['guardian_phone']) ?></small></td>
    <td><span class="badge <?= e($s['status']) ?>"><?= e($s['status']) ?></span></td>
    <td><div class="row-actions">
      <a class="btn alt sm" href="student_form.php?id=<?= $s['id'] ?>">Edit</a>
      <form method="post" action="student_delete.php" data-confirm="Delete <?= e($s['first_name']) ?>? Their attendance records will be deleted too.">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn danger sm">Delete</button></form>
    </div></td></tr>
  <?php endforeach; ?></tbody></table></div>
  <p class="hint"><?= $total ?> student<?= $total == 1 ? '' : 's' ?> found</p>
  <?php if ($pages > 1): ?><nav class="pager" aria-label="Pages"><?php for ($i = 1; $i <= $pages; $i++):
    $link = '?' . http_build_query(array_filter(['q' => $q, 'class' => $cid, 'status' => $st, 'page' => $i])); ?>
    <?= $i == $page ? "<span class='cur' aria-current='page'>$i</span>" : "<a href='" . e($link) . "'>$i</a>" ?><?php endfor; ?></nav><?php endif; ?>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php';
