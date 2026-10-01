<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$s = ['roll_no'=>'','first_name'=>'','last_name'=>'','gender'=>'Male','dob'=>'','email'=>'','phone'=>'','address'=>'',
      'guardian_name'=>'','guardian_phone'=>'','class_id'=>'','admission_date'=>date('Y-m-d'),'status'=>'active','photo'=>''];
if ($id) {
    $q = $pdo->prepare('SELECT * FROM students WHERE id=?'); $q->execute([$id]);
    $s = $q->fetch() ?: redirect('students.php');
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['roll_no','first_name','last_name','gender','dob','email','phone','address','guardian_name','guardian_phone','admission_date','status'] as $k)
        $s[$k] = trim($_POST[$k] ?? '');
    $s['class_id'] = (int)($_POST['class_id'] ?? 0) ?: '';

    if ($s['roll_no'] === '') $errors['roll_no'] = 'Enter a roll number.';
    if ($s['first_name'] === '') $errors['first_name'] = 'Enter the first name.';
    if ($s['last_name'] === '') $errors['last_name'] = 'Enter the last name.';
    if (!in_array($s['gender'], ['Male','Female','Other'], true)) $errors['gender'] = 'Choose a gender.';
    if (!in_array($s['status'], ['active','inactive','graduated'], true)) $s['status'] = 'active';
    if ($s['email'] !== '' && !filter_var($s['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email, like name@example.com.';
    foreach (['dob','admission_date'] as $d) if ($s[$d] !== '' && !strtotime($s[$d])) $errors[$d] = 'Enter a valid date.';
    if (!isset($errors['roll_no'])) {
        $c = $pdo->prepare('SELECT id FROM students WHERE roll_no=? AND id<>?'); $c->execute([$s['roll_no'], $id]);
        if ($c->fetch()) $errors['roll_no'] = 'This roll number is already used by another student.';
    }

    $newPhoto = null;
    if (!$errors && !empty($_FILES['photo']['name'])) {
        $f = $_FILES['photo'];
        $mime = $f['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) : '';
        $ext = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime] ?? null;
        if (!$ext) $errors['photo'] = 'Upload a JPG, PNG or WebP image.';
        elseif ($f['size'] > 2 * 1024 * 1024) $errors['photo'] = 'The photo is larger than 2 MB. Choose a smaller one.';
        else {
            $newPhoto = bin2hex(random_bytes(10)) . '.' . $ext;
            move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $newPhoto);
        }
    }

    if (!$errors) {
        $vals = [$s['roll_no'],$s['first_name'],$s['last_name'],$s['gender'],$s['dob'] ?: null,$s['email'] ?: null,$s['phone'] ?: null,
                 $s['address'] ?: null,$s['guardian_name'] ?: null,$s['guardian_phone'] ?: null,$s['class_id'] ?: null,$s['admission_date'] ?: null,$s['status']];
        if ($id) {
            $sql = 'UPDATE students SET roll_no=?,first_name=?,last_name=?,gender=?,dob=?,email=?,phone=?,address=?,guardian_name=?,guardian_phone=?,class_id=?,admission_date=?,status=?';
            if ($newPhoto) { $sql .= ',photo=?'; $vals[] = $newPhoto; if ($s['photo'] && is_file(__DIR__.'/uploads/'.$s['photo'])) unlink(__DIR__.'/uploads/'.$s['photo']); }
            $vals[] = $id;
            $pdo->prepare($sql . ' WHERE id=?')->execute($vals);
            flash('Changes saved.');
        } else {
            $vals[] = $newPhoto;
            $pdo->prepare('INSERT INTO students (roll_no,first_name,last_name,gender,dob,email,phone,address,guardian_name,guardian_phone,class_id,admission_date,status,photo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($vals);
            $id = (int)$pdo->lastInsertId();
            flash('Student added.');
        }
        redirect('student_view.php?id=' . $id);
    }
}

$title = $id ? 'Edit student' : 'Add student'; $active = 'students';
require __DIR__ . '/includes/header.php';
$classes = $pdo->query('SELECT id,name,section FROM classes ORDER BY name')->fetchAll();
function fld($k, $label, $type = 'text', $req = false, $cls = '') {
    global $s, $errors;
    echo '<div class="field ' . $cls . '"><label for="' . $k . '">' . e($label) . ($req ? ' *' : '') . '</label><input id="' . $k . '" name="' . $k . '" type="' . $type . '" value="' . e($s[$k]) . '"' . ($req ? ' required' : '') . '>'
       . (isset($errors[$k]) ? '<div class="err-t" role="alert">' . e($errors[$k]) . '</div>' : '') . '</div>';
}
?>
<form method="post" enctype="multipart/form-data" class="card" style="max-width:860px" novalidate>
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
  <?php if ($errors): ?><div class="toast err" role="alert">Some details need fixing. Check the fields marked below.</div><?php endif; ?>
  <fieldset><legend>Student</legend><div class="fgrid">
    <?php fld('first_name','First name','text',true); fld('last_name','Last name','text',true); fld('roll_no','Roll number','text',true); ?>
    <div class="field"><label for="gender">Gender *</label><select id="gender" name="gender">
      <?php foreach (['Male','Female','Other'] as $g): ?><option <?= $s['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option><?php endforeach; ?></select></div>
    <?php fld('dob','Date of birth','date'); fld('admission_date','Admission date','date'); ?>
    <div class="field"><label for="class_id">Class</label><select id="class_id" name="class_id"><option value="">Unassigned</option>
      <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $s['class_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name'] . ' ' . $c['section']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label for="status">Status</label><select id="status" name="status">
      <?php foreach (['active','inactive','graduated'] as $x): ?><option value="<?= $x ?>" <?= $s['status'] === $x ? 'selected' : '' ?>><?= ucfirst($x) ?></option><?php endforeach; ?></select></div>
    <div class="field full"><label for="photo">Photo</label><input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-preview="pv">
      <div class="hint">JPG, PNG or WebP, up to 2 MB.</div><?php if (isset($errors['photo'])): ?><div class="err-t"><?= e($errors['photo']) ?></div><?php endif; ?>
      <img id="pv" alt="Photo preview" <?= $s['photo'] ? 'src="uploads/' . e($s['photo']) . '"' : 'hidden' ?>></div>
  </div></fieldset>
  <fieldset><legend>Contact and guardian</legend><div class="fgrid">
    <?php fld('email','Email','email'); fld('phone','Phone','tel'); fld('guardian_name','Guardian name'); fld('guardian_phone','Guardian phone','tel'); ?>
    <div class="field full"><label for="address">Address</label><textarea id="address" name="address" rows="3"><?= e($s['address']) ?></textarea></div>
  </div></fieldset>
  <div class="form-bar"><button class="btn pencil">Save student</button><a class="btn alt" href="students.php">Cancel</a></div>
</form>
<?php require __DIR__ . '/includes/footer.php';
