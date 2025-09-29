<?php
// user_new.php
require_once 'db_connect.php';

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function back_to_self(){ header('Location: '. strtok($_SERVER['REQUEST_URI'],'?')); exit; }

$errors=[]; $notices=[];

/* ===================== Actions ===================== */
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';

  if($action==='add_user'){
    $username=trim($_POST['username']??'');
    $email=trim($_POST['email']??'');
    $role=trim($_POST['role']??'Lecturer');
    $pwd=$_POST['password']??'';
    if($username===''||$email===''||$role===''){ $errors[]='กรอกข้อมูลให้ครบถ้วน'; back_to_self(); }
    $hash = $pwd!=='' ? password_hash($pwd, PASSWORD_DEFAULT) : null;

    $stmt=$conn->prepare("INSERT INTO users(username,email,role,password_hash) VALUES(?,?,?,?)");
    if(!$stmt){ $errors[]=$conn->error; back_to_self(); }
    $stmt->bind_param('ssss',$username,$email,$role,$hash);
    if($stmt->execute()) $notices[]='เพิ่มผู้ใช้เรียบร้อย'; else $errors[]=$stmt->error;
    $stmt->close(); back_to_self();
  }

  if($action==='update_user'){
    $id=intval($_POST['id']??0);
    $username=trim($_POST['username']??'');
    $email=trim($_POST['email']??'');
    $role=trim($_POST['role']??'');
    if($id<=0||$username===''||$email===''||$role===''){ $errors[]='ข้อมูลไม่ครบถ้วน'; back_to_self(); }

    $stmt=$conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
    if(!$stmt){ $errors[]=$conn->error; back_to_self(); }
    $stmt->bind_param('sssi',$username,$email,$role,$id);
    if($stmt->execute()) $notices[]='บันทึกการแก้ไขแล้ว'; else $errors[]=$stmt->error;
    $stmt->close(); back_to_self();
  }

  if($action==='delete_user'){
    $id=intval($_POST['id']??0);
    if($id<=0){ $errors[]='รหัสผู้ใช้ไม่ถูกต้อง'; back_to_self(); }
    $stmt=$conn->prepare("DELETE FROM users WHERE id=?");
    if(!$stmt){ $errors[]=$conn->error; back_to_self(); }
    $stmt->bind_param('i',$id);
    if($stmt->execute()) $notices[]='ลบผู้ใช้เรียบร้อย'; else $errors[]=$stmt->error;
    $stmt->close(); back_to_self();
  }

  if($action==='reset_password'){
    $id=intval($_POST['id']??0);
    if($id<=0){ $errors[]='รหัสผู้ใช้ไม่ถูกต้อง'; back_to_self(); }
    $temp = bin2hex(random_bytes(5)); // 10 ตัวอักษร
    $hash = password_hash($temp, PASSWORD_DEFAULT);
    $stmt=$conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
    if(!$stmt){ $errors[]=$conn->error; back_to_self(); }
    $stmt->bind_param('si',$hash,$id);
    if($stmt->execute()) $notices[]='รีเซ็ตรหัสผ่านสำเร็จ • รหัสชั่วคราว: <code>'.esc($temp).'</code>';
    else $errors[]=$stmt->error;
    $stmt->close(); back_to_self();
  }
}

/* ===================== Query users ===================== */
$users=[];
$res=$conn->query("SELECT id,username,email,role FROM users ORDER BY id");
if($res){ while($r=$res->fetch_assoc()) $users[]=$r; $res->free(); }
else { $errors[]="คิวรีล้มเหลว/ไม่พบตาราง users: ".$conn->error; }
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>จัดการบัญชีผู้ใช้</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{
  --bg1:#eaf1ff; --bg2:#cfe0ff; --card:#ffffff; --muted:#94a3b8; --text:#0f172a;
  --head:#4b5563; --headText:#e5e7eb; --line:#e5e7eb;
  --blue:#2563eb; --blue-700:#1d4ed8; --gray:#6b7280; --red:#e11d48; --orange:#f59e0b;
}
*{box-sizing:border-box}
body{
  margin:0; font-family: ui-sans-serif,system-ui,Segoe UI,Roboto,Arial; color:var(--text);
  background: linear-gradient(180deg,var(--bg1),var(--bg2)) fixed;
}

