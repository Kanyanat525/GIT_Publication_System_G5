<?php
require_once 'db_connect.php';

/* รับ UID ของอาจารย์ (ถ้ามี) เพื่อใช้กลับหน้าโปรไฟล์ */
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

/* โหลดประเภทผลงานสำหรับ select */
$types = [];
$rs = mysqli_query($conn, "SELECT type_ID, type_name FROM publicationtype ORDER BY type_name ASC");
while ($row = mysqli_fetch_assoc($rs)) $types[] = $row;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เพิ่มผลงานตีพิมพ์</title>
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
      <a href="<?= $uid? 'teacher_profile.php?uid='.$uid : 'Homepage_lecturer.php' ?>"
         class="px-3 py-1.5 rounded-lg border text-gray-700 hover:bg-gray-50">ย้อนกลับ</a>
      <a href="logout.php" class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">Log out</a>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-8">
  <div class="bg-white rounded-2xl shadow p-6 sm:p-8">
    <h1 class="text-xl font-bold text-gray-900 mb-6">เพิ่มผลงานตีพิมพ์</h1>

    <!-- NOTE: เปลี่ยน action ให้ตรงกับไฟล์บันทึกของคุณ (เช่น save_publication.php) -->
    <form action="save_publication.php" method="post" enctype="multipart/form-data" class="space-y-5">
      <input type="hidden" name="uid" value="<?= (int)$uid ?>">

      <!-- ชื่อเรื่อง -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">ชื่อเรื่อง</label>
        <input type="text" name="title" required
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
            <option value="<?= (int)$t['type_ID'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- วันที่ตีพิมพ์ -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">วันที่ตีพิมพ์</label>
        <input type="date" name="publish_date" required
               class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 bg-white">
      </div>

      <!-- รูปภาพ (ปก/โลโก้) -->
      <div>
        <label class="block text-gray-800 font-semibold mb-1">รูปภาพ (ปก/โลโก้)</label>
        <input type="file" name="file" accept="image/*,application/pdf"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 bg-white file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
        <p class="text-sm text-gray-500 mt-1">รองรับไฟล์ภาพหรือ PDF</p>
      </div>

      <!-- ปุ่ม -->
      <div class="pt-2 flex items-center gap-3">
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">บันทึก</button>
        <a href="<?= $uid? 'teacher_profile.php?uid='.$uid : 'Homepage_lecturer.php' ?>"
           class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">ยกเลิก</a>
      </div>
    </form>
  </div>
</main>
</body>
</html>
