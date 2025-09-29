<?php
include('db_connect.php');

/* ---------- บันทึก (อัปเดตผลงาน + คีย์เวิร์ด) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pub'])) {
    $pub_ID       = intval($_POST['pub_ID']);
    $title        = mysqli_real_escape_string($conn, $_POST['title']);
    $publish_year = mysqli_real_escape_string($conn, $_POST['publish_year']); // 2024
    $type_ID      = intval($_POST['type_ID']);
    $file         = mysqli_real_escape_string($conn, $_POST['file']);
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    // อัปเดตรายละเอียดผลงาน
    $sql = "UPDATE publication 
            SET title='$title',
                publish_date='{$publish_year}-01-01',
                type_ID=$type_ID,
                file='$file',
                status='$status'
            WHERE pub_ID=$pub_ID";
    mysqli_query($conn, $sql);

    // ลบ keyword เดิม -> ใส่ใหม่
    mysqli_query($conn, "DELETE FROM pub_keyword WHERE pub_ID=$pub_ID");

    if (!empty($_POST['keywords'])) {
        foreach ($_POST['keywords'] as $kwName) {
            $kwName = trim($kwName);
            if ($kwName === '') continue;

            // ถ้า keyword ยังไม่มีในตาราง keyword -> insert ใหม่
            $res = mysqli_query($conn, "SELECT key_ID FROM keyword WHERE key_name='".mysqli_real_escape_string($conn,$kwName)."' LIMIT 1");
            if ($res && mysqli_num_rows($res) > 0) {
                $row    = mysqli_fetch_assoc($res);
                $key_ID = intval($row['key_ID']);
            } else {
                mysqli_query($conn, "INSERT INTO keyword (key_name) VALUES ('".mysqli_real_escape_string($conn,$kwName)."')");
                $key_ID = mysqli_insert_id($conn);
            }
            // ผูก pub ↔ keyword
            mysqli_query($conn, "INSERT INTO pub_keyword (pub_ID, key_ID) VALUES ($pub_ID, $key_ID)");
        }
    }

    echo "<script>alert('บันทึกการแก้ไขเรียบร้อยแล้ว'); location.href='".$_SERVER['PHP_SELF']."';</script>";
    exit;
}

/* ---------- ลบผลงาน ---------- */
if (isset($_GET['delete'])) {
    $pub_ID = intval($_GET['delete']);
    if (mysqli_query($conn, "DELETE FROM publication WHERE pub_ID=$pub_ID")) {
        echo "<script>alert('ลบผลงานเรียบร้อยแล้ว'); location.href='".$_SERVER['PHP_SELF']."';</script>";
        exit;
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบ');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ระบบจัดการผลงาน</title>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  body {font-family:'Kanit',sans-serif; margin:0;
    background:linear-gradient(to bottom,#fff 0%,#e0f2fe 70%,#bfdbfe 100%); background-attachment:fixed;}
  .navbar {background:#fff; padding:15px 40px; display:flex; justify-content:space-between; border-bottom:1px solid #e5e7eb;}
  .system-title {font-size:24px; font-weight:700; color:#1d4ed8; letter-spacing:2px;}
  .categories {display:flex; justify-content:center; gap:20px; margin:30px; flex-wrap:wrap;}
    .category-card {background:#fff; padding: 40px;px; border-radius:16px; width:160px; text-align:center; box-shadow:0 3px 8px rgba(0,0,0,.1); cursor:pointer;}
  .category-card:hover {transform: translateY(-3px); box-shadow:0 6px 14px rgba(0,0,0,.15);}
  .file-section {display:none; max-width:1000px; margin:0 auto 40px; background:#fff; padding:20px; border-radius:12px;}
  .file-section.active {display:block;}
  table {width:100%; border-collapse:collapse; margin-top:15px; table-layout:fixed;}
  th,td {padding:10px; border:1px solid #ddd; text-align:center; word-wrap:break-word;}
  th:nth-child(1),td:nth-child(1){width:6%} th:nth-child(2),td:nth-child(2){width:40%}
  th:nth-child(3),td:nth-child(3){width:18%} th:nth-child(4),td:nth-child(4){width:12%}
  th:nth-child(5),td:nth-child(5){width:24%}
  .action-btn{display:inline-block;padding:6px 14px;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:500;text-decoration:none;margin:3px;transition:.2s}
  .edit-btn{background:#facc15;color:#000}.edit-btn:hover{background:#eab308}
  .delete-btn{background:#ef4444;color:#fff}.delete-btn:hover{background:#dc2626}

  /* Modal */
  .modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center}
  .modal-content{background:#fff;padding:20px;border-radius:12px;width:600px;max-height:90vh;overflow-y:auto}
  .close-btn{float:right;cursor:pointer;font-size:20px}
  label{display:block;margin-top:10px;font-weight:500}
  input,select{width:100%;padding:8px;margin-top:5px;border:1px solid #ccc;border-radius:6px}
  .btn-primary{background:#2563eb;color:#fff;width:100%;padding:10px;border:none;border-radius:8px;margin-top:15px}

  /* Keyword chips */
  .kw-row{display:flex;gap:8px;align-items:center;margin-top:6px}
  .kw-input{flex:1}
  .kw-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
  .chip{display:inline-flex;align-items:center;gap:8px;background:#eef2ff;color:#1e40af;border:1px solid #c7d2fe;border-radius:9999px;padding:6px 10px}
  .chip button{background:transparent;border:none;cursor:pointer;font-weight:700;color:#1e40af}
    .logout-btn {
    background:#ef4444;
    color:#fff;
    padding:8px 16px;
    border-radius:6px;
    text-decoration:none;
    font-weight:500;
    transition:background .2s;
    }
    .logout-btn:hover {background:#dc2626;}

</style>
</head>
<body>

<div class="navbar">
  <span class="system-title">ระบบบริหารจัดการผลงานตีพิมพ์</span>
    <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
</div>

<!-- การ์ดหมวดหมู่ -->
<div class="categories">
  <div class="category-card" onclick="showFiles('textbook')">📘 <div>ตำรา</div></div>
  <div class="category-card" onclick="showFiles('journal')">📑 <div>วารสาร</div></div>
  <div class="category-card" onclick="showFiles('article')">📝 <div>บทความ</div></div>
  <div class="category-card" onclick="showFiles('other')">📂 <div>อื่น ๆ</div></div>
</div>

<?php
// ฟังก์ชันแสดงตารางไฟล์
function renderTable($conn, $type_id, $html_id, $title) {
    $sql = "SELECT p.*, t.type_name 
            FROM publication p
            JOIN publicationtype t ON p.type_ID = t.type_ID
            WHERE p.type_ID=$type_id";
    $res = mysqli_query($conn, $sql);

    echo "<div id='$html_id' class='file-section'>";
    echo "<h3>ไฟล์ในหมวด: $title</h3>";
    echo "<table><thead><tr>
            <th>ลำดับ</th><th>ชื่อผลงาน</th><th>ไฟล์</th><th>สถานะ</th><th>จัดการ</th>
          </tr></thead><tbody>";

    $i=1;
    while($row=mysqli_fetch_assoc($res)){
        // ดึง keyword
        $ids = []; $names = [];
        $kwres = mysqli_query($conn,"SELECT k.key_ID,k.key_name
                                     FROM pub_keyword pk
                                     JOIN keyword k ON pk.key_ID=k.key_ID
                                     WHERE pk.pub_ID=".$row['pub_ID']);
        while($k=mysqli_fetch_assoc($kwres)){ $ids[] = (int)$k['key_ID']; $names[] = $k['key_name']; }
        $row['keyword_ids']   = $ids;
        $row['keyword_names'] = $names;

        echo "<tr>
          <td>".$i++."</td>
          <td>".htmlspecialchars($row['title'])."</td>
          <td><a href='".htmlspecialchars($row['file'])."' target='_blank'>📥 ดาวน์โหลด</a></td>
          <td>".htmlspecialchars($row['status'])."</td>
          <td>
            <button class='action-btn edit-btn' onclick='openModal(".json_encode($row, JSON_UNESCAPED_UNICODE).")'>แก้ไข</button>
            <a class='action-btn delete-btn' href='?delete=".$row['pub_ID']."' onclick='return confirm(\"ยืนยันการลบผลงานนี้?\")'>ลบ</a>
          </td>
        </tr>";
    }
    echo "</tbody></table></div>";
}

renderTable($conn, 1, 'textbook', 'ตำรา');
renderTable($conn, 2, 'journal',  'วารสาร');
renderTable($conn, 3, 'article',  'บทความ');
renderTable($conn, 4, 'other',    'อื่น ๆ');
?>

<!-- Modal -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h3>รายละเอียดผลงาน</h3>

    <form method="POST" action="" id="editForm">
      <input type="hidden" name="update_pub" value="1">
      <input type="hidden" name="pub_ID" id="pub_ID">

      <label>ชื่อผลงาน</label>
      <input type="text" name="title" id="title">

      <label>ปีพิมพ์</label>
      <input type="text" name="publish_year" id="publish_year" placeholder="ตัวอย่าง 2024">

      <label>ประเภท</label>
      <select name="type_ID" id="type_ID">
        <option value="1">ตำรา</option>
        <option value="2">วารสาร</option>
        <option value="3">บทความ</option>
        <option value="4">อื่น ๆ</option>
      </select>

      <label>ไฟล์ (พาธไฟล์/ชื่อไฟล์)</label>
      <input type="text" name="file" id="file" placeholder="เช่น file_1.pdf">

      <hr>
      <h4>สำหรับเจ้าหน้าที่</h4>

      <label>Keyword</label>
      <div class="kw-row">
        <input type="text" class="kw-input" id="kw_input" placeholder="พิมพ์คีย์เวิร์ด แล้วกด Enter หรือปุ่มเพิ่ม">
        <button type="button" class="action-btn edit-btn" onclick="addKeyword()">+ เพิ่ม</button>
      </div>
      <div id="kw_chips" class="kw-chips"></div>

      <label>สถานะ</label>
      <select id="status" name="status">
        <option value="Pending">Pending</option>
        <option value="Approved">Approved</option>
        <option value="Modify">Modify</option>
      </select>

      <button type="submit" class="btn-primary">บันทึก</button>
    </form>
  </div>
</div>

<script>
function showFiles(id){
  document.querySelectorAll('.file-section').forEach(sec=>sec.classList.remove('active'));
  document.getElementById(id).classList.add('active');
}

function openModal(data){
  document.getElementById('pub_ID').value      = data.pub_ID;
  document.getElementById('title').value       = data.title || '';
  document.getElementById('publish_year').value= (data.publish_date||'').substring(0,4);
  document.getElementById('type_ID').value     = data.type_ID;
  document.getElementById('file').value        = data.file || '';
  document.getElementById('status').value      = data.status || 'Pending';

  // รีเซ็ต keyword chips
  document.getElementById('kw_chips').innerHTML = '';
  if (Array.isArray(data.keyword_names)) {
    data.keyword_names.forEach(name => addKeyword(name));
  }

  document.getElementById('editModal').style.display='flex';
}

function closeModal(){ document.getElementById('editModal').style.display='none'; }

function addKeyword(value){
  const input = document.getElementById('kw_input');
  let val = (value !== undefined ? value : input.value).trim();
  if (!val) return;

  // กันซ้ำ
  const chips = Array.from(document.querySelectorAll('#kw_chips .chip span'))
                     .map(s=>s.textContent.toLowerCase());
  if (chips.includes(val.toLowerCase())) { input.value=''; return; }

  const chip = document.createElement('div');
  chip.className = 'chip';
  chip.innerHTML = `<span></span><button type="button" title="ลบ">×</button>
                    <input type="hidden" name="keywords[]" value="">`;
  chip.querySelector('span').textContent = val;
  chip.querySelector('input').value = val;
  chip.querySelector('button').onclick = () => chip.remove();
  document.getElementById('kw_chips').appendChild(chip);

  input.value = '';
}

// Enter = เพิ่ม keyword
document.getElementById('kw_input').addEventListener('keydown', e=>{
  if (e.key === 'Enter') { e.preventDefault(); addKeyword(); }
});
</script>
</body>
</html>
