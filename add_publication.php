<?php
require_once 'db_connect.php';

/* ---------- Params ---------- */
$uid     = isset($_GET['uid'])    ? (int)$_GET['uid'] : (int)($_POST['uid'] ?? 0);
$pub_id  = isset($_GET['pub_id']) ? (int)$_GET['pub_id'] : (int)($_POST['pub_id'] ?? 0);
$return  = isset($_GET['return']) ? $_GET['return']   : ($_POST['return'] ?? ($uid ? "teacher_profile.php?uid=$uid" : "Homepage_lecturer.php"));
$is_edit = $pub_id > 0;

/* ---------- บันทึกเมื่อ POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title        = trim($_POST['title'] ?? '');
  $type_ID      = (int)($_POST['type_ID'] ?? 0);
  $publish_date = $_POST['publish_date'] ?? null; // YYYY-mm-dd
  $filePath     = null;

  // อัปโหลดไฟล์ (ถ้ามี)
  if (!empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    $uploadDir = __DIR__ . '/uploads';               // เปลี่ยนได้ตามโครงสร้างโปรเจกต์
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $safeName  = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/','_', $_FILES['file']['name']);
    $destPath  = $uploadDir . '/' . $safeName;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
      $filePath = 'uploads/' . $safeName;            // path ที่เก็บใน DB (relative)
    }
  }

  if ($is_edit) {
    // แก้ไข
    if ($filePath) {
      $st = mysqli_prepare($conn, "UPDATE publication SET title=?, type_ID=?, publish_date=?, file=? WHERE pub_ID=?");
      mysqli_stmt_bind_param($st, "sissi", $title, $type_ID, $publish_date, $filePath, $pub_id);
    } else {
      $st = mysqli_prepare($conn, "UPDATE publication SET title=?, type_ID=?, publish_date=? WHERE pub_ID=?");
      mysqli_stmt_bind_param($st, "sisi", $title, $type_ID, $publish_date, $pub_id);
    }
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
  } else {
    // เพิ่มใหม่ (สถานะเริ่มต้น pending)
    $st = mysqli_prepare($conn, "INSERT INTO publication (title, type_ID, publish_date, file, status) VALUES (?,?,?,?, 'pending')");
    mysqli_stmt_bind_param($st, "siss", $title, $type_ID, $publish_date, $filePath);
    mysqli_stmt_execute($st);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($st);

    // ผูกผู้แต่ง (ถ้าต้องการ)
    if ($uid && $newId) {
      $st = mysqli_prepare($conn, "INSERT INTO pub_author (pub_ID, UID) VALUES (?,?)");
      mysqli_stmt_bind_param($st, "ii", $newId, $uid);
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);
    }
  }

  // เสร็จแล้วกลับหน้าโปรไฟล์ พร้อมแจ้งเตือน uploaded=1
  $sep = (strpos($return, '?') !== false) ? '&' : '?';
  header('Location: ' . $return . $sep . 'uploaded=1');
  exit;
}

/* ---------- โหลดข้อมูลสำหรับฟอร์ม (GET) ---------- */
// ประเภทผลงาน
$types = [];
$rs = mysqli_query($conn, "SELECT type_ID, type_name FROM publicationtype ORDER BY type_name ASC");
while ($row = mysqli_fetch_assoc($rs)) $types[] = $row;

// ค่าเริ่มต้น
$title = ''; $type_ID = ''; $publish_date = ''; $file = '';

if ($is_edit) {
  $st = mysqli_prepare($conn, "SELECT title, type_ID, publish_date, file FROM publication WHERE pub_ID=?");
  mysqli_stmt_bind_param($st, "i", $pub_id);
  mysqli_stmt_execute($st);
  $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
  mysqli_stmt_close($st);
  if ($row) {
    $title        = $row['title'] ?? '';
    $type_ID      = $row['type_ID'] ?? '';
    $publish_date = $row['publish_date'] ?? '';
    $file         = $row['file'] ?? '';
  } else {
    header("Location: $return"); exit;
  }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $is_edit ? 'แก้ไขผลงานตีพิมพ์' : 'เพิ่มผลงานตีพิมพ์' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700;900&display=swap" rel="stylesheet">
<style>body{font-family:'Noto Sans Thai',system-ui}</style>
</head>
<body class="min-h-screen bg-gradient-to-b from-white via-blue-200 to-blue-100">

<!-- Header -->
<header class="bg-white shadow">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
    <span class="text-2xl font-bold text-blue-700 tracking-wider">ระบบบริหารจัดการผลงานตีพิมพ์</span>
    <div class="flex items-center gap-3">
      <a href="<?= htmlspecialchars($return, ENT_QUOTES) ?>" class="px-3 py-1.5 rounded-lg border text-gray-700 hover:bg-gray-50">ย้อนกลับ</a>
      <a href="logout.php" class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">ออกจากระบบ</a>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-8">
  <div class="bg-white rounded-2xl shadow p-6 sm:p-8">
    <h1 class="text-xl font-bold text-gray-900 mb-6"><?= $is_edit ? 'แก้ไขผลงานตีพิมพ์' : 'เพิ่มผลงานตีพิมพ์' ?></h1>

    <!-- ส่งกลับมาหน้าเดิม (ไฟล์นี้) เพื่อเลี่ยง Not Found -->
    <form action="" method="post" enctype="multipart/form-data" class="space-y-5">
      <input type="hidden" name="uid" value="<?= (int)$uid ?>">
      <input type="hidden" name="pub_id" value="<?= (int)$pub_id ?>">
      <input type="hidden" name="return" value="<?= htmlspecialchars($return, ENT_QUOTES) ?>">

      <!-- ชื่อเรื่อง -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">ชื่อเรื่อง</label>
        <input type="text" name="title" required
               value="<?= htmlspecialchars($title) ?>"
               class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
               placeholder="ใส่ชื่อเรื่องผลงาน">
      </div>

      <!-- ประเภทผลงาน -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">ประเภทผลงาน</label>
        <select name="type_ID" required
                class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 bg-white">
          <option value="">-- เลือกประเภท --</option>
          <?php foreach($types as $t): ?>
            <option value="<?= (int)$t['type_ID'] ?>" <?= ($type_ID==$t['type_ID']?'selected':'') ?>>
              <?= htmlspecialchars($t['type_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- วันที่ตีพิมพ์ -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">วันที่ตีพิมพ์</label>
        <input type="date" name="publish_date" required
               value="<?= htmlspecialchars($publish_date) ?>"
               class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 bg-white">
      </div>

      <!-- ไฟล์แนบ -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">ไฟล์แนบ (ภาพหรือ PDF)</label>
        <input type="file" name="file" accept="image/*,application/pdf"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 bg-white file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
        <?php if ($is_edit && $file): ?>
          <p class="text-sm text-gray-500 mt-1">ไฟล์ปัจจุบัน: <?= htmlspecialchars($file) ?></p>
        <?php endif; ?>
      </div>

      <!-- ปุ่ม -->
      <div class="pt-2 flex items-center gap-3">
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
          <?= $is_edit ? 'บันทึกการแก้ไข' : 'บันทึก' ?>
        </button>
        <a href="<?= htmlspecialchars($return, ENT_QUOTES) ?>"
           class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">ยกเลิก</a>
      </div>
    </form>
  </div>
</main>
</body>
</html>

