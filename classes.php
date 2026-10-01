<?php
require_once __DIR__ . '/includes/bootstrap.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['act'] ?? ''; $cid = (int)($_POST['id'] ?? 0);
    if ($act === 'delete' && $cid) { $pdo->prepare('DELETE FROM classes WHERE id=?')->execute([$cid]); flash('Class deleted. Its students are now unassigned.'); }
    elseif ($act === 'save') {
        $n = trim($_POST['name'] ?? ''); $sec = trim($_POST['section'] ?? ''); $t = trim($_POST['teacher'] ?? '');
        if ($n === '') flash('Enter a class name.', 'err');
        elseif ($cid) { $pdo->prepare('UPDATE classes SET name=?,section=?,teacher=? WHERE id=?')->execute([$n, $sec ?: null, $t ?: null, $cid]); flash('Class updated.'); }
        else { $pdo->prepare('INSERT INTO classes (name,section,teacher) VALUES (?,?,?)')->execute([$n, $sec ?: null, $t ?: null]); flash('Class added.'); }
    }
    redirect('classes.php');
}
$title = 'Classes'; $active = 'classes';
require __DIR__ . '/includes/header.php';
$edit = null;
if (!empty($_GET['edit'])) { $q = $pdo->prepare('SELECT * FROM classes WHERE id=?'); $q->execute([(int)$_GET['edit']]); $edit = $q->fetch(); }
$rows = $pdo->query('SELECT c.*, COUNT(s.id) n FROM classes c LEFT JOIN students s ON s.class_id=c.id GROUP BY c.id ORDER BY c.name, c.section')->fetchAll();
?>
<div class="grid g2">
  <section class="card"><?php if (!$rows): ?><div class="empty"><b>No classes yet</b>Add a class using the form.</div><?php else: ?>
    <div class="tbl"><table><thead><tr><th>Class</th><th>Teacher</th><th>Students</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $c): ?><tr><td><strong><?= e($c['name'] . ' ' . $c['section']) ?></strong></td><td><?= e($c['teacher'] ?: 'Not assigned') ?></td>
      <td><a href="students.php?class=<?= $c['id'] ?>"><?= $c['n'] ?></a></td>
      <td><div class="row-actions"><a class="btn alt sm" href="?edit=<?= $c['id'] ?>">Edit</a>
        <form method="post" data-confirm="Delete this class? Its students stay but become unassigned."><?= csrf_field() ?>
        <input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn danger sm">Delete</button></form></div></td></tr>
    <?php endforeach; ?></tbody></table></div><?php endif; ?></section>
  <section class="card"><h2><?= $edit ? 'Edit class' : 'Add class' ?></h2>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="field"><label for="name">Class name *</label><input id="name" name="name" value="<?= e($edit['name'] ?? '') ?>" required></div>
      <div class="field"><label for="section">Section</label><input id="section" name="section" value="<?= e($edit['section'] ?? '') ?>" maxlength="10"></div>
      <div class="field"><label for="teacher">Class teacher</label><input id="teacher" name="teacher" value="<?= e($edit['teacher'] ?? '') ?>"></div>
      <div class="form-bar"><button class="btn pencil">Save class</button><?php if ($edit): ?><a class="btn alt" href="classes.php">Cancel</a><?php endif; ?></div>
    </form></section>
</div>
<?php require __DIR__ . '/includes/footer.php';
