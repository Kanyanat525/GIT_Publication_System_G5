<?php
require_once 'db_connect.php';

/* ปุ่มย้อนกลับ */
function backHref($default='edit.php'){ return !empty($_GET['return']) ? $_GET['return'] : $default; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id<=0){ header('Location: edit.php'); exit; }

/* โหลดข้อมูลผลงาน */
$sql="SELECT p.pub_ID id,p.title,p.publish_date,p.file image_path,p.type_ID,pt.type_name
      FROM publication p LEFT JOIN publicationtype pt ON pt.type_ID=p.type_ID
      WHERE p.pub_ID=?";
$st=mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($st,"i",$id);
mysqli_stmt_execute($st);
$res=mysqli_stmt_get_result($st);
$item=mysqli_fetch_assoc($res);
mysqli_stmt_close($st);
if(!$item){ header('Location: edit.php'); exit; }

/* โหลดประเภทผลงานทั้งหมด */
$types=[];
$rs=mysqli_query($conn,"SELECT type_ID,type_name FROM publicationtype ORDER BY type_ID");
while($r=mysqli_fetch_assoc($rs)){ $types[]=$r; }

/* ที่หมายสำหรับย้อนไปโปรไฟล์ */
$return = $_GET['return'] ?? '';
$uid    = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

/* ====== ลบรายการ ====== */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {

  // ลบไฟล์ภาพใน uploads/
  if (!empty($item['image_path']) && preg_match('#^uploads/#',$item['image_path']) && file_exists($item['image_path'])) {
    @unlink($item['image_path']);
  }

  // ลบ pub_author ก่อน
  $d1=mysqli_prepare($conn,"DELETE FROM pub_author WHERE pub_ID=?");
  mysqli_stmt_bind_param($d1,"i",$id);
  mysqli_stmt_execute($d1); mysqli_stmt_close($d1);

  // ลบ publication
  $d2=mysqli_prepare($conn,"DELETE FROM publication WHERE pub_ID=?");
  mysqli_stmt_bind_param($d2,"i",$id);
  mysqli_stmt_execute($d2); mysqli_stmt_close($d2);

  if($return==='teacher_profile.php' && $uid>0){
    header('Location: teacher_profile.php?uid='.$uid);
  }else{
    header('Location: '.backHref('edit.php'));
  }
  exit;
}

/* ====== บันทึกแก้ไข ====== */
if($_SERVER['REQUEST_METHOD']==='POST' && (!isset($_POST['action']) || $_POST['action']==='save')){
  $title = trim($_POST['title']??'');
  $type  = (int)($_POST['type_ID']??0);
  $newPath = $item['image_path'];

  if(!empty($_FILES['image']['name']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
    if(!is_dir('uploads')) mkdir('uploads',0777,true);
    $ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,['jpg','jpeg','png','gif','webp'],true)){
      $dest='uploads/pub_'.$id.'_'.time().'.'.$ext;
      if(move_uploaded_file($_FILES['image']['tmp_name'],$dest)) {
        if (!empty($newPath) && preg_match('#^uploads/#',$newPath) && file_exists($newPath)) @unlink($newPath);
        $newPath=$dest;
      }
    }
  }

  $u=mysqli_prepare($conn,"UPDATE publication SET title=?, file=?, type_ID=? WHERE pub_ID=?");
  mysqli_stmt_bind_param($u,"ssii",$title,$newPath,$type,$id);
  mysqli_stmt_execute($u); mysqli_stmt_close($u);

  if($return==='teacher_profile.php' && $uid>0){
    header('Location: teacher_profile.php?uid='.$uid);
  }else{
    header('Location: '.backHref('edit.php'));
  }
  exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=900">
<title>แก้ไข: <?= htmlspecialchars($item['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700&display=swap" rel="stylesheet">
<style>
  body{font-family:"Noto Sans Thai",system-ui;background:#eee;margin:0}
  .wrap{max-width:780px;margin:26px auto;background:#fff;border:1px solid #ddd;border-radius:8px}
  .topbar{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #e5e5e5;background:#fff}
  .backbtn{padding:6px 10px;border:1px solid #d0d0d0;border-radius:8px;background:#fff;cursor:pointer}
  form{padding:18px}
  label{display:block;margin:10px 0 6px;font-weight:700}
  input[type="text"],input[type="file"],select{width:100%;height:38px;border:1px solid #cfcfcf;border-radius:8px;padding:6px 12px}
  .img-prev img{max-width:180px;height:auto;border:1px solid #ddd;border-radius:6px;margin:10px 0}
  .actions{display:flex;gap:10px;margin-top:18px;flex-wrap:wrap}
  .btn{padding:10px 16px;border:1px solid #cfcfcf;border-radius:10px;background:#f5f5f5;text-decoration:none;color:#222;cursor:pointer}
  .primary{background:#2d6cdf;color:#fff;border-color:#2d6cdf}
  .danger{background:#ef4444;color:#fff;border-color:#ef4444}
</style>
<script>
  function confirmDelete(form){
    if(confirm('ยืนยันลบผลงานนี้หรือไม่? การลบไม่สามารถย้อนกลับได้.')){
      form.action.value='delete';
      form.submit();
    }
  }
</script>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <button class="backbtn" onclick="if(history.length>1){history.back()}else{location.href='<?= htmlspecialchars(backHref('edit.php')) ?>'}">← ย้อนกลับ</button>
    <div style="margin-left:auto"><a href="<?= $uid>0 ? 'teacher_profile.php?uid='.$uid : 'teacher_profile.php' ?>">โปรไฟล์อาจารย์</a></div>
  </div>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">

    <label>ชื่อเรื่อง</label>
    <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>

    <label>ประเภทผลงาน</label>
    <select name="type_ID" required>
      <option value="">-- เลือกประเภท --</option>
      <?php foreach($types as $t): ?>
        <option value="<?= (int)$t['type_ID'] ?>" <?= $t['type_ID']==$item['type_ID']?'selected':'' ?>>
          <?= htmlspecialchars($t['type_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>รูปภาพ (1:1)</label>
    <div class="img-prev"><img src="<?= htmlspecialchars($item['image_path'] ?: 'assets/logo.png') ?>"></div>
    <input type="file" name="image" accept="image/*">

    <div class="actions">
      <button class="btn primary" type="submit">บันทึก</button>
      <button class="btn danger" type="button" onclick="confirmDelete(this.form)">ลบผลงาน</button>
      <a class="btn" href="<?= $uid>0 && $return==='teacher_profile.php' ? 'teacher_profile.php?uid='.$uid : backHref('edit.php') ?>">ยกเลิก</a>
    </div>
  </form>
</div>
</body>
</html>
