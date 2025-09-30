<?php
// admin.php — ใช้สคีมาตาม publication_db.sql เท่านั้น
// ตาราง: user, user_type, registration_request

require_once "session_check.php";
require_once 'db_connect.php';

if ($conn->connect_error) {
  die("DB connect error");
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash($msg){ $_SESSION['flash']=$msg; }
function back($extra=''){ header('Location: admin.php'.$extra); exit; }

$tab = $_GET['tab'] ?? 'users'; // users | requests

// ---------- Actions ----------
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = $_POST['action'] ?? '';

  // เพิ่มผู้ใช้
  if ($action==='add_user') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pwd_raw  = trim($_POST['password'] ?? '');
    $user_type_ID = (int)($_POST['user_type_ID'] ?? 0);

    if ($username==='' || $email==='' || $pwd_raw==='' || $user_type_ID<=0) {
      flash('กรอกข้อมูลให้ครบ'); back();
    }
    $pwd_hash = password_hash($pwd_raw, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO user (username,password,email,user_type_ID) VALUES (?,?,?,?)");
    $stmt->bind_param('sssi', $username, $pwd_hash, $email, $user_type_ID);
    $ok=$stmt->execute(); $stmt->close();
    flash($ok?'เพิ่มผู้ใช้สำเร็จ':'เพิ่มผู้ใช้ไม่สำเร็จ');
    back();
  }

  // แก้ไขผู้ใช้
  if ($action==='update_user') {
    $UID  = (int)($_POST['UID'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $user_type_ID = (int)($_POST['user_type_ID'] ?? 0);

    if ($UID<=0 || $username==='' || $email==='' || $user_type_ID<=0) {
      flash('ข้อมูลไม่ครบ'); back();
    }
    $stmt = $conn->prepare("UPDATE user SET username=?, email=?, user_type_ID=? WHERE UID=?");
    $stmt->bind_param('ssii', $username, $email, $user_type_ID, $UID);
    $ok=$stmt->execute(); $stmt->close();
    flash($ok?'บันทึกแล้ว':'บันทึกไม่สำเร็จ'); back();
  }

  // ลบผู้ใช้
  if ($action==='delete_user') {
    $UID = (int)($_POST['UID'] ?? 0);
    if($UID>0){
      $stmt=$conn->prepare("DELETE FROM user WHERE UID=?");
      $stmt->bind_param('i',$UID);
      $ok=$stmt->execute(); $stmt->close();
      flash($ok?'ลบแล้ว':'ลบไม่สำเร็จ');
    }
    back();
  }

  // รีเซ็ตรหัสผ่าน
  if ($action==='reset_password') {
    $UID = (int)($_POST['UID'] ?? 0);
    $new = trim($_POST['new_password'] ?? '');
    if($UID>0 && $new!==''){
      $hash = password_hash($new, PASSWORD_BCRYPT);
      $stmt = $conn->prepare("UPDATE user SET password=? WHERE UID=?");
      $stmt->bind_param('si',$hash,$UID);
      $ok=$stmt->execute(); $stmt->close();
      flash($ok?'รีเซ็ตรหัสผ่านแล้ว':'รีเซ็ตรหัสผ่านไม่สำเร็จ');
    } else {
      flash('ระบุรหัสผ่านใหม่');
    }
    back();
  }

  // อนุมัติคำร้อง -> สร้าง user แล้วลบคำร้อง
  if ($action==='approve_request') {
    $request_ID = (int)($_POST['request_ID'] ?? 0);
    $temp_password = trim($_POST['temp_password'] ?? '');

    if($request_ID>0 && $temp_password!==''){
      $stmt=$conn->prepare("SELECT username,email,request_user_type_ID FROM registration_request WHERE request_ID=?");
      $stmt->bind_param('i',$request_ID);
      $stmt->execute();
      $stmt->bind_result($rqUser,$rqEmail,$rqType);
      if($stmt->fetch()){
        $stmt->close();
        $hash = password_hash($temp_password, PASSWORD_BCRYPT);
        $ins=$conn->prepare("INSERT INTO user (username,password,email,user_type_ID) VALUES (?,?,?,?)");
        $ins->bind_param('sssi',$rqUser,$hash,$rqEmail,$rqType);
        if($ins->execute()){
          $ins->close();
          $del=$conn->prepare("DELETE FROM registration_request WHERE request_ID=?");
          $del->bind_param('i',$request_ID);
          $del->execute(); $del->close();
          flash('อนุมัติคำร้องและสร้างผู้ใช้เรียบร้อย');
        } else {
          $ins->close();
          flash('สร้างผู้ใช้ไม่สำเร็จ (อาจซ้ำ username/email)');
        }
      } else {
        $stmt->close();
        flash('ไม่พบคำร้อง');
      }
    } else {
      flash('ข้อมูลอนุมัติไม่ครบ');
    }
    back('?tab=requests');
  }

  // ลบคำร้อง
  if ($action==='delete_request') {
    $request_ID = (int)($_POST['request_ID'] ?? 0);
    if($request_ID>0){
      $del=$conn->prepare("DELETE FROM registration_request WHERE request_ID=?");
      $del->bind_param('i',$request_ID);
      $ok=$del->execute(); $del->close();
      flash($ok?'ลบคำร้องแล้ว':'ลบคำร้องไม่สำเร็จ');
    }
    back('?tab=requests');
  }
}

// ออกจากระบบ
if(isset($_GET['logout'])){
  session_start();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'],$p['domain'],$p['secure'],$p['httponly']);
  }
  session_destroy();
  header('Location: index.php'); exit;
}

// ---------- Query data ----------
$users = [];
if ($tab==='users') {
  $sql = "SELECT u.UID, u.username, u.email, u.user_type_ID, ut.type_name
          FROM user u LEFT JOIN user_type ut ON u.user_type_ID = ut.user_type_ID
          ORDER BY u.UID ASC";
  if ($res = $conn->query($sql)) {
    while($row=$res->fetch_assoc()) $users[]=$row;
    $res->close();
  }
}

$requests = [];
if ($tab==='requests') {
  $sql = "SELECT rr.request_ID, rr.username, rr.email, rr.request_user_type_ID, rr.request_date, ut.type_name
          FROM registration_request rr
          LEFT JOIN user_type ut ON rr.request_user_type_ID = ut.user_type_ID
          ORDER BY rr.request_date DESC, rr.request_ID DESC";
  if ($res = $conn->query($sql)) {
    while($row=$res->fetch_assoc()) $requests[]=$row;
    $res->close();
  }
}

// user types (ใช้ทั้งสองแท็บ)
$userTypes = [];
if ($r = $conn->query("SELECT user_type_ID, type_name FROM user_type ORDER BY user_type_ID")) {
  while($t=$r->fetch_assoc()) $userTypes[]=$t;
  $r->close();
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin: จัดการบัญชีผู้ใช้</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font Noto Sans Thai -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* กำหนด Noto Sans Thai เป็นฟอนต์หลัก */
        body {
            margin: 0;
            font-family: 'Noto Sans Thai', sans-serif;
            min-height: 100vh;
            /* ไล่สีจากบนลงล่างตามที่ผู้ใช้ร้องขอ */
            background: linear-gradient(to bottom, #FFFFFF 19%, #B1D3FE 54%, #CAE2FF 92%);
            /* ลบ display: flex; justify-content: center; align-items: center; 
               เพื่อรักษารูปแบบการแสดงผลแบบเอกสารที่สามารถเลื่อนได้ */
        }
        .header-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }
        
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-white via-blue-200 to-blue-100">

<header class="bg-white shadow">
  <div class="w-full px-6 py-4 flex items-center justify-between">
    <a href="index.php"
       class="text-2xl font-bold text-blue-700 tracking-wider hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">
      ระบบบริหารจัดการผลงานตีพิมพ์
    </a>
    <div class="flex items-center gap-3">
      <a href="index.php"
         onclick="if (document.referrer) { history.back(); return false; }"
         class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-blue-700 hover:bg-blue-50">
        ย้อนกลับ
      </a>
      <a href="?logout=1"
         class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
        ออกจากระบบ
      </a>
    </div>
  </div>
</header>

<div class="max-w-6xl mx-auto p-6">
  <div class="bg-white shadow rounded-2xl p-6">
<div class="mb-4">
  <h2 class="text-2xl font-bold text-gray-700">
    Admin: <?= $tab==='requests' ? 'ตรวจสอบคำร้อง' : 'จัดการบัญชีผู้ใช้' ?>
  </h2>
</div>


    <?php if(!empty($_SESSION['flash'])): ?>
      <div class="mb-4 px-4 py-2 rounded-lg bg-green-100 text-green-800">
        <?=h($_SESSION['flash']); unset($_SESSION['flash']);?>
      </div>
    <?php endif; ?>

    <?php if ($tab==='users'): ?>
      <div class="flex justify-end gap-2 mb-4">
        <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                onclick="document.getElementById('dlgAdd').showModal();">+ เพิ่มบัญชีผู้ใช้ใหม่</button>
        <a class="px-4 py-2 rounded-lg bg-cyan-500 text-white hover:bg-cyan-600" href="?tab=requests">ตรวจสอบคำร้อง</a>
      </div>

      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-600 text-white">
            <th class="px-4 py-2">UID</th>
            <th class="px-4 py-2">Username</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">สิทธิ์ผู้ใช้งาน</th>
            <th class="px-4 py-2">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!$users): ?>
            <tr><td colspan="5" class="text-center text-gray-500 py-4">ยังไม่มีผู้ใช้</td></tr>
          <?php else: foreach($users as $u): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= (int)$u['UID'] ?></td>
              <td class="px-4 py-2"><?= h($u['username']) ?></td>
              <td class="px-4 py-2"><?= h($u['email']) ?></td>
              <td class="px-4 py-2">
                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700">
                  <?= h($u['type_name'] ?? ('ID:'.$u['user_type_ID'])) ?>
                </span>
              </td>
              <td class="px-4 py-2">
                <div class="flex gap-2">
                  <button class="px-3 py-1 rounded-lg bg-gray-600 text-white hover:bg-gray-700"
                          onclick='openEdit(<?= (int)$u["UID"] ?>,<?= json_encode($u["username"]) ?>,<?= json_encode($u["email"]) ?>,<?= (int)$u["user_type_ID"] ?>)'>แก้ไข</button>
                  <form method="post" onsubmit="return confirm('ลบผู้ใช้นี้?');">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="UID" value="<?= (int)$u['UID'] ?>">
                    <button class="px-3 py-1 rounded-lg bg-red-500 text-white hover:bg-red-600">ลบ</button>
                  </form>
                  <button class="px-3 py-1 rounded-lg bg-amber-400 text-white hover:bg-amber-500"
                          onclick="openReset(<?= (int)$u['UID'] ?>)">รีเซ็ตรหัสผ่าน</button>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      
      <div class="flex justify-end mt-4 space-x-2">
        <a href="suggestion.php" 
          class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
             class="lucide lucide-message-square mr-1">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z"/>
        </svg>
        เสนอแนะเกี่ยวกับระบบ
        </a>
        <span class="text-gray-400">|</span>

        <a href="User_manual.php" 
          class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
             class="lucide lucide-book mr-1">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 7H20v13"/>
        </svg>
        คู่มือการใช้งาน
        </a>
        <span class="text-gray-400">|</span>
        <a href="feature_editing_history.php" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">ประวัติการแก้ไข</a>
      </div>

    <?php else: // --- tab=requests --- ?>
      <div class="mb-4 text-gray-600">คำร้องสมัครสมาชิกจากตาราง <code>registration_request</code></div>

      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-600 text-white">
            <th class="px-4 py-2">รหัสคำร้อง</th>
            <th class="px-4 py-2">Username</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">สิทธิ์ที่ขอ</th>
            <th class="px-4 py-2">วันที่ขอ</th>
            <th class="px-4 py-2">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!$requests): ?>
            <tr><td colspan="6" class="text-center text-gray-500 py-4">ยังไม่มีคำร้อง</td></tr>
          <?php else: foreach($requests as $r): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= (int)$r['request_ID'] ?></td>
              <td class="px-4 py-2"><?= h($r['username']) ?></td>
              <td class="px-4 py-2"><?= h($r['email']) ?></td>
              <td class="px-4 py-2">
                <span class="px-3 py-1 rounded-full bg-sky-100 text-sky-700">
                  <?= h($r['type_name'] ?? ('ID:'.$r['request_user_type_ID'])) ?>
                </span>
              </td>
              <td class="px-4 py-2"><?= h($r['request_date']) ?></td>
              <td class="px-4 py-2">
                <div class="flex gap-2">
                  <form method="post" class="flex items-center gap-2" onsubmit="return confirm('อนุมัติคำร้องนี้?');">
                    <input type="hidden" name="action" value="approve_request">
                    <input type="hidden" name="request_ID" value="<?= (int)$r['request_ID'] ?>">
                    <input type="password" name="temp_password" placeholder="รหัสเริ่มต้น" required
                           class="border rounded px-2 py-1">
                    <button class="px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">อนุมัติ</button>
                  </form>
                  <form method="post" onsubmit="return confirm('ลบคำร้องนี้?');">
                    <input type="hidden" name="action" value="delete_request">
                    <input type="hidden" name="request_ID" value="<?= (int)$r['request_ID'] ?>">
                    <button class="px-3 py-1 rounded-lg bg-red-500 text-white hover:bg-red-600">ลบ</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>
