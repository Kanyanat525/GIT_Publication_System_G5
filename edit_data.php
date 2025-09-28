<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: edit.php"); exit; }

/* โหลดข้อมูลเดิม */
$sql = "SELECT id, tab_name, title, by_who, year, image_path FROM works WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$item) { header("Location: edit.php"); exit; }

/* บันทึกเมื่อ submit */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $title = trim($_POST['title'] ?? '');
  $by    = trim($_POST['by'] ?? '');
  $year  = trim($_POST['year'] ?? '');

  $newPath = $item['image_path']; // ค่าเดิม

  // ถ้ามีการอัปโหลดรูปใหม่
  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // ตรวจโฟลเดอร์
    if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $safeName = 'work_'.$id.'_'.time().'.'.strtolower($ext);
    $dest = 'uploads/'.$safeName;

    // จำกัดชนิดไฟล์รูปแบบง่าย ๆ
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array(strtolower($ext), $allowed, true)) {
      if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $newPath = $dest;
      }
    }
  }

  $sql = "UPDATE works SET title = ?, by_who = ?, year = ?, image_path = ? WHERE id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ssssi", $title, $by, $year, $newPath, $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: edit.php?tab=".urlencode($item['tab_name']));
  exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=900">
<title>แก้ไข: <?= htmlspecialchars($item['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700&display=swap" rel="stylesheet">
<style>
  body{font-family:"Noto Sans Thai","Segoe UI",Tahoma,Arial,sans-serif;background:#eee;margin:0}
  .wrap{max-width:780px;margin:26px auto;background:#fff;border:1px solid #ddd;border-radius:8px}
  .head{padding:14px 18px;border-bottom:1px solid #e4e4e4;font-weight:800}
  form{padding:18px}
  label{display:block;margin:10px 0 6px;font-weight:700}
  input[type="text"],input[type="file"]{width:100%;height:38px;border:1px solid #cfcfcf;border-radius:8px;padding:6px 12px;outline:none}
  .img-prev{margin:10px 0}
  .img-prev img{max-width:180px;height:auto;border:1px solid #ddd;border-radius:6px}
  .actions{display:flex;gap:10px;margin-top:18px}
  .btn{padding:10px 16px;border:1px solid #cfcfcf;border-radius:10px;background:#f5f5f5;cursor:pointer}
  .primary{background:#2d6cdf;color:#fff;border-color:#2d6cdf}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">แก้ไขรายการใน “<?= htmlspecialchars($item['tab_name']) ?>”</div>
  <form method="post" enctype="multipart/form-data">
    <label>ชื่อเรื่อง</label>
    <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>

    <label>จัดทำโดย</label>
    <input type="text" name="by" value="<?= htmlspecialchars($item['by_who']) ?>" required>

    <label>ปีที่พิมพ์ (เว้นว่างได้)</label>
    <input type="text" name="year" value="<?= htmlspecialchars($item['year']) ?>">

    <label>รูปภาพ (อัปโหลดเพื่อเปลี่ยนรูปในช่อง 1:1)</label>
    <div class="img-prev">
      <?php
        $thumb = $item['image_path'] && file_exists($item['image_path']) ? $item['image_path'] : 'assets/logo.png';
      ?>
      <img src="<?= htmlspecialchars($thumb) ?>" alt="current image">
    </div>
    <input type="file" name="image" accept="image/*">

    <div class="actions">
      <button class="btn primary" type="submit">บันทึก</button>
      <a class="btn" href="edit.php?tab=<?= urlencode($item['tab_name']) ?>">ยกเลิก</a>
    </div>
  </form>
</div>
</body>
</html>
