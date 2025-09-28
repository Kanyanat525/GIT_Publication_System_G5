<?php
require_once 'db_connect.php';
function backHref($d='teacher_profile.php'){ return !empty($_GET['return'])?$_GET['return']:$d; }

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

/* รายชื่ออาจารย์ (กรณีไม่ส่ง uid) */
$teachers=[]; $q=mysqli_query($conn,"SELECT UID,username FROM user ORDER BY username");
while($t=mysqli_fetch_assoc($q)) $teachers[]=$t;

/* ประเภทผลงาน */
$types=[]; $r=mysqli_query($conn,"SELECT type_ID,type_name FROM publicationtype ORDER BY type_ID");
while($x=mysqli_fetch_assoc($r)) $types[]=$x;

if($_SERVER['REQUEST_METHOD']==='POST'){
  $uid = (int)($_POST['uid'] ?? $uid);
  $title = trim($_POST['title'] ?? '');
  $type  = (int)($_POST['type_ID'] ?? 0);
  $date  = $_POST['publish_date'] ?: null;

  if($uid<=0 || $type<=0 || $title===''){ die('กรุณากรอกข้อมูลให้ครบ'); }

  $filePath=null;
  if(!empty($_FILES['image']['name']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
    if(!is_dir('uploads')) mkdir('uploads',0777,true);
    $ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,['jpg','jpeg','png','gif','webp'],true)){
      $dest='uploads/pub_new_'.time().'.'.$ext;
      if(move_uploaded_file($_FILES['image']['tmp_name'],$dest)) $filePath=$dest;
    }
  }

  $ins=mysqli_prepare($conn,"INSERT INTO publication (title,abstract,publish_date,status,file,type_ID) VALUES (?,?,?,?,?,?)");
  $abs=''; $status='Pending';
  mysqli_stmt_bind_param($ins,"sssssi",$title,$abs,$date,$status,$filePath,$type);
  mysqli_stmt_execute($ins); $pubId=mysqli_insert_id($conn); mysqli_stmt_close($ins);

  $link=mysqli_prepare($conn,"INSERT INTO pub_author (pub_ID,UID,is_creator) VALUES (?,?,1)");
  mysqli_stmt_bind_param($link,"ii",$pubId,$uid);
  mysqli_stmt_execute($link); mysqli_stmt_close($link);

  header('Location: teacher_profile.php?uid='.$uid); exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=900">
<title>เพิ่มผลงาน</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700&display=swap" rel="stylesheet">
<style>
  body{font-family:'Noto Sans Thai',system-ui;background:#eee;margin:0}
  .wrap{max-width:760px;margin:26px auto;background:#fff;border:1px solid #ddd;border-radius:10px}
  .topbar{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #e5e5e5;background:#fff}
  .backbtn{padding:6px 10px;border:1px solid #d0d0d0;border-radius:8px;background:#fff;cursor:pointer}
  form{padding:18px}
  label{display:block;margin:10px 0 6px;font-weight:700}
  input[type="text"],input[type="date"],select,input[type="file"]{width:100%;height:38px;border:1px solid #cfcfcf;border-radius:8px;padding:6px 12px}
  .actions{display:flex;gap:10px;margin-top:18px}
  .btn{padding:10px 16px;border-radius:10px;border:1px solid #cfcfcf;background:#f5f5f5;text-decoration:none}
  .primary{background:#2d6cdf;color:#fff;border-color:#2d6cdf}
</style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <button class="backbtn" onclick="if(history.length>1){history.back()}else{location.href='<?= htmlspecialchars(backHref('teacher_profile.php')) ?>'}">← ย้อนกลับ</button>
    <div style="margin-left:auto"><a href="<?= $uid>0 ? 'teacher_profile.php?uid='.$uid : 'teacher_profile.php' ?>">โปรไฟล์อาจารย์</a></div>
  </div>

  <form method="post" enctype="multipart/form-data">
    <?php if ($uid<=0): ?>
      <label>เลือกอาจารย์</label>
      <select name="uid" required>
        <option value="">-- เลือกอาจารย์ --</option>
        <?php foreach($teachers as $t): ?>
          <option value="<?= (int)$t['UID'] ?>"><?= htmlspecialchars($t['username']) ?></option>
        <?php endforeach; ?>
      </select>
    <?php else: ?>
      <input type="hidden" name="uid" value="<?= (int)$uid ?>">
      <div style="margin:6px 0;color:#555">เพิ่มให้ UID: <?= (int)$uid ?></div>
    <?php endif; ?>

    <label>ชื่อเรื่อง</label>
    <input type="text" name="title" required>

    <label>ประเภทผลงาน</label>
    <select name="type_ID" required>
      <option value="">-- เลือกประเภท --</option>
      <?php foreach($types as $t): ?>
        <option value="<?= (int)$t['type_ID'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>วันที่ตีพิมพ์</label>
    <input type="date" name="publish_date">

    <label>รูปภาพ (ปก/โลโก้)</label>
    <input type="file" name="image" accept="image/*">

    <div class="actions">
      <button type="submit" class="btn primary">บันทึก</button>
      <a class="btn" href="<?= $uid>0 ? 'teacher_profile.php?uid='.$uid : 'teacher_profile.php' ?>">ยกเลิก</a>
    </div>
  </form>
</div>
</body>
</html>