</div>

<!-- Dialog: Add -->
<dialog id="dlgAdd" class="rounded-xl p-0">
  <form method="post" class="p-6 space-y-4" onsubmit="return confirm('ยืนยันเพิ่มผู้ใช้?');">
    <h3 class="text-lg font-bold">เพิ่มบัญชีผู้ใช้ใหม่</h3>
    <input type="hidden" name="action" value="add_user">
    <input type="text" name="username" placeholder="Username" required class="w-full border rounded px-3 py-2">
    <input type="email" name="email" placeholder="Email" required class="w-full border rounded px-3 py-2">
    <input type="password" name="password" placeholder="รหัสผ่านเริ่มต้น" required class="w-full border rounded px-3 py-2">
    <select name="user_type_ID" required class="w-full border rounded px-3 py-2">
      <option value="">-- เลือกสิทธิ์ผู้ใช้ --</option>
      <?php foreach($userTypes as $t): ?>
        <option value="<?= (int)$t['user_type_ID'] ?>"><?= h($t['type_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="flex justify-end gap-2">
      <button type="button" class="px-3 py-1 rounded bg-gray-200" onclick="document.getElementById('dlgAdd').close()">ยกเลิก</button>
      <button class="px-3 py-1 rounded bg-blue-600 text-white">บันทึก</button>
    </div>
  </form>
</dialog>

<!-- Dialog: Edit -->
<dialog id="dlgEdit" class="rounded-xl p-0">
  <form method="post" class="p-6 space-y-4" onsubmit="return confirm('บันทึกการแก้ไข?');">
    <h3 class="text-lg font-bold">แก้ไขผู้ใช้</h3>
    <input type="hidden" name="action" value="update_user">
    <input type="hidden" name="UID" id="editUID">
    <input type="text" name="username" id="editUsername" required class="w-full border rounded px-3 py-2">
    <input type="email" name="email" id="editEmail" required class="w-full border rounded px-3 py-2">
    <select name="user_type_ID" id="editType" required class="w-full border rounded px-3 py-2">
      <?php foreach($userTypes as $t): ?>
        <option value="<?= (int)$t['user_type_ID'] ?>"><?= h($t['type_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="flex justify-end gap-2">
      <button type="button" class="px-3 py-1 rounded bg-gray-200" onclick="document.getElementById('dlgEdit').close()">ยกเลิก</button>
      <button class="px-3 py-1 rounded bg-blue-600 text-white">บันทึก</button>
    </div>
  </form>
</dialog>

<!-- Dialog: Reset Password -->
<dialog id="dlgReset" class="rounded-xl p-0">
  <form method="post" class="p-6 space-y-4" onsubmit="return confirm('รีเซ็ตรหัสผ่าน?');">
    <h3 class="text-lg font-bold">รีเซ็ตรหัสผ่าน</h3>
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="UID" id="resetUID">
    <input type="password" name="new_password" placeholder="รหัสผ่านใหม่" required class="w-full border rounded px-3 py-2">
    <div class="flex justify-end gap-2">
      <button type="button" class="px-3 py-1 rounded bg-gray-200" onclick="document.getElementById('dlgReset').close()">ยกเลิก</button>
      <button class="px-3 py-1 rounded bg-amber-500 text-white">ยืนยัน</button>
    </div>
  </form>
</dialog>

<script>
  function openEdit(uid, username, email, typeId){
    document.getElementById('editUID').value = uid;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value = email;
    document.getElementById('editType').value = parseInt(typeId,10);
    document.getElementById('dlgEdit').showModal();
  }
  function openReset(uid){
    document.getElementById('resetUID').value = uid;
    document.getElementById('dlgReset').showModal();
  }
</script>
</body>
</html>