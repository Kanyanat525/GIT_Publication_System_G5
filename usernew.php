<?php
// เชื่อมต่อฐานข้อมูล
require_once "config.php"; // ไฟล์นี้คือไฟล์ที่มี mysqli_connect()

// ดึงข้อมูลผู้ใช้ + สิทธิ์ผู้ใช้
$sql = "SELECT u.UID, u.username, u.email, ut.type_name 
        FROM user u
        JOIN user_type ut ON u.user_type_ID = ut.user_type_ID";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ระบบบริหารจัดการผลงานตีพิมพ์</title>
<style>
  body{margin:0;font-family:sans-serif;background:#e9f1ff;}
  header{display:flex;justify-content:space-between;align-items:center;padding:16px;background:#fff;
         box-shadow:0 2px 6px rgba(0,0,0,0.1);}
  header h1{margin:0;font-size:20px;}
  header .logout{background:#dc2626;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;}
  main{padding:20px;display:flex;flex-direction:column;align-items:center;}
  .container{background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.1);width:80%;}
  .container h2{text-align:center;margin-top:0;}
  table{border-collapse:collapse;width:100%;margin-top:20px;}
  th,td{padding:12px 16px;text-align:left;border-bottom:1px solid #ddd;}
  th{background:#555;color:#fff;}
  .btn{padding:6px 14px;border:none;border-radius:6px;cursor:pointer;font-size:14px;margin:2px;}
  .btn-add{background:#2563eb;color:#fff;float:right;}
  .btn-edit{background:#6b7280;color:#fff;}
  .btn-delete{background:#dc2626;color:#fff;}
</style>
</head>
<body>

<header>
  <h1>ระบบบริหารจัดการผลงานตีพิมพ์</h1>
  <button class="logout">Log out</button>
</header>

<main>
  <div class="container">
    <h2>Admin: จัดการบัญชีผู้ใช้</h2>
    <button class="btn btn-add">+ เพิ่มบัญชีผู้ใช้ใหม่</button>
    <table>
      <tr>
        <th>UID</th>
        <th>Username</th>
        <th>Email</th>
        <th>สิทธิ์ผู้ใช้งาน</th>
        <th>จัดการ</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?= $row["UID"] ?></td>
        <td><?= htmlspecialchars($row["username"]) ?></td>
        <td><?= htmlspecialchars($row["email"]) ?></td>
        <td><?= htmlspecialchars($row["type_name"]) ?></td>
        <td>
          <button class="btn btn-edit">แก้ไข</button>
          <?php if($row["type_name"] != "Admin"): ?>
            <button class="btn btn-delete">ลบ</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</main>

</body>
</html>
