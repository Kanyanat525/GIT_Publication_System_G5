<?php 
require_once 'db_connect.php';
// จำลองข้อมูลผู้ใช้
$users = [
  ["id"=>1, "username"=>"admin", "email"=>"admin1@test.com", "role"=>"Admin"],
  ["id"=>2, "username"=>"officer_user", "email"=>"officer1@test.com", "role"=>"Officer"],
  ["id"=>3, "username"=>"lecturer_user", "email"=>"lecturer1@test.com", "role"=>"Lecturer"],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการผู้ใช้</title>
<style>
/* thai */ 
@font-face {
  font-family: 'Noto Sans Thai';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');
  unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
}
/* latin-ext */
@font-face {
  font-family: 'Noto Sans Thai';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Noto Sans Thai';
  font-style: normal;
  font-weight: 400;
  font-stretch: 100%;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfPI2h.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* thai */
@font-face {
  font-family: 'Noto Sans Thai';
  font-style: normal;
  font-weight: 600;
  font-stretch: 100%;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');
  unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
}
/* latin-ext */
@font-face {
  font-family: 'Noto Sans Thai';
  font-style: normal;
  font-weight: 600;
  font-stretch: 100%;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Noto Sans Thai';
  font-style: normal;
  font-weight: 600;
  font-stretch: 100%;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfPI2h.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}

  body {
    margin: 0;
    font-family: 'Noto Sans Thai', sans-serif;
    background: linear-gradient(#e9f1ff,#cfe2ff,#c6dbff);
    min-height: 100vh;
  }
  header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
    background:#fff;
    box-shadow:0 2px 6px rgba(0,0,0,.1);
  }
  header span {
    font-size:22px;
    font-weight:bold;
    color:#1d4ed8;
    letter-spacing:1px;
  }
  .logout {
    background:#e53935;
    color:#fff;
    padding:8px 16px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
  }
  .container {
    width:90%;
    margin:30px auto;
    background:#fff;
    border-radius:16px;
    box-shadow:0 6px 16px rgba(0,0,0,.1);
    padding:20px;
  }
  .container h2 {
    text-align:center;
    margin-bottom:15px;
    color:#222;
  }
  .toolbar {
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-bottom:15px;
  }
  .btn {
    padding:6px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:13px;
  }
  .add, .request {
    background:#1d4ed8;
    color:#fff;
  }
  .edit {
    background:#757575;
    color:#fff;
  }
  .delete {
    background:#e53935;
    color:#fff;
  }
  table {
    width:100%;
    border-collapse:separate;
    border-spacing:6px;
  }
  thead {
    background:none;
  }
  thead th {
    background:#646c74;
    color:#fff;
    padding:14px 20px;
    text-align:center;
    border-radius:8px;
    font-weight:600;
  }
  tbody td {
    padding:12px 16px;
    text-align:left;
  }
  tr:nth-child(even){
    background:#f9f9f9;
  }
</style>
</head>
<body>

<header>
  <span>ระบบบริหารจัดการผลงานตีพิมพ์</span>
  <button class="logout">Log out</button>
</header>

<div class="container">
  <h2>Admin: จัดการบัญชีผู้ใช้</h2>
  <div class="toolbar">
    <button class="btn add">+ เพิ่มบัญชีผู้ใช้ใหม่</button>
    <button class="btn request">ตรวจสอบคำร้อง</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>UID</th>
        <th>Username</th>
        <th>Email</th>
        <th>สิทธิ์ผู้ใช้งาน</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($users as $u): ?>
      <tr>
        <td><?= $u["id"] ?></td>
        <td><?= htmlspecialchars($u["username"]) ?></td>
        <td><?= htmlspecialchars($u["email"]) ?></td>
        <td><?= htmlspecialchars($u["role"]) ?></td>
        <td>
          <button class="btn edit">แก้ไข</button>
          <button class="btn delete">ลบ</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>