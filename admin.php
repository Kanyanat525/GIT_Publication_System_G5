<?php
// admin.php — ดีไซน์คงเดิม + ตัวแปร/ชื่อคอลัมน์ตรงสคีมาตาม publication_db.sql
// ตารางที่ใช้: user, user_type

session_start();
require_once 'db_connect.php';

if ($conn->connect_error) {
  die("DB connect error");
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash($msg){ $_SESSION['flash']=$msg; }
function back(){ header('Location: admin.php'); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = $_POST['action'] ?? '';
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
    if(!$stmt->execute()){ flash('เพิ่มผู้ใช้ไม่สำเร็จ'); }
    else { flash('เพิ่มผู้ใช้สำเร็จ'); }
    $stmt->close(); back();
  }

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
    $ok = $stmt->execute();
    $stmt->close();
    flash($ok?'บันทึกแล้ว':'บันทึกไม่สำเร็จ'); back();
  }

  if ($action==='delete_user') {
    $UID = (int)($_POST['UID'] ?? 0);
    if($UID>0){
      $stmt=$conn->prepare("DELETE FROM user WHERE UID=?");
      $stmt->bind_param('i',$UID);
      $ok=$stmt->execute();
      $stmt->close();
      flash($ok?'ลบแล้ว':'ลบไม่สำเร็จ');
    }
    back();
  }

  if ($action==='reset_password') {
    $UID = (int)($_POST['UID'] ?? 0);
    $new = trim($_POST['new_password'] ?? '');
    if($UID>0 && $new!==''){
      $hash = password_hash($new, PASSWORD_BCRYPT);
      $stmt = $conn->prepare("UPDATE user SET password=? WHERE UID=?");
      $stmt->bind_param('si',$hash,$UID);
      $ok=$stmt->execute();
      $stmt->close();
      flash($ok?'รีเซ็ตรหัสผ่านแล้ว':'รีเซ็ตรหัสผ่านไม่สำเร็จ');
    } else { flash('ระบุรหัสผ่านใหม่'); }
    back();
  }
}

if(isset($_GET['logout'])){
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'],$p['domain'],$p['secure'],$p['httponly']);
  }
  session_destroy();
  header('Location: index.php'); exit;
}

$users = [];
$sql = "SELECT u.UID, u.username, u.email, u.user_type_ID, ut.type_name
        FROM user u LEFT JOIN user_type ut ON u.user_type_ID = ut.user_type_ID
        ORDER BY u.UID ASC";
if ($res = $conn->query($sql)) {
  while($row=$res->fetch_assoc()) $users[]=$row;
  $res->close();
}

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
</head>
<body class="min-h-screen bg-gradient-to-b from-white via-blue-200 to-blue-100">

<header class="bg-white shadow">
  <div class="w-full px-6 py-4 flex items-center justify-between">
    <!-- หัวข้อ -->
    <a href="index.php"
       class="text-2xl font-bold text-blue-700 tracking-wider hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">
      ระบบบริหารจัดการผลงานตีพิมพ์
    </a>

    <div class="flex items-center gap-3">
      <!-- ปุ่มย้อนกลับ -->
      <a href="index.php"
         onclick="if (document.referrer) { history.back(); return false; }"
         class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-blue-700 hover:bg-blue-50">
        ย้อนกลับ
      </a>

      <!-- ปุ่มออกจากระบบ -->
      <a href="?logout=1"
         class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
        ออกจากระบบ
      </a>
    </div>
  </div>
</header>

<div class="max-w-6xl mx-auto p-6">
  <div class="bg-white shadow rounded-2xl p-6">
    <h2 class="text-2xl font-bold text-gray-700 mb-4">Admin: จัดการบัญชีผู้ใช้</h2>

    <div class="flex justify-end gap-2 mb-4">
      <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
              onclick="document.getElementById('dlgAdd').showModal();">+ เพิ่มบัญชีผู้ใช้ใหม่</button>
      <a class="px-4 py-2 rounded-lg bg-cyan-500 text-white hover:bg-cyan-600" href="registration_review.php">ตรวจสอบคำร้อง</a>
    </div>

    <?php if(!empty($_SESSION['flash'])): ?>
      <div class="mb-4 px-4 py-2 rounded-lg bg-green-100 text-green-800">
        <?=h($_SESSION['flash']); unset($_SESSION['flash']);?>
      </div>
    <?php endif; ?>

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

    <div class="text-right mt-4 space-x-4">
      <a href="suggestion.php" class="text-blue-600 hover:underline">เสนอแนะเกี่ยวกับระบบ</a> |
      <a href="User_manual.php" class="text-blue-600 hover:underline">คู่มือการใช้งาน</a> |
      <a href="feature_editing_history.php" class="text-blue-600 hover:underline">ประวัติการแก้ไข</a>
    </div>
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
