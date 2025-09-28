<?php
// จำลองข้อมูลผู้ใช้
$users = [
  ["id"=>1, "username"=>"admin", "email"=>"admin@example.com", "status"=>"เปิดใช้งาน", "role"=>"เจ้าหน้าที่"],
  ["id"=>2, "username"=>"student01", "email"=>"std01@example.com", "status"=>"เปิดใช้งาน", "role"=>"นักศึกษา"],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการผู้ใช้</title>
<style>
  body{margin:0;font-family:sans-serif;background:linear-gradient(#e9f1ff,#cfe2ff,#c6dbff);min-height:100vh}
  h1{text-align:center;padding:20px 0;color:#2443c1}
  table{border-collapse:collapse;width:90%;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1)}
  th,td{padding:12px 16px;border-bottom:1px solid #ddd;text-align:left}
  th{background:#2443c1;color:#fff}
  tr:last-child td{border-bottom:none}
  .btn{padding:6px 12px;border:none;border-radius:8px;cursor:pointer;font-size:13px}
  .edit{background:#ffb74d;color:#fff}
  .delete{background:#e57373;color:#fff}
  .reset{background:#64b5f6;color:#fff}
  .modal{position:fixed;top:0;left:0;width:100%;height:100%;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.4)}
  .modal-content{background:#fff;padding:20px;border-radius:12px;min-width:300px;max-width:400px}
  .close{float:right;cursor:pointer;font-weight:bold}
  .form-group{margin:10px 0}
  label{display:block;margin-bottom:6px}
  input,select{width:100%;padding:8px;border:1px solid #ccc;border-radius:6px}
</style>
</head>
<body>

<h1>รายชื่อผู้ใช้งาน</h1>
<table>
  <tr>
    <th>ลำดับ</th><th>ชื่อผู้ใช้</th><th>อีเมล</th><th>สถานะ</th><th>การดำเนินการ</th>
  </tr>
  <?php foreach($users as $u): ?>
  <tr>
    <td><?= $u["id"] ?></td>
    <td><?= htmlspecialchars($u["username"]) ?></td>
    <td><?= htmlspecialchars($u["email"]) ?></td>
    <td><?= htmlspecialchars($u["status"]) ?></td>
    <td>
      <button class="btn edit" onclick="openModal('edit',<?= $u['id'] ?>)">แก้ไขสิทธิ์</button>
      <button class="btn delete" onclick="openModal('delete',<?= $u['id'] ?>)">ลบ</button>
      <button class="btn reset" onclick="openModal('reset',<?= $u['id'] ?>)">รีเซ็ตรหัส</button>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<!-- Modal แก้ไขสิทธิ์ -->
<div id="modal-edit" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('edit')">&times;</span>
    <h3>แก้ไขสิทธิ์</h3>
    <div class="form-group">
      <label>สิทธิ์ผู้ใช้</label>
      <select>
        <option>นักศึกษา</option>
        <option>อาจารย์</option>
        <option>เจ้าหน้าที่</option>
      </select>
    </div>
    <button class="btn edit">บันทึก</button>
  </div>
</div>

<!-- Modal ลบ -->
<div id="modal-delete" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('delete')">&times;</span>
    <h3>ลบผู้ใช้</h3>
    <p>คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?</p>
    <button class="btn delete">ยืนยัน</button>
  </div>
</div>

<!-- Modal รีเซ็ตรหัส -->
<div id="modal-reset" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('reset')">&times;</span>
    <h3>รีเซ็ตรหัสผ่าน</h3>
    <div class="form-group">
      <label>รหัสผ่านใหม่</label>
      <input type="password">
    </div>
    <div class="form-group">
      <label>ยืนยันรหัสผ่าน</label>
      <input type="password">
    </div>
    <button class="btn reset">บันทึก</button>
  </div>
</div>

<script>
function openModal(type,id){ document.getElementById('modal-'+type).style.display='flex'; }
function closeModal(type){ document.getElementById('modal-'+type).style.display='none'; }
</script>
</body>
</html>