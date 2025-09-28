<?php
require_once 'db_connect.php';

/* ----------------- helper สำหรับปุ่มย้อนกลับ ----------------- */
function backHref($default = 'edit.php') {
  return !empty($_GET['return']) ? $_GET['return'] : $default;
}

/* ----------------- รับพารามิเตอร์ ----------------- */
$tab = $_GET['tab'] ?? 'บทความ';
$validTabs = ['บทความ','วารสาร','ตำรา','อื่นๆ'];
if (!in_array($tab, $validTabs, true)) $tab = 'บทความ';

$search   = $_GET['search'] ?? '';
$yearBE   = $_GET['year']   ?? '';
$yearCE   = $yearBE !== '' ? (string)((int)$yearBE - 543) : '';

/* ----------------- map tab -> type_ID ----------------- */
$tabToTypeId = [
  'วารสาร' => 1,
  'บทความ' => 2,
  'อื่นๆ'   => 3,
  'ตำรา'   => 4,
];
$typeId = $tabToTypeId[$tab] ?? 2;

/* ----------------- สร้างรายการปี (พ.ศ.) ----------------- */
$years = [];
$yrSql = "SELECT DISTINCT YEAR(p.publish_date) y FROM publication p WHERE p.type_ID=? ORDER BY y DESC";
$st = mysqli_prepare($conn, $yrSql);
mysqli_stmt_bind_param($st, "i", $typeId);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
while($r = mysqli_fetch_assoc($rs)) if ($r['y']) $years[] = (string)((int)$r['y']+543);
mysqli_stmt_close($st);
if (empty($years)) {
  $be = (int)date('Y')+543; for($y=$be;$y>=$be-10;$y--) $years[]=(string)$y;
}

/* ----------------- ดึงรายการ ----------------- */
$sql = "
SELECT p.pub_ID id, p.title, p.publish_date, p.file image_path,
       COALESCE(GROUP_CONCAT(u.username SEPARATOR ', '),'') AS by_who
FROM publication p
LEFT JOIN pub_author pa ON pa.pub_ID=p.pub_ID AND pa.is_creator=1
LEFT JOIN user u ON u.UID=pa.UID
WHERE p.type_ID=?
";
$params = [$typeId]; $types="i";
if ($search!==''){ $sql.=" AND (p.title LIKE ? OR u.username LIKE ?)"; $like="%$search%"; $params[]=$like; $params[]=$like; $types.="ss"; }
if ($yearCE!==''){ $sql.=" AND YEAR(p.publish_date)=?"; $params[]=$yearCE; $types.="s"; }
$sql.=" GROUP BY p.pub_ID ORDER BY p.pub_ID DESC";

$st = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($st,$types,...$params);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$items=[]; while($row=mysqli_fetch_assoc($res)){ $row['year']=$row['publish_date']? (date('Y',strtotime($row['publish_date']))+543):''; $items[]=$row; }
mysqli_stmt_close($st);

