<?php

require_once "session_check.php";
require_once 'db_connect.php';

/* เลือก uid: ถ้าไม่ส่งมา จะใช้อาจารย์คนแรก */
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if ($uid <= 0) {
  $res = mysqli_query($conn, "SELECT UID FROM user WHERE user_type_ID=3 ORDER BY UID ASC LIMIT 1");
  $row = mysqli_fetch_assoc($res);
  if ($row) $uid = (int)$row['UID']; else die('ยังไม่มีข้อมูลอาจารย์');
}

/* ดึงข้อมูลอาจารย์ */
$st = mysqli_prepare($conn, "SELECT UID, username, email FROM user WHERE UID=?");
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);
if(!$user){ die("ไม่พบผู้ใช้ (UID=$uid)"); }

/* ดึงผลงานของอาจารย์ (รวมฟิลด์ status: ตั้งค่าเริ่มต้นเป็น Pedding) */
$st = mysqli_prepare($conn, "
  SELECT p.pub_ID, p.title, p.publish_date, pt.type_name, p.file, 
         COALESCE(p.status, 'Pedding') AS status
  FROM publication p
  JOIN pub_author pa ON pa.pub_ID=p.pub_ID AND pa.UID=?
  LEFT JOIN publicationtype pt ON pt.type_ID=p.type_ID
  ORDER BY p.publish_date DESC, p.pub_ID DESC
");
mysqli_stmt_bind_param($st, "i", $uid);
mysqli_stmt_execute($st);
$rs = mysqli_stmt_get_result($st);
$pubs=[]; while($r=mysqli_fetch_assoc($rs)){ $pubs[]=$r; }
mysqli_stmt_close($st);

/* helper: ปี พ.ศ. */
function beYear($date){ return $date? (date('Y',strtotime($date))+543) : ''; }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>โปรไฟล์อาจารย์</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700;900&display=swap" rel="stylesheet">
<style>body{font-family:'Noto Sans Thai',system-ui}</style>
</head>
<body class="min-h-screen bg-gradient-to-b from-white via-blue-200 to-blue-100">

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

      <!-- ปุ่มออกจากระบบ: ใช้พารามิเตอร์ ?logout=1 ตามโค้ดส่วนบน -->
      <a href="logout.php"
         class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
        ออกจากระบบ
      </a>
    </div>
  </div>
</header>

<?php
  // แถบแจ้งเตือนหลังอัปโหลดเสร็จ
  $uploaded_ok = (isset($_GET['uploaded']) && $_GET['uploaded'] === '1')
              || (isset($_GET['status'])   && $_GET['status']   === 'uploaded');

  if ($uploaded_ok):
      // ถ้ามี pub_id ให้หา status จากรายการนั้น
      $just_pub_id = isset($_GET['pub_id']) ? (int)$_GET['pub_id'] : 0;
      $target_status = null;

      if ($just_pub_id > 0 && !empty($pubs)) {
          foreach ($pubs as $p) {
              if ((int)$p['pub_ID'] === $just_pub_id) {
                  $target_status = strtolower(trim($p['status'] ?? 'pending'));
                  break;
              }
          }
      }

      // ถ้าไม่เจอ ใช้ผลงานล่าสุดเป็นค่า default
      if ($target_status === null && !empty($pubs)) {
          $target_status = strtolower(trim($pubs[0]['status'] ?? 'pending'));
      }

      // map สถานะเป็น HTML พร้อมสี
      if ($target_status === 'approved') {
          $status_html = '<span class="font-semibold text-green-700">อนุมัติแล้ว</span>';
      } elseif ($target_status === 'Modify') {
          $status_html = '<span class="font-semibold text-red-700">รอการแก้ไข</span>';
      } else {
          $status_html = '<span class="font-semibold text-yellow-700">รออนุมัติ</span>';
      }
?>
  <div class="max-w-7xl mx-auto px-4 mt-4">
    <div class="rounded-lg bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3">
      <div class="font-semibold">อัปโหลดสำเร็จ</div>
      <div>รายงานถูกส่งแล้ว — <?= $status_html ?></div>
    </div>
  </div>
<?php endif; ?>

<main class="max-w-6xl mx-auto px-4 py-8 space-y-6">
  <!-- การ์ดข้อมูลอาจารย์ -->
  <section class="bg-white rounded-2xl shadow p-6">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-800 font-black flex items-center justify-center">
        <?= htmlspecialchars(mb_strtoupper(mb_substr($user['username'],0,1))) ?>
      </div>
      <div>
        <div class="text-xl font-bold"><?= htmlspecialchars($user['username']) ?></div>
        <div class="text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></div>
      </div>
      <div class="ml-auto">
        <a class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
           href="add_publication.php?uid=<?= (int)$user['UID'] ?>">
          + เพิ่มผลงาน
        </a>
      </div>
    </div>
  </section>

  <!-- ตารางผลงาน -->
  <section class="bg-white rounded-2xl shadow p-6">
    <h2 class="text-lg font-bold mb-4 text-gray-800">ผลงานตีพิมพ์</h2>
    <?php if(empty($pubs)): ?>
      <div class="text-gray-600">ยังไม่มีผลงาน</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-blue-100 text-gray-800">
              <th class="px-4 py-2">ชื่อเรื่อง</th>
              <th class="px-4 py-2">ประเภท</th>
              <th class="px-4 py-2">ปี พ.ศ.</th>
              <th class="px-4 py-2">สถานะ</th>
              <th class="px-4 py-2 text-center">การจัดการ</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <?php foreach($pubs as $p): ?>
              <tr>
                <td class="px-4 py-2"><?= htmlspecialchars($p['title']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($p['type_name']) ?></td>
                <td class="px-4 py-2"><?= beYear($p['publish_date']) ?></td>
                <td class="px-4 py-2">
                  <?php
                    // รองรับทั้ง Pedding/Pending/approved/rejected
                    $status = trim((string)($p['status'] ?? 'Pedding'));
                    $norm = strtolower($status);
                    if ($norm === 'approved') {
                      echo '<span class="px-2 py-1 rounded bg-green-100 text-green-700 text-sm font-semibold">อนุมัติแล้ว</span>';
                    } elseif ($norm === 'rejected') {
                      echo '<span class="px-2 py-1 rounded bg-red-100 text-red-700 text-sm font-semibold">ไม่อนุมัติ</span>';
                    } elseif ($norm === 'pending' || $norm === 'pedding' || $norm === '') {
                      echo '<span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700 text-sm font-semibold">รออนุมัติ</span>';
                    } else {
                      // กรณีสถานะอื่นๆ แสดงเป็นป้ายเทา
                      echo '<span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-sm font-semibold">'.htmlspecialchars($status).'</span>';
                    }
                  ?>
                </td>
                <td class="px-4 py-2 text-center space-x-2">
                  <!-- ปรับให้ไป add_publication.php (โหมดแก้ไข) -->
                  <a class="inline-block px-3 py-1.5 rounded bg-gray-600 text-white hover:bg-gray-700"
                     href="add_publication.php?uid=<?= (int)$user['UID'] ?>&pub_id=<?= (int)$p['pub_ID'] ?>&return=lecturer.php?uid=<?= (int)$user['UID'] ?>">
                    แก้ไข
                  </a>
                  <form action="delete_publication.php" method="post" class="inline"
                        onsubmit="return confirm('ยืนยันลบผลงานนี้หรือไม่?');">
                    <input type="hidden" name="id" value="<?= (int)$p['pub_ID'] ?>">
                    <input type="hidden" name="uid" value="<?= (int)$user['UID'] ?>">
                    <button type="submit" class="px-3 py-1.5 rounded bg-red-600 text-white hover:bg-red-700">ลบ</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>

<!-- ปุ่มด้านล่างของหน้า: คู่มือการใช้งาน / เสนอแนะเกี่ยวกับระบบ / ประวัติการแก้ไข -->
<footer class="max-w-6xl mx-auto px-4 pb-8">
  <div class="mt-4 rounded-lg bg-blue-100/80 px-4 py-2">
    <nav class="flex items-center justify-end text-blue-700 text-[15px]">
      <!-- เสนอแนะเกี่ยวกับระบบ -->
      <a href="suggestion.php" 
       class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
             class="lucide lucide-message-square mr-1">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z"/>
        </svg>
        เสนอแนะเกี่ยวกับระบบ
      </a>

      <span class="mx-3 h-5 w-px bg-blue-300" aria-hidden="true"></span>

      <!-- คู่มือการใช้งาน -->
      <a href="manual.php" 
       class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
             class="lucide lucide-book mr-1">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 7H20v13"/>
        </svg>
        คู่มือการใช้งาน
      </a>

      <span class="mx-3 h-5 w-px bg-blue-300" aria-hidden="true"></span>

      <!-- ประวัติการแก้ไข -->
      <a href="lecturer_edit_history.php?uid=<?= (int)$user['UID'] ?>"
         class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 8v5l3 2M5.1 7.9A8 8 0 1 1 4 12h2M4 12H2" />
        </svg>
        <span class="font-boid">ประวัติการแก้ไข</span>
      </a>
    </nav>
  </div>
</footer>

</body>
</html>