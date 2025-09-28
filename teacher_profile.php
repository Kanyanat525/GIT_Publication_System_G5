<?php
require_once 'db_connect.php';
function backHref($d='edit.php'){ return !empty($_GET['return'])?$_GET['return']:$d; }

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

/* ถ้าไม่ส่ง uid โชว์รายชื่อให้เลือก */
if ($uid<=0){
  $rs = mysqli_query($conn,"SELECT UID,username,email FROM user ORDER BY username");
  echo '<!doctype html><meta charset="utf-8"><title>เลือกอาจารย์</title>
  <style>body{font-family:sans-serif;background:#eee} .box{max-width:900px;margin:20px auto;background:#fff;border:1px solid #ddd;border-radius:10px;padding:16px}
  .backbtn{padding:6px 10px;border:1px solid #d0d0d0;border-radius:8px;background:#fff;cursor:pointer}</style>
  <div class="box"><button class="backbtn" onclick="if(history.length>1){history.back()}else{location.href=\''.htmlspecialchars(backHref('edit.php')).'\'}">← ย้อนกลับ</button>
  <h2>เลือกอาจารย์</h2><ul>';
  while($u=mysqli_fetch_assoc($rs)){
    echo '<li><a href="teacher_profile.php?uid='.$u['UID'].'">'.htmlspecialchars($u['username']).'</a> — '.htmlspecialchars($u['email']).'</li>';
  }
  echo '</ul></div>'; exit;
}

/* โหลดโปรไฟล์ */
$st=mysqli_prepare($conn,"SELECT UID,username,email FROM user WHERE UID=?");
mysqli_stmt_bind_param($st,"i",$uid);
mysqli_stmt_execute($st);
$teacher=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);
if(!$teacher){ die('ไม่พบผู้ใช้'); }

/* โหลดผลงาน */
$st=mysqli_prepare($conn,"
SELECT p.pub_ID id,p.title,p.publish_date,p.file file_path,pt.type_name
FROM publication p
JOIN pub_author pa ON pa.pub_ID=p.pub_ID AND pa.UID=? AND pa.is_creator=1
LEFT JOIN publicationtype pt ON pt.type_ID=p.type_ID
ORDER BY p.pub_ID DESC");
mysqli_stmt_bind_param($st,"i",$uid);
mysqli_stmt_execute($st);
$res=mysqli_stmt_get_result($st);
$items=[];
while($r=mysqli_fetch_assoc($res)){ $r['year']=$r['publish_date']? (date('Y',strtotime($r['publish_date']))+543):''; $items[]=$r; }
mysqli_stmt_close($st);

function thumbPath($p){ return ($p && preg_match('/\.(jpe?g|png|gif|webp)$/i',$p) && file_exists($p))? $p : 'assets/logo.png'; }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1200">
<title>โปรไฟล์อาจารย์: <?= htmlspecialchars($teacher['username']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700;900&display=swap" rel="stylesheet">
<style>
  *,*:before,*:after{box-sizing:border-box}
  body{margin:0;background:#eee;font-family:'Noto Sans Thai',system-ui}
  .wrap{max-width:1100px;margin:0 auto}
  .top{background:#fff;border-bottom:1px solid #ddd;padding:14px 16px;display:flex;align-items:center;gap:14px}
  .backbtn{padding:6px 10px;border:1px solid #d0d0d0;border-radius:8px;background:#fff;cursor:pointer}
  .avatar{width:56px;height:56px;border-radius:50%;background:#dbeafe;color:#1e40af;display:inline-flex;align-items:center;justify-content:center;font-weight:900}
  .name{font-weight:900;font-size:20px}
  .right{margin-left:auto}
  .btn{display:inline-block;padding:8px 14px;border:1px solid #d0d0d0;border-radius:10px;background:#2d6cdf;color:#fff;text-decoration:none}
  .list{background:#fff;margin:16px;border-radius:8px;border:1px solid #e4e4e4}
  .item{display:flex;gap:14px;padding:12px 14px;border-top:1px solid #f0f0f0}
  .item:first-child{border-top:0}
  .thumb{width:78px;height:78px;background:#f7f7f7;border:1px solid #ddd;border-radius:10px;overflow:hidden;flex:0 0 78px}
  .thumb img{width:100%;height:100%;object-fit:cover}
  .title{margin:0 0 6px 0;font-weight:800}
  .meta{color:#555}
  .actions{margin-top:6px}
  .pill{display:inline-block;padding:4px 10px;border:1px solid #d7d7d7;border-radius:8px;background:#f5f5f5;color:#222;text-decoration:none;font-size:12px}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <button class="backbtn" onclick="if(history.length>1){history.back()}else{location.href='<?= htmlspecialchars(backHref('edit.php')) ?>'}">← ย้อนกลับ</button>
    <div class="avatar"><?= htmlspecialchars(mb_substr($teacher['username'],0,1)) ?></div>
    <div>
      <div class="name"><?= htmlspecialchars($teacher['username']) ?></div>
      <div style="color:#777"><?= htmlspecialchars($teacher['email'] ?? '') ?></div>
    </div>
    <div class="right">
      <a class="btn" href="add_publication.php?uid=<?= (int)$teacher['UID'] ?>">+ เพิ่มผลงาน</a>
    </div>
  </div>

  <div class="list">
    <?php if(empty($items)): ?>
      <div style="padding:14px;color:#666">ยังไม่มีผลงาน</div>
    <?php else: foreach($items as $row): ?>
      <div class="item">
        <div class="thumb"><img src="<?= htmlspecialchars(thumbPath($row['file_path'])) ?>"></div>
        <div>
          <p class="title"><?= htmlspecialchars($row['title']) ?></p>
          <div class="meta"><?= htmlspecialchars($row['type_name'] ?? '') ?> <?= $row['year'] ? '• '.$row['year'] : '' ?></div>
          <div class="actions">
            <a class="pill" href="edit_data.php?id=<?= (int)$row['id'] ?>&return=teacher_profile.php&uid=<?= (int)$teacher['UID'] ?>">แก้ไข</a>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
</body>
</html>