/* ----------------- uid สำหรับลิงก์โปรไฟล์ (ถ้ามี) ----------------- */
$uidTop = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1280">
<title><?= htmlspecialchars($tab) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box}
html,body{margin:0;padding:0}
body{font-family:"Noto Sans Thai","Segoe UI",Tahoma,Arial,sans-serif;background:#eee;color:#222}
a{color:inherit;text-decoration:none}
.wrap{max-width:1150px;margin:0 auto;background:#f3f3f3;}

.topbar{background:#fff;border-bottom:1px solid #d8d8d8}
.topbar .row{display:flex;align-items:center;padding:10px 18px;gap:16px}
.spacer{flex:1}
.auth{font-size:14px;color:#666}
.auth a{color:#666}
.auth a+a{margin-left:8px}
.backbtn{padding:6px 10px;border:1px solid #d0d0d0;border-radius:8px;background:#fff;cursor:pointer;font-family:inherit}
.backbtn:hover{background:#f5f5f5}

.nav{background:#fff;border-bottom:1px solid #d8d8d8}
.nav .row{display:flex;align-items:center;padding:10px 18px;gap:18px}
.tabs{display:flex;gap:22px}
.tab{font-weight:800;color:#757575;padding:6px 2px}
.tab.active{color:#000;border-bottom:3px solid #000}
.searchbar{margin-left:auto;display:flex;align-items:center;gap:10px}
.searchwrap{position:relative}
.searchwrap input{width:240px;height:32px;border:1px solid #cfcfcf;border-radius:16px;padding:0 36px 0 12px;background:#fff;outline:none}
.iconbtn{width:28px;height:28px;border:1px solid #cfcfcf;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#fff;cursor:pointer}
.searchicon{position:absolute;right:4px;top:50%;transform:translateY(-50%)}
.yearselect{height:32px;border:1px solid #cfcfcf;border-radius:8px;padding:0 8px;background:#fff}

.hero{margin:16px;border:1px solid #a9c7f1;background:#b9d6ff;border-radius:2px;height:180px;position:relative;overflow:hidden}
.hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}

.list{background:#fff;margin:0 16px 24px;border-radius:2px;padding:6px 10px}
.item{display:flex;gap:16px;align-items:flex-start;padding:14px 8px}
.thumb{width:78px;height:78px;border:1px solid #d6d6d6;border-radius:10px;background:#f7f7f7;overflow:hidden;flex:0 0 78px}
.thumb img{width:100%;height:100%;object-fit:cover}
.meta{flex:1}
.title{margin:0 0 6px 0;font-weight:800}
.badges{display:flex;gap:8px;margin:6px 0}
.pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border:1px solid #d7d7d7;border-radius:8px;background:#f5f5f5;font-size:12px;color:#333}
.by{margin:0;color:#333}
.year{margin:0}
.footer-switch{margin:0 16px 30px;color:#777;font-size:14px}
.footer-switch a{text-decoration:underline;margin-right:10px}
</style>
</head>
<body>
<div class="wrap">

  <!-- Topbar -->
  <div class="topbar">
    <div class="row">
      <button class="backbtn" type="button"
        onclick="if(history.length>1){history.back()}else{location.href='<?= htmlspecialchars(backHref('edit.php')) ?>'}">← ย้อนกลับ</button>
      <div class="spacer"></div>
      <div class="auth">
        <a href="<?= $uidTop>0 ? 'teacher_profile.php?uid='.$uidTop : 'teacher_profile.php' ?>">โปรไฟล์อาจารย์</a>
      </div>
    </div>
  </div>

  <!-- Nav + search -->
  <div class="nav">
    <div class="row">
      <div class="tabs">
        <span class="tab active">
          <img src="assets/logo.png" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
        </span>
        <span class="tab" style="color:#8b8b8b"><?= htmlspecialchars($tab) ?></span>
      </div>

      <form method="get" class="searchbar">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        <?php if($uidTop>0): ?><input type="hidden" name="uid" value="<?= (int)$uidTop ?>"><?php endif; ?>
        <div class="searchwrap">
          <input type="text" name="search" placeholder="ค้นหา..." value="<?= htmlspecialchars($search) ?>">
          <button class="iconbtn searchicon" title="ค้นหา">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
        </div>
        <select name="year" class="yearselect" onchange="this.form.submit()" title="เลือกปี">
          <option value="">--ทั้งหมด--</option>
          <?php foreach($years as $y): ?>
            <option value="<?= htmlspecialchars($y) ?>" <?= $yearBE===$y?'selected':'' ?>><?= htmlspecialchars($y) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <!-- Hero -->
  <div class="hero"><img src="assets/hero.jpg" alt="PSU Building"></div>

  <!-- List -->
  <div class="list">
    <?php if(empty($items)): ?>
      <div style="padding:16px 8px;color:#666">ไม่พบข้อมูล</div>
    <?php else: foreach($items as $row):
      $thumb = (!empty($row['image_path']) && preg_match('/\.(jpe?g|png|gif|webp)$/i',$row['image_path']) && file_exists($row['image_path']))
               ? $row['image_path'] : 'assets/logo.png';
    ?>
      <div class="item">
        <div class="thumb"><img src="<?= htmlspecialchars($thumb) ?>" alt=""></div>
        <div class="meta">
          <p class="title">ตัวอย่าง: <?= htmlspecialchars($row['title']) ?></p>
          <div class="badges">
            <a class="pill" href="edit_data.php?id=<?= (int)$row['id'] ?><?= $uidTop>0 ? '&return=teacher_profile.php&uid='.$uidTop : '' ?>">แก้ไข</a>
          </div>
          <p class="by"><?= htmlspecialchars($row['by_who']) ?></p>
          <?php if($row['year']): ?><p class="year"><?= htmlspecialchars($row['year']) ?></p><?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- สลับแท็บ -->
  <div class="footer-switch">
    สลับแท็บ:
    <?php foreach($validTabs as $name): ?>
      <a href="?tab=<?= urlencode($name) ?><?= $uidTop>0 ? '&uid='.$uidTop : '' ?>"><?= htmlspecialchars($name) ?></a>
    <?php endforeach; ?>
  </div>

</div>
</body>
</html>
