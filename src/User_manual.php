<?php

require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>คู่มือการใช้งาน</title>

  <!-- (ตัวเลือก) เปิดใช้ Tailwind ให้สไตล์ header ตรงคลาสทำงาน -->
  <!-- <script src="https://cdn.tailwindcss.com"></script> -->

  <!-- ฟ้อนต์ Noto Sans Thai + สไตล์เดิมของหน้า -->
  <style>
    /* thai */
    @font-face{font-family:'Noto Sans Thai';font-style:normal;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');unicode-range:U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;}
    /* latin-ext */
    @font-face{font-family:'Noto Sans Thai';font-style:normal;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');unicode-range:U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;}
    /* latin */
    @font-face{font-family:'Noto Sans Thai';font-style:normal;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfPI2h.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}
    /* thai bold */
    @font-face{font-family:'Noto Sans Thai';font-style:normal;font-weight:600;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');unicode-range:U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;}
    /* latin-ext bold */
    @font-face{font-family:'Noto Sans Thai';font-style:normal;font-weight:600;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');unicode-range:U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;}
    /* latin bold */
    @font-face{font-family:'Noto Sans Thai';font-style:normal;font-weight:600;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfPI2h.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}

    body{font-family:'Noto Sans Thai', sans-serif;margin:0;min-height:100vh;background:linear-gradient(to bottom,#FFFFFF 19%,#B1D3FE 54%,#CAE2FF 92%);display:flex;flex-direction:column;align-items:center;}
    /* กล่องหลัก */
    .popup{background:#fff;border-radius:12px;padding:40px 30px;text-align:center;box-shadow:0 6px 20px rgba(0,0,0,.25);width:380px;position:relative;animation:fadeIn .4s ease;margin-top:24px;}
    .popup h2{margin-top:0;font-size:24px;color:#333;font-weight:600;}
    .popup p{color:#555;margin:12px 0 24px 0;font-size:15px;font-weight:400;}
    .popup button,.back-btn{background:linear-gradient(135deg,#4A90E2,#357ABD);border:none;padding:12px 36px;border-radius:12px;font-size:16px;cursor:pointer;font-weight:600;color:#fff;transition:all .3s ease;box-shadow:0 4px 8px rgba(0,0,0,.15);}
    .popup button:hover,.back-btn:hover{background:linear-gradient(135deg,#357ABD,#1F4F8B);transform:translateY(-2px);box-shadow:0 6px 12px rgba(0,0,0,.2);}

    /* โมดัล */
    .modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);justify-content:center;align-items:center;animation:fadeIn .3s ease;}
    .modal-content{background:#fff;padding:30px 40px;border-radius:16px;width:500px;max-width:90%;box-shadow:0 8px 20px rgba(0,0,0,.25);position:relative;animation:slideUp .3s ease;font-weight:400;}
    .close{position:absolute;right:18px;top:12px;font-size:28px;font-weight:bold;color:#666;cursor:pointer;transition:.2s;}
    .close:hover{color:#e74c3c;}
    .link{display:block;margin:12px 0;padding:12px 16px;background:#2b98ceff;color:#fff;font-weight:500;cursor:pointer;text-align:left;font-size:15px;border-radius:10px;transition:all .3s ease;box-shadow:0 3px 6px rgba(0,0,0,.1);}
    .link:hover{background:#0d3762ff;transform:translateY(-1px);box-shadow:0 5px 10px rgba(0,0,0,.15);text-decoration:none;}
    .guide-link{display:block;margin:10px 0;padding:12px 18px;background:#e74c3c;color:#fff;font-weight:500;text-align:center;font-size:15px;border-radius:10px;text-decoration:none;transition:all .3s ease;box-shadow:0 3px 6px rgba(0,0,0,.15);}
    .guide-link:hover{background:#c0392b;transform:translateY(-2px);box-shadow:0 5px 10px rgba(0,0,0,.2);text-decoration:none;}

    @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
    @keyframes slideUp{from{transform:translateY(30px);opacity:0;}to{transform:translateY(0);opacity:1;}}
  </style>
</head>

<!-- เพิ่มคลาสพื้นหลังตามที่ส่งมา (ไม่ต้องใช้ Tailwind ก็ไม่พัง) -->
<body class="min-h-screen bg-gradient-to-b from-white via-blue-200 to-blue-100">

  <!-- Header ใหม่ (ทำงานจริง: โลโก้กลับ index.php, ปุ่มย้อนมี fallback, logout ออกจริง) -->
  <header class="bg-white shadow" style="width:100%;position:sticky;top:0;z-index:50;">
    <div class="w-full px-6 py-4 flex items-center justify-between" style="display:flex;align-items:center;justify-content:space-between;padding:16px 24px;">
      <a href="index.php"
         class="text-2xl font-bold text-blue-700 tracking-wider hover:text-blue-800"
         style="font-weight:700;color:#1d4ed8;text-decoration:none;">
        ระบบบริหารจัดการผลงานตีพิมพ์
      </a>

      <div class="flex items-center gap-3" style="display:flex;gap:12px;">
        <!-- ปุ่มย้อนกลับ: ถ้ามี referrer → history.back(); ถ้าไม่มีกลับ index.php -->
        <a href="index.php"
           onclick="if (document.referrer) { history.back(); return false; }"
           class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-blue-700 hover:bg-blue-50"
           style="padding:6px 12px;border:1px solid #93c5fd;border-radius:8px;background:#fff;color:#1d4ed8;text-decoration:none;">
          ย้อนกลับ
        </a>

        <!-- ออกจากระบบ -->
        <a href="logout.php"
           class="px-4 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700"
           style="padding:6px 16px;border-radius:8px;background:#dc2626;color:#fff;text-decoration:none;">
          ออกจากระบบ
        </a>
      </div>
    </div>
  </header>

  <!-- เนื้อหาเดิมของหน้า -->
  <div class="popup" style="margin-top:16px;">
    <h2>คู่มือการใช้งาน</h2>
    <p>เอกสารคู่มือสำหรับการใช้งานระบบแต่ละประเภทผู้ใช้</p>
    <button id="openModal">เปิด</button>
  </div>

  <div id="guideModal" class="modal" aria-hidden="true">
    <div class="modal-content" id="mainContent" data-view="main" style="display:none;">
      <span class="close" id="closeModal" aria-label="ปิด">&times;</span>
      <h2>รายละเอียดคู่มือการใช้งาน</h2>
      <div>
        <span class="link" onclick="openSub('admin')">คู่มือสำหรับผู้ดูแลระบบ</span>
        <span class="link" onclick="openSub('teacher')">คู่มือสำหรับอาจารย์</span>
        <span class="link" onclick="openSub('student')">คู่มือสำหรับนักศึกษา/ผู้ใช้งานทั่วไป</span>
        <span class="link" onclick="openSub('staff')">คู่มือสำหรับเจ้าหน้าที่</span>
        <button class="back-btn" onclick="goBack()">ย้อนกลับ</button>
      </div>
    </div>

    <div class="modal-content" id="adminContent" data-view="admin" style="display:none;">
      <span class="close" onclick="closeModal()" aria-label="ปิด">&times;</span>
      <h2>คู่มือสำหรับผู้ดูแลระบบ</h2>
      <p>
        <a href="guides/admin.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/admin.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="goBack()">ย้อนกลับ</button>
    </div>

    <div class="modal-content" id="teacherContent" data-view="teacher" style="display:none;">
      <span class="close" onclick="closeModal()" aria-label="ปิด">&times;</span>
      <h2>คู่มือสำหรับอาจารย์</h2>
      <p>
        <a href="guides/teacher.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/teacher.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="goBack()">ย้อนกลับ</button>
    </div>

    <div class="modal-content" id="studentContent" data-view="student" style="display:none;">
      <span class="close" onclick="closeModal()" aria-label="ปิด">&times;</span>
      <h2>คู่มือสำหรับนักศึกษา</h2>
      <p>
        <a href="guides/student.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/student.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="goBack()">ย้อนกลับ</button>
    </div>

    <div class="modal-content" id="staffContent" data-view="staff" style="display:none;">
      <span class="close" onclick="closeModal()" aria-label="ปิด">&times;</span>
      <h2>คู่มือสำหรับเจ้าหน้าที่</h2>
      <p>
        <a href="guides/staff.pdf" target="_blank" class="guide-link">📖 เปิดดูคู่มือ (PDF)</a>
        <a href="guides/staff.pdf" download class="guide-link">⬇️ ดาวน์โหลดคู่มือ</a>
      </p>
      <button class="back-btn" onclick="goBack()">ย้อนกลับ</button>
    </div>
  </div>

  <script>
    // ---------- State ----------
    let currentView = 'main';
    const modal = document.getElementById('guideModal');
    const views = {
      main: document.getElementById('mainContent'),
      admin: document.getElementById('adminContent'),
      teacher: document.getElementById('teacherContent'),
      student: document.getElementById('studentContent'),
      staff: document.getElementById('staffContent'),
    };

    // ---------- Helpers ----------
    function showView(view){ Object.values(views).forEach(el=>el.style.display='none'); views[view].style.display='block'; currentView=view; }
    function ensureModalOpen(){ modal.style.display='flex'; modal.setAttribute('aria-hidden','false'); }
    function ensureModalClosed(){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); Object.values(views).forEach(el=>el.style.display='none'); }

    // ---------- Navigation ----------
    function openModal(){
      ensureModalOpen();
      showView('main');
      if(!(history.state && history.state.modal)){ history.pushState({modal:true,view:'main'},''); }
    }
    function closeModal(){
      ensureModalClosed();
      if(history.state && history.state.modal){ history.back(); }
    }
    function openSub(view){
      ensureModalOpen();
      showView(view);
      history.pushState({modal:true,view},'', '#'+view);
    }
    function goBack(){
      if(currentView==='main'){ closeModal(); } else { history.back(); }
    }

    // ---------- Wire UI ----------
    document.getElementById('openModal').addEventListener('click', openModal);
    document.getElementById('closeModal').addEventListener('click', closeModal);

    window.addEventListener('click', e => { if(e.target===modal) closeModal(); });
    window.addEventListener('keydown', e => { if(e.key==='Escape' && modal.style.display==='flex') closeModal(); });

    window.addEventListener('popstate', e => {
      if(!e.state || !e.state.modal){ ensureModalClosed(); return; }
      ensureModalOpen();
      const view = e.state.view ? e.state.view : 'main';
      showView(view);
    });

    // เปิดลิงก์ #hash ตรง ๆ
    window.addEventListener('load', () => {
      const hash = (location.hash||'').replace('#','');
      if(['admin','teacher','student','staff'].includes(hash)){ openModal(); openSub(hash); }
    });
  </script>

</body>
</html>