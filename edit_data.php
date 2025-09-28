<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: edit.php"); exit; }

/* อ่านข้อมูลเดิม + type เพื่อนำกลับแท็บเดิม */
$sql = "SELECT p.pub_ID AS id, p.title, p.publish_date, p.file AS image_path, pt.type_name
        FROM publication p
        LEFT JOIN publicationtype pt ON pt.type_ID = p.type_ID
        WHERE p.pub_ID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$item) { header("Location: edit.php"); exit; }

/* map กลับไปชื่อแท็บไทย */
$typeName = $item['type_name'] ?? '';
$tab = 'บทความ';
if ($typeName === 'Journal Article') $tab = 'วารสาร';
elseif ($typeName === 'Conference Proceeding') $tab = 'บทความ';
elseif ($typeName === 'Book Chapter') $tab = 'ตำรา';
elseif ($typeName === 'Thesis/Dissertation') $tab = 'อื่นๆ';

/* submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');

  $newPath = $item['image_path'];

  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext, $allowed, true)) {
      $dest = 'uploads/pub_'.$id.'_'.time().'.'.$ext;
      if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $newPath = $dest;
      }
    }
  }

  $uSql = "UPDATE publication SET title = ?, file = ? WHERE pub_ID = ?";
  $uStmt = mysqli_prepare($conn, $uSql);
  mysqli_stmt_bind_param($uStmt, "ssi", $title, $newPath, $id);
  mysqli_stmt_execute($uStmt);
  mysqli_stmt_close($uStmt);

  header("Location: edit.php?tab=".urlencode($tab));
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
  <div class="head">แก้ไขรายการ</div>
  <form method="post" enctype="multipart/form-data">
    <label>ชื่อเรื่อง</label>
    <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>

    <label>รูปภาพ (อัปโหลดเพื่อเปลี่ยนรูป 1:1)</label>
    <div class="img-prev">
      <img src="<?= htmlspecialchars($item['image_path'] ?: 'assets/logo.png') ?>" alt="">
    </div>
    <input type="file" name="image" accept="image/*">

    <div class="actions">
      <button class="btn primary" type="submit">บันทึก</button>
      <a class="btn" href="edit.php?tab=<?= urlencode($tab) ?>">ยกเลิก</a>
    </div>
  </form>
</div>
</body>
</html>
