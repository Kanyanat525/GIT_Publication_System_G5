<?php
require_once "session_check.php";
require_once 'db_connect.php';

/* --- เลือก UID ของอาจารย์ --- */
$uid = 0;

// 1) ถ้ามี session และเป็นอาจารย์
if (isset($_SESSION['UID'], $_SESSION['user_type_ID']) && $_SESSION['user_type_ID'] == 3) {
    $uid = (int)$_SESSION['UID'];
}
// 2) ถ้ามีการส่ง ?uid= มาจาก teacher_profile.php
elseif (isset($_GET['uid']) && ctype_digit($_GET['uid'])) {
    $uid = (int)$_GET['uid'];
}
// 3) fallback: เอาอาจารย์คนแรกจากฐานข้อมูล
else {
    $res = mysqli_query($conn, "SELECT UID FROM user WHERE user_type_ID=3 ORDER BY UID ASC LIMIT 1");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    if ($row) $uid = (int)$row['UID'];
}

if ($uid <= 0) {
    die("ไม่พบข้อมูลอาจารย์");
}

/* --- ข้อมูลอาจารย์ --- */
$st = mysqli_prepare($conn, "SELECT UID, username, email FROM user WHERE UID=? AND user_type_ID=3");
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);

if (!$user) {
    die("ไม่พบข้อมูลอาจารย์ (UID={$uid})");
}

/* --- ดึงประวัติการแก้ไข --- */
$sql = "
  SELECT eh.edit_ID, eh.date, eh.time, eh.pub_ID, p.title
  FROM edit_history eh
  LEFT JOIN publication p ON p.pub_ID = eh.pub_ID
  WHERE eh.UID = ?
  ORDER BY eh.date DESC, eh.time DESC, eh.edit_ID DESC
";
$st = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);

$histories = [];
while ($row = mysqli_fetch_assoc($rs)) {
    $histories[] = $row;
}
mysqli_stmt_close($st);

/* helper: วันเวลาไทย */
function th_datetime($date, $time) {
    if(!$date) return '';
    $ts = strtotime($date . ($time ? " ".$time : " 00:00:00"));
    return $ts ? date("d/m/Y | H.i", $ts) . " น." : '';
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <title>ประวัติการแก้ไข</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&display=swap" rel="stylesheet">
  <style>body{font-family:'Noto Sans Thai',system-ui}</style>
</head>
<body class="min-h-screen bg-gradient-to-b from-white via-blue-200 to-blue-100 flex flex-col">

  <!-- Header -->
<header class="bg-white shadow">
  <div class="w-full px-6 py-4 flex items-center justify-between">
    <!-- ทำให้หัวข้อเป็นลิงก์กลับหน้า index.php -->
    <a href="index.php"
       class="text-2xl font-bold text-blue-700 tracking-wider hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">
      ระบบบริหารจัดการผลงานตีพิมพ์
    </a>

    <div class="flex items-center gap-3">
      <!-- ปุ่มย้อนกลับ: ถ้ามี referrer ให้ย้อนกลับ, ถ้าไม่มีกลับหน้า index.php -->
      <a href="index.php"
         onclick="if (document.referrer) { history.back(); return false; }"
         class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-blue-700 hover:bg-blue-50">
        ย้อนกลับ
      </a>

      <!-- ปุ่มออกจากระบบ-->
      <a href="logout.php"
         class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
        ออกจากระบบ
      </a>
    </div>
  </div>
</header>


  <!-- Content -->
  <main class="flex-1 w-full">
    <div class="max-w-5xl mx-auto px-4 py-8">
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="mb-1 text-center text-xl font-bold text-gray-900">ประวัติการแก้ไข</div>
        <div class="mb-6 text-center text-gray-600">
          อาจารย์: <span class="font-semibold"><?= htmlspecialchars($user['username']) ?></span>
          (UID: <?= (int)$user['UID'] ?>)
        </div>

        <?php if (empty($histories)): ?>
          <div class="text-center text-gray-600 py-8">ยังไม่มีประวัติการแก้ไข</div>
        <?php else: ?>
        <div class="overflow-x-auto">
           <table class="w-full text-left border-separate border-spacing-x-2 border-spacing-y-2">
              <thead>
                 <tr class="bg-gray-600 text-white">
                 <th class="px-4 py-3 text-center w-56 rounded-lg">วันที่</th>
                 <th class="px-4 py-3 rounded-lg">ผลงานที่แก้ไข</th>
                 <th class="px-4 py-3 text-center w-32 rounded-lg">รหัสผลงาน</th>
               </tr>
              </thead>
              <tbody>
                <?php foreach ($histories as $index => $h): ?>
                 <tr class="hover:bg-gray-50">
                   <td class="px-4 py-2 text-center"><?= th_datetime($h['date'] ?? '', $h['time'] ?? '') ?></td>
                   <td class="px-4 py-2">
                     <span class="inline-block max-w-[560px] truncate align-bottom">
                       <?= htmlspecialchars($h['title'] ?? '— ไม่พบชื่อเรื่อง —') ?>
                     </span>
                   </td>
                   <td class="px-4 py-2 text-center"><?= (int)($h['pub_ID'] ?? 0) ?></td>
                 </tr>
                 <!-- เส้นขั้นคั่นแต่ละข้อมูล -->
                 <?php if ($index < count($histories) - 1): ?>
                   <tr>
                     <td colspan="3" class="px-2">
                       <div class="h-px bg-gray-200"></div>
                     </td>
                   </tr>
                 <?php endif; ?>
              <?php endforeach; ?>
              </tbody>

            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </main>
</body>
</html>