/* ===== Topbar ===== */
.topbar{
  background:#ffffffcc; backdrop-filter: blur(6px);
  border-bottom:1px solid #dbe3ff; padding:14px 24px;
  display:flex; align-items:center; justify-content:space-between;
}
.brand{ font-weight:700; color:#1e3a8a; }
.logout{ background:#ef4444; color:#fff; border:none; padding:10px 16px; border-radius:10px; cursor:pointer; }

/* ===== Layout ===== */
.container{ max-width:1200px; margin:28px auto; padding:0 20px; }
.card{ background:var(--card); border-radius:18px; box-shadow:0 10px 30px rgba(30,64,175,.12); border:1px solid #e6ecff; padding:24px; }
.header-line{ display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; position:relative; }
.h-title{ font-size:28px; font-weight:800; margin:0 auto; text-align:center; }
.header-actions{ display:flex; gap:10px; position:absolute; right:0; }
.btn{ appearance:none; border:none; cursor:pointer; padding:10px 14px; border-radius:12px; font-weight:600; font-size:14px; }
.btn.blue{ background:var(--blue); color:#fff; } .btn.blue:hover{ background:var(--blue-700); }
.btn.gray{ background:var(--gray); color:#fff; } .btn.red{ background:var(--red); color:#fff; } .btn.orange{ background:var(--orange); color:#111827; }

/* ===== Table ===== */
.table{ width:100%; border-collapse:separate; border-spacing:0; }
.table tbody td{ padding:16px; border-bottom:1px solid var(--line); font-size:15px; }
.table tbody tr:nth-child(odd){ background:#f8fbff; }
.cell-actions{ display:flex; gap:10px; flex-wrap:wrap; }
.tag{ display:inline-block; background:#eef2ff; border:1px dashed #c7d2fe; color:#312e81; padding:5px 10px; border-radius:999px; font-size:12px; }

/* ===== Pill Header (หัวตารางแบบกล่องมน) ===== */
.pill-head th{ padding:0; background:transparent; border:0; }
.pill-head th .pill{
  background:#5d6770;       /* สีเทาเข้ม */
  color:#fff;                /* ตัวหนังสือสีขาว */
  font-weight:800;
  text-align:center;
  padding:12px 16px;
  border-radius:12px;        /* มนตามภาพ */
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.06);
}
.pill-head th:not(:last-child) .pill{ margin-right:12px; }

/* กำหนดความกว้างคอลัมน์ */
.col-uid{ width:90px; }
.col-role{ width:160px; }
.col-actions{ width:300px; }

/* ===== Notice & Error ===== */
.notice{ margin:12px 0; padding:12px 14px; border-radius:12px; background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; }
.error{ margin:12px 0; padding:12px 14px; border-radius:12px; background:#fef2f2; border:1px solid #fecaca; color:#7f1d1d; }

/* ===== Modal ===== */
.modal{ position:fixed; inset:0; background:rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; padding:16px; }
.modal.open{ display:flex; }
.modal-card{ width:100%; max-width:560px; background:#fff; border-radius:16px; padding:18px; border:1px solid #e5e7eb; }
.modal-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.modal-title{ font-weight:800; font-size:18px; }
.input,.select{ width:100%; padding:11px 12px; border:1px solid #cbd5e1; border-radius:12px; font-size:14px; }
.form-grid{ display:grid; gap:10px; }
.form-actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:8px; }
code{ background:#f1f5f9; padding:2px 6px; border-radius:6px; }

@media (max-width:640px){
  .header-actions{ position:static; margin-left:auto; }
  .h-title{ font-size:22px; }
}
</style>
</head>
<body>

<!-- ========= Topbar ========= -->
<div class="topbar">
  <div class="brand">ระบบบริหารจัดการผลงานตีพิมพ์</div>
  <form action="logout.php" method="post">
    <button class="logout" type="submit">ออกจากระบบ</button>
  </form>
</div>

<div class="container">
  <div class="card">
    <div class="header-line">
      <h2 class="h-title">Admin: จัดการบัญชีผู้ใช้</h2>
      <div class="header-actions">
        <button class="btn blue" id="btnAdd">+ เพิ่มบัญชีผู้ใช้ใหม่</button>
        <a href="role_requests.php" class="btn blue" style="text-decoration:none;">ตรวจสอบคำร้อง</a>
      </div>
    </div>

    <?php foreach($notices as $n): ?><div class="notice"><?= $n ?></div><?php endforeach; ?>
    <?php foreach($errors as $e): ?><div class="error"><?= esc($e) ?></div><?php endforeach; ?>

    <table class="table">
      <thead class="pill-head">
        <tr>
          <th class="col-uid"><div class="pill">UID</div></th>
          <th><div class="pill">Username</div></th>
          <th><div class="pill">Email</div></th>
          <th class="col-role"><div class="pill">สิทธิ์ผู้ใช้งาน</div></th>
          <th class="col-actions"><div class="pill">จัดการ</div></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!count($users)): ?>
          <tr><td colspan="5" style="text-align:center;color:#64748b;">ไม่มีข้อมูลผู้ใช้</td></tr>
        <?php endif; ?>
        <?php foreach($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= esc($u['username']) ?></td>
            <td><?= esc($u['email']) ?></td>
            <td><span class="tag"><?= esc($u['role']) ?></span></td>
            <td>
              <div class="cell-actions">
                <button class="btn gray" data-edit
                        data-id="<?= (int)$u['id'] ?>"
                        data-username="<?= esc($u['username']) ?>"
                        data-email="<?= esc($u['email']) ?>"
                        data-role="<?= esc($u['role']) ?>">แก้ไข</button>

                <form method="post" onsubmit="return confirm('ยืนยันลบผู้ใช้นี้?')" style="display:inline;">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <button class="btn red" type="submit">ลบ</button>
                </form>

                <form method="post" onsubmit="return confirm('รีเซ็ตรหัสผ่านและสร้างรหัสชั่วคราวใหม่?')" style="display:inline;">
                  <input type="hidden" name="action" value="reset_password">
                  <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <button class="btn orange" type="submit">รีเซ็ตรหัสผ่าน</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>
</div>

<!-- ===== Modal: Add ===== -->
<div class="modal" id="modalAdd">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">เพิ่มบัญชีผู้ใช้ใหม่</div>
      <button class="btn gray" data-close type="button">×</button>
    </div>
    <form method="post">
      <input type="hidden" name="action" value="add_user">
      <div class="form-grid">
        <input class="input" name="username" placeholder="Username" required>
        <input class="input" type="email" name="email" placeholder="Email" required>
        <select class="select" name="role" required>
          <option value="Admin">Admin</option>
          <option value="Officer">Officer</option>
          <option value="Lecturer">Lecturer</option>
        </select>
        <input class="input" name="password" placeholder="รหัสผ่านเริ่มต้น (ไม่บังคับ)">
      </div>
      <div class="form-actions">
        <button type="button" class="btn gray" data-close>ยกเลิก</button>
        <button class="btn blue" type="submit">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== Modal: Edit ===== -->
<div class="modal" id="modalEdit">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">แก้ไขบัญชีผู้ใช้</div>
      <button class="btn gray" data-close type="button">×</button>
    </div>
    <form method="post" id="formEdit">
      <input type="hidden" name="action" value="update_user">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-grid">
        <input class="input" name="username" id="edit_username" required>
        <input class="input" type="email" name="email" id="edit_email" required>
        <select class="select" name="role" id="edit_role" required>
          <option value="Admin">Admin</option>
          <option value="Officer">Officer</option>
          <option value="Lecturer">Lecturer</option>
        </select>
      </div>
      <div class="form-actions">
        <button type="button" class="btn gray" data-close>ยกเลิก</button>
        <button class="btn blue" type="submit">บันทึกการแก้ไข</button>
      </div>
    </form>
  </div>
</div>

<script>
const $ = (s, el=document)=> el.querySelector(s);
const $$ = (s, el=document)=> Array.from(el.querySelectorAll(s));

function openModal(id){ $(id).classList.add('open'); }
function closeModal(btn){ btn.closest('.modal').classList.remove('open'); }

$('#btnAdd')?.addEventListener('click', ()=> openModal('#modalAdd'));
$$('[data-close]').forEach(b=> b.addEventListener('click', e=> closeModal(e.target)));

$$('[data-edit]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    $('#edit_id').value = btn.dataset.id;
    $('#edit_username').value = btn.dataset.username || '';
    $('#edit_email').value = btn.dataset.email || '';
    $('#edit_role').value = btn.dataset.role || 'Lecturer';
    openModal('#modalEdit');
  });
});
</script>
</body>
</html>
