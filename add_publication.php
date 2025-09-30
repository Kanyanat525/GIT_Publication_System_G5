<?php
require_once "session_check.php";
require_once "db_connect.php";

/* ---------- Params ---------- */
$uid     = isset($_GET['uid'])    ? (int)$_GET['uid'] : (int)($_POST['uid'] ?? 0);
$pub_id  = isset($_GET['pub_id']) ? (int)$_GET['pub_id'] : (int)($_POST['pub_id'] ?? 0);
$is_edit = $pub_id > 0;

/* กลับไปที่ lecturer.php เสมอ (ถ้ามี uid ให้ติดไปด้วย) */
$return  = "lecturer.php" . ($uid ? "?uid={$uid}" : "");

/* ---------- ลบ (POST: action=delete) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
  // ดึง path ไฟล์ไว้เพื่อลบ
  $filePath = null;
  if ($is_edit) {
    $st = mysqli_prepare($conn, "SELECT file FROM publication WHERE pub_ID=?");
    mysqli_stmt_bind_param($st, "i", $pub_id);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    if ($row && !empty($row['file'])) $filePath = $row['file'];
  }

  // ลบความสัมพันธ์ผู้แต่ง (กัน FK)
  $st = mysqli_prepare($conn, "DELETE FROM pub_author WHERE pub_ID=?");
  mysqli_stmt_bind_param($st, "i", $pub_id);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);

  // ลบรายการผลงาน
  $st = mysqli_prepare($conn, "DELETE FROM publication WHERE pub_ID=?");
  mysqli_stmt_bind_param($st, "i", $pub_id);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);

  // ลบไฟล์จริงถ้ามี
  if ($filePath) {
    $abs = __DIR__ . '/' . ltrim($filePath, '/');
    if (is_file($abs)) @unlink($abs);
  }

  // กลับไป lecturer.php (กรณีลบ ไม่ต้องส่ง pub_id แล้ว)
  $sep = (strpos($return, '?') !== false) ? '&' : '?';
  header("Location: {$return}{$sep}deleted=1");
  exit;
}

/* ---------- บันทึก/แก้ไข (POST: action=save หรือ ไม่มี action) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? 'save') === 'save')) {
  $title        = trim($_POST['title'] ?? '');
  $type_ID      = (int)($_POST['type_ID'] ?? 0);
  $publish_date = $_POST['publish_date'] ?? null; // YYYY-mm-dd
  $filePath     = null;

  // อัปโหลดไฟล์ (ถ้ามี)
  if (!empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $safeName  = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/','_', $_FILES['file']['name']);
    $destPath  = $uploadDir . '/' . $safeName;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
      $filePath = 'uploads/' . $safeName;  // เก็บ path แบบ relative
    }
  }

  if ($is_edit) {
    // แก้ไข → รีเซ็ตสถานะกลับเป็น pending เสมอ
    if ($filePath) {
      $st = mysqli_prepare(
        $conn,
        "UPDATE publication
         SET title=?, type_ID=?, publish_date=?, file=?, status='pending'
         WHERE pub_ID=?"
      );
      mysqli_stmt_bind_param($st, "sissi", $title, $type_ID, $publish_date, $filePath, $pub_id);
    } else {
      $st = mysqli_prepare(
        $conn,
        "UPDATE publication
         SET title=?, type_ID=?, publish_date=?, status='pending'
         WHERE pub_ID=?"
      );
      mysqli_stmt_bind_param($st, "sisi", $title, $type_ID, $publish_date, $pub_id);
    }
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
  } else {
    // เพิ่มผลงานใหม่ (สถานะเริ่มต้น pending)
    $st = mysqli_prepare(
      $conn,
      "INSERT INTO publication (title, type_ID, publish_date, file, status)
       VALUES (?,?,?,?, 'pending')"
    );
    mysqli_stmt_bind_param($st, "siss", $title, $type_ID, $publish_date, $filePath);
    mysqli_stmt_execute($st);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($st);

    // ผูกผู้แต่ง
    if ($uid && $newId) {
      $st = mysqli_prepare($conn, "INSERT INTO pub_author (pub_ID, UID) VALUES (?,?)");
      mysqli_stmt_bind_param($st, "ii", $newId, $uid);
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);
    }
  }

  // กลับไป lecturer.php พร้อม pub_id ที่เพิ่งดำเนินการ
  $sep = (strpos($return, '?') !== false) ? '&' : '?';
  $redirect_pub_id = $is_edit ? $pub_id : $newId;
  header("Location: {$return}{$sep}uploaded=1&pub_id={$redirect_pub_id}");
  exit;
}

/* ---------- โหลดข้อมูลสำหรับฟอร์ม (GET) ---------- */
// ประเภทผลงาน
$types = [];
if ($rs = mysqli_query($conn, "SELECT type_ID, type_name FROM publicationtype ORDER BY type_name ASC")) {
  while ($row = mysqli_fetch_assoc($rs)) $types[] = $row;
}

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
    header("Location: {$return}");
    exit;
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

<header class="bg-white shadow">
  <div class="w-full px-6 py-4 flex items-center justify-between">
    <a href="index.php"
       class="text-2xl font-bold text-blue-700 tracking-wider hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">
      ระบบบริหารจัดการผลงานตีพิมพ์
    </a>
    <div class="flex items-center gap-3">
      <!-- ปุ่มกลับหน้าอาจารย์ -->
      <a href="<?= htmlspecialchars($return, ENT_QUOTES) ?>"
         class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-blue-700 hover:bg-blue-50">
        กลับหน้าอาจารย์
      </a>
      <!-- ปุ่มออกจากระบบ -->
      <a href="logout.php"
         class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
        ออกจากระบบ
      </a>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-8">
  <div class="bg-white rounded-2xl shadow p-6 sm:p-8">
    <h1 class="text-xl font-bold text-gray-900 mb-6"><?= $is_edit ? 'แก้ไขผลงานตีพิมพ์' : 'เพิ่มผลงานตีพิมพ์' ?></h1>

    <!-- แสดงสถานะแจ้งเตือนถ้าถูกส่งมาจาก query (optional) -->
    <?php if (isset($_GET['uploaded'])): ?>
      <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3">บันทึกข้อมูลเรียบร้อย กำลังรออนุมัติ</div>
    <?php elseif (isset($_GET['deleted'])): ?>
      <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-3">ลบผลงานเรียบร้อย</div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="space-y-5" id="pubForm">
      <input type="hidden" name="uid" value="<?= (int)$uid ?>">
      <input type="hidden" name="pub_id" value="<?= (int)$pub_id ?>">
      <input type="hidden" name="action" value="save">

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
      <div class="pt-2 flex flex-wrap items-center gap-3">
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
          <?= $is_edit ? 'บันทึกการแก้ไข' : 'บันทึก' ?>
        </button>

        <!-- ยกเลิก: กลับไป lecturer.php -->
        <a href="<?= htmlspecialchars($return, ENT_QUOTES) ?>"
           class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">ยกเลิก</a>

        <?php if ($is_edit): ?>
          <!-- ปุ่มลบ -->
          <button type="button"
                  onclick="confirmDelete()"
                  class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 ml-auto">
            ลบผลงานนี้
          </button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</main>

<script>
function confirmDelete() {
  if (confirm('คุณต้องการลบผลงานนี้หรือไม่? การลบไม่สามารถย้อนกลับได้')) {
    const f = document.getElementById('pubForm');
    f.querySelector('input[name=action]').value = 'delete';
    f.submit();
  }
}
</script>

</body>
</html>
