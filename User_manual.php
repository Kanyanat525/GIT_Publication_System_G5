<?php
// guide.php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>คู่มือการใช้งาน</title>

  <!-- ฟ้อนต์ Noto Sans Thai (WOFF2 ตามที่ให้มา) -->
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
    /* thai bold */
    @font-face {
      font-family: 'Noto Sans Thai';
      font-style: normal;
      font-weight: 600;
      font-stretch: 100%;
      font-display: swap;
      src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');
      unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
    }
    /* latin-ext bold */
    @font-face {
      font-family: 'Noto Sans Thai';
      font-style: normal;
      font-weight: 600;
      font-stretch: 100%;
      font-display: swap;
      src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');
      unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
    }
    /* latin bold */
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
      font-family: 'Noto Sans Thai', sans-serif;
      margin: 0;
      height: 100vh;
      background: linear-gradient(to bottom, #FFFFFF 19%, #B1D3FE 54%, #CAE2FF 92%);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .popup {
      background: #fff;
      border-radius: 12px;
      padding: 40px 30px;
      text-align: center;
      box-shadow: 0px 6px 20px rgba(0,0,0,0.25);
      width: 380px;
      position: relative;
      animation: fadeIn 0.4s ease;
    }

    .popup h2 {
      margin-top: 0;
      font-size: 24px;
      color: #333;
      font-weight: 600;
    }

    .popup p {
      color: #555;
      margin: 12px 0 24px 0;
      font-size: 15px;
      font-weight: 400;
    }

    .popup button, .back-btn {
      background: linear-gradient(135deg, #4A90E2, #357ABD);
      border: none;
      padding: 12px 36px;
      border-radius: 12px;
      font-size: 16px;
      cursor: pointer;
      font-weight: 600;
      color: #fff;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .popup button:hover, .back-btn:hover {
      background: linear-gradient(135deg, #357ABD, #1F4F8B);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease;
    }

    .modal-content {
      background: #fff;
      padding: 30px 40px;
      border-radius: 16px;
      width: 500px;
      max-width: 90%;
      box-shadow: 0 8px 20px rgba(0,0,0,0.25);
      position: relative;
      animation: slideUp 0.3s ease;
      font-weight: 400;
    }

    .close {
      position: absolute;
      right: 18px;
      top: 12px;
      font-size: 28px;
      font-weight: bold;
      color: #666;
      cursor: pointer;
      transition: 0.2s;
    }

    .close:hover {
      color: #e74c3c;
    }

    .link {
      display: block;
      margin: 12px 0;
      padding: 12px 16px;
      background: #2b98ceff; 
      color: #fff;
      font-weight: 500;
      cursor: pointer;
      text-align: left;
      font-size: 15px;
      border-radius: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    .link:hover {
      background: #0d3762ff; 
      transform: translateY(-1px);
      box-shadow: 0 5px 10px rgba(0,0,0,0.15);
      text-decoration: none;
    }

    .guide-link {
      display: block;
      margin: 10px 0;
      padding: 12px 18px;
      background: #e74c3c; 
      color: #fff;
      font-weight: 500;
      text-align: center;
      font-size: 15px;
      border-radius: 10px;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    }

    .guide-link:hover {
      background: #c0392b;
      transform: translateY(-2px);
      box-shadow: 0 5px 10px rgba(0,0,0,0.2);
      text-decoration: none;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
  </style>
</head>
<body>

  <div class="popup">
    <h2>คู่มือการใช้งาน</h2>
    <p>เอกสารคู่มือสำหรับการใช้งานระบบแต่ละประเภทผู้ใช้</p>
    <button id="openModal">เปิด</button>
  </div>

  <div id="guideModal" class="modal">
    <div class="modal-content" id="mainContent">
      <span class="close" id="closeModal">&times;</span>
      <h2>รายละเอียดคู่มือการใช้งาน</h2>
      <div>
        <span class="link" onclick="openSubModal('admin')">คู่มือสำหรับผู้ดูแลระบบ</span>
        <span class="link" onclick="openSubModal('teacher')">คู่มือสำหรับอาจารย์</span>
        <span class="link" onclick="openSubModal('student')">คู่มือสำหรับนักศึกษา/ผู้ใช้งานทั่วไป</span>
        <span class="link" onclick="openSubModal('staff')">คู่มือสำหรับเจ้าหน้าที่</span>
      </div>
    </div>

    <!-- ✅ ผู้ดูแลระบบ -->
    <div class="modal-content" id="adminContent" style="display:none;">
      <span class="close" onclick="backToMain()">&times;</span>
      <h2>คู่มือสำหรับผู้ดูแลระบบ</h2>
      <p>
        <a href="guides/admin.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/admin.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="backToMain()">ย้อนกลับ</button>
    </div>

    <!-- ✅ อาจารย์ -->
    <div class="modal-content" id="teacherContent" style="display:none;">
      <span class="close" onclick="backToMain()">&times;</span>
      <h2>คู่มือสำหรับอาจารย์</h2>
      <p>
        <a href="guides/teacher.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/teacher.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="backToMain()">ย้อนกลับ</button>
    </div>

    <!-- ✅ นักศึกษา -->
    <div class="modal-content" id="studentContent" style="display:none;">
      <span class="close" onclick="backToMain()">&times;</span>
      <h2>คู่มือสำหรับนักศึกษา</h2>
      <p>
        <a href="guides/student.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/student.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="backToMain()">ย้อนกลับ</button>
    </div>

    <!-- ✅ เจ้าหน้าที่ -->
    <div class="modal-content" id="staffContent" style="display:none;">
      <span class="close" onclick="backToMain()">&times;</span>
      <h2>คู่มือสำหรับเจ้าหน้าที่</h2>
      <p>
        <a href="guides/staff.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/staff.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="backToMain()">ย้อนกลับ</button>
    </div>
  </div>

  <script>
    document.getElementById("openModal").onclick = function() {
      document.getElementById("guideModal").style.display = "flex";
      showMain();
    };

    document.getElementById("closeModal").onclick = function() {
      document.getElementById("guideModal").style.display = "none";
    };

    window.onclick = function(event) {
      if (event.target == document.getElementById("guideModal")) {
        document.getElementById("guideModal").style.display = "none";
      }
    };

    function openSubModal(type) {
      document.getElementById("mainContent").style.display = "none";
      document.getElementById(type + "Content").style.display = "block";
    }

    function backToMain() {
      document.querySelectorAll(".modal-content").forEach(el => el.style.display = "none");
      document.getElementById("mainContent").style.display = "block";
    }

    function showMain() {
      document.querySelectorAll(".modal-content").forEach(el => el.style.display = "none");
      document.getElementById("mainContent").style.display = "block";
    }
  </script>

</body>
</html>
