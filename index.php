<?php
// ไฟล์นี้เชื่อมต่อกับฐานข้อมูลผ่าน db_connect.php
// (ใช้สำหรับ Local Server ที่ตั้งค่า DB_PASSWORD เป็นค่าว่าง)
include('db_connect.php');

// ดึงปีปัจจุบันสำหรับการค้นหาเริ่มต้น
$current_year = date("Y") + 543; // ปีพ.ศ.

// ตรวจสอบการค้นหาและ Filter
$search_query = ""; // ข้อความค้นหาหลัก
$search_year = "";  // ปี พ.ศ. ที่เลือก
$search_type = "";  // ประเภทผลงานที่เลือก (ชื่อภาษาอังกฤษจาก DB)

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['search'])) {
        $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    }
    if (isset($_GET['year']) && $_GET['year'] !== 'all') {
        $search_year = mysqli_real_escape_string($conn, $_GET['year']);
    }
    // ค่า search_type ถูกส่งมาเป็นชื่อภาษาไทยจาก UI แต่จะถูกแปลงเป็นชื่ออังกฤษใน Query
    if (isset($_GET['type']) && $_GET['type'] !== 'all') {
        // ใน PHP เราเก็บชื่อประเภทภาษาไทยที่ผู้ใช้คลิกไว้ (เช่น 'ตำรา') 
        // แต่ใน Query เราจะใช้ชื่อภาษาอังกฤษที่ถูก map ไว้
        $search_type_thai = mysqli_real_escape_string($conn, $_GET['type']); 
    }
}

// Map ชื่อภาษาไทยจาก UI ไปยังชื่อภาษาอังกฤษในตาราง PublicationType
// อิงตามรูปตาราง PublicationType ล่าสุดที่คุณส่งมา
$type_mapping = [
    'ตำรา' => 'Journal Article', 
    'วารสาร' => 'Conference Proceeding', 
    'บทความ' => 'Thesis/Dissertation', 
    'อื่นๆ' => 'Book Chapter'
];

// ถ้ามีการเลือกประเภทผลงาน ให้หาชื่อภาษาอังกฤษที่แท้จริงสำหรับใช้ใน SQL
if (isset($search_type_thai) && isset($type_mapping[$search_type_thai])) {
    $search_type = $type_mapping[$search_type_thai];
} else if (isset($search_type_thai)) {
    // กรณีที่ type ถูก set แต่ไม่ตรงกับ map (ควรกำหนดเป็นค่าว่างหากไม่ตรง)
    $search_type = ''; 
}


// --------------------------------------------------------------------------------------
// SQL QUERY: เพิ่มการค้นหา Keyword และใช้ชื่อตาราง/คอลัมน์ที่ถูกต้อง
// --------------------------------------------------------------------------------------

// 1. SELECT: ดึงข้อมูลที่ต้องการและรวมชื่อผู้แต่ง
$sql = "
SELECT 
    p.pub_ID,
    p.title,
    p.abstract,
    p.publish_date,
    p.file,
    pt.type_name,
    GROUP_CONCAT(DISTINCT u.username SEPARATOR ', ') AS authors_list
FROM 
    Publication p
LEFT JOIN 
    PublicationType pt ON p.type_ID = pt.type_ID
LEFT JOIN 
    pub_author pa ON p.pub_ID = pa.pub_ID
LEFT JOIN 
    User u ON pa.UID = u.UID 
-- เพิ่มตาราง Keyword และตารางเชื่อมโยง (สันนิษฐานชื่อ pub_keyword)
LEFT JOIN
    pub_keyword pk ON p.pub_ID = pk.pub_ID
LEFT JOIN
    Keyword k ON pk.key_ID = k.key_ID
";

// 2. WHERE: กำหนดเงื่อนไขการค้นหา
$where_clauses = [];

$where_clauses[] = "p.status = 'Approved'";

// เงื่อนไขการค้นหาข้อความ (Title, Abstract, Author Name, Keyword)
if (!empty($search_query)) {
    $search_query_escaped = "%" . $search_query . "%";
    $where_clauses[] = "
        (p.title LIKE '$search_query_escaped'         -- ค้นหาจากชื่อผลงาน
        OR p.abstract LIKE '$search_query_escaped'    -- ค้นหาจากบทคัดย่อ
        OR u.username LIKE '$search_query_escaped'    -- ค้นหาจากชื่อผู้แต่ง
        OR k.key_name LIKE '$search_query_escaped')   -- ค้นหาจาก Keyword
    ";
}

// เงื่อนไขการค้นหาตามปี พ.ศ.
if (!empty($search_year)) {
    // แปลงปี พ.ศ. ให้เป็นปี ค.ศ. สำหรับการค้นหาในฐานข้อมูล
    $search_ad_year = $search_year - 543;
    $where_clauses[] = "YEAR(p.publish_date) = '$search_ad_year'";
}

// เงื่อนไขการค้นหาตามประเภทผลงาน (ใช้ชื่อภาษาอังกฤษที่ถูก map แล้ว)
if (!empty($search_type)) {
    // ต้องระบุเป็นชื่อภาษาอังกฤษที่ถูกต้องในฐานข้อมูล (เช่น 'Journal Article')
    $where_clauses[] = "pt.type_name = '$search_type'"; 
}

// สร้าง WHERE Clause
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// 3. GROUP BY: จัดกลุ่มเพื่อให้ GROUP_CONCAT ทำงานได้ (สำคัญสำหรับ M:N relationship)
$sql .= " GROUP BY p.pub_ID ORDER BY p.publish_date DESC";

// --------------------------------------------------------------------------------------
// สิ้นสุด SQL QUERY
// --------------------------------------------------------------------------------------

// *** DEBUG LINE: UNCOMMENT THIS LINE TEMPORARILY หากพบข้อผิดพลาด
// echo "DEBUG: Running SQL: " . htmlspecialchars($sql); die(); 

$result = mysqli_query($conn, $sql);

if (!$result) {
    // จัดการข้อผิดพลาดของ Query
    die("Query Error: " . mysqli_error($conn) . "<br>SQL: " . htmlspecialchars($sql));
}

// ฟังก์ชันสำหรับแปลงปี ค.ศ. เป็น พ.ศ.
function toBuddhistYear($date) {
    if (empty($date) || $date == '0000-00-00') return '';
    return date('Y', strtotime($date)) + 543;
}

// ฟังก์ชันสำหรับสร้างไอคอนตามประเภทผลงาน (เปลี่ยนเป็น Emoji)
function getCategoryIcon($type) {
    // ใช้ชื่อภาษาไทยในการแสดงผล
    switch ($type) {
        case 'ตำรา':
            return '📘';
        case 'วารสาร':
            return '📑';
        case 'บทความ':
            return '📝';
        case 'อื่นๆ':
            return '📂';
        default:
            return '📄';
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบค้นหาผลงานตีพิมพ์</title>
    <!-- Tailwind CSS CDN -->
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

<body class="antialiased">

    <!-- Header (แถบยาวเต็มจอ) -->
<header class="bg-white header-shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-0">
    <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="mr-auto pl-2">
    <span class="text-2xl font-bold text-blue-700 tracking-wider">ระบบบริหารจัดการผลงานตีพิมพ์</span>

</div>


            <!-- User Actions (ปุ่มลงทะเบียน/เข้าสู่ระบบ) -->
            <div class="flex items-center space-x-4">
                <!-- ลงทะเบียน (Secondary Button Style) -->
                <a href="#" class="px-3 py-1.5 font-semibold text-blue-600 border border-blue-600 rounded-lg 
                                  transition duration-150 ease-in-out hover:bg-blue-600 hover:text-white 
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    ส่งคำร้องขอบัญชี
                </a>
                <!-- เข้าสู่ระบบ (Primary Button Style) -->
                <a href="#" class="px-4 py-1.5 font-semibold text-white bg-blue-600 border border-blue-600 rounded-lg 
                                  transition duration-150 ease-in-out hover:bg-blue-700 hover:border-blue-700 
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    เข้าสู่ระบบ
                </a>
            </div>
        </div>
    </div>
</header>


    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Search Bar and Filters Section -->
        <div class="flex flex-col items-center mb-12">
            
            <!-- Search Form -->
            <form method="GET" class="w-full max-w-2xl bg-white shadow-xl rounded-full p-1 flex items-center mb-6 border border-gray-200">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="ค้นหาชื่อผลงาน, บทคัดย่อ, ผู้แต่ง, หรือ Keyword..." 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    class="flex-grow py-3 px-6 text-gray-700 placeholder-gray-400 focus:outline-none bg-transparent rounded-l-full"
                >
                
                <!-- Year Filter Dropdown -->
                <select name="year" class="px-4 py-3 text-gray-700 bg-transparent focus:outline-none border-l border-gray-200">
                    <option value="all">ปีทั้งหมด</option>
                    <?php
                    // สร้าง Dropdown ปี พ.ศ. (ย้อนหลัง 10 ปี)
                    for ($y = $current_year; $y >= $current_year - 10; $y--) {
                        $selected = ($search_year == $y) ? 'selected' : '';
                        echo "<option value='{$y}' {$selected}>พ.ศ. {$y}</option>";
                    }
                    ?>
                </select>

                <!-- Search Button -->
                <button type="submit" class="bg-blue-600 text-white p-3.5 rounded-full hover:bg-blue-700 transition duration-150 ease-in-out ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </form>

            <!-- Category Icons (ใช้สำหรับ Filter) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 w-full max-w-4xl">
                <?php
                // Mapping ชื่อประเภทผลงานจากภาษาไทย (UI) ไปยังชื่อภาษาอังกฤษ (DB)
                $categories_ui = [
                    'ตำรา' => 'Journal Article', 
                    'วารสาร' => 'Conference Proceeding', 
                    'บทความ' => 'Thesis/Dissertation', 
                    'อื่นๆ' => 'Book Chapter'
                ];
                
                // ใช้ $categories_ui ในการสร้างปุ่ม Filter
                foreach ($categories_ui as $thai_name => $db_value) {
                    
                    // ตรวจสอบว่าไอคอนนี้ถูกเลือกอยู่หรือไม่ โดยเทียบกับค่าที่ส่งมาจาก GET (ค่าภาษาไทย)
                    $isActive = (isset($search_type_thai) && $search_type_thai === $thai_name) ? 'bg-blue-50 ring-2 ring-blue-500' : 'bg-white hover:shadow-lg';
                    $icon = getCategoryIcon($thai_name);
                    
                    // สร้าง URL สำหรับ Filter 
                    $link = "?search=" . urlencode($search_query) . "&year=" . urlencode($search_year) . "&type=" . urlencode($thai_name);
                    
                    // ถ้าคลิกซ้ำให้ยกเลิกการ filter โดยตั้ง type เป็น 'all'
                    if (isset($search_type_thai) && $search_type_thai === $thai_name) {
                         $link = "?search=" . urlencode($search_query) . "&year=" . urlencode($search_year) . "&type=all";
                    }

                    echo "
                    <a href='{$link}' class='flex flex-col items-center justify-center p-4 rounded-xl shadow-md transition duration-300 {$isActive} transform hover:scale-[1.02] cursor-pointer'>
                        <div class='text-5xl text-blue-600 mb-2'>{$icon}</div>
                        <span class='font-semibold text-gray-700'>{$thai_name}</span>
                    </a>
                    ";
                }
                ?>
            </div>

        </div>
        
        <!-- Results Section -->
        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">ผลการค้นหา (<?php echo mysqli_num_rows($result); ?> รายการ)</h2>
        

        <div class="space-y-6">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 transition duration-300 hover:shadow-xl">
                        <div class="flex items-start justify-between">
                            <h3 class="text-xl font-bold text-blue-700 mb-1"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <span class="px-3 py-1 text-sm font-medium text-purple-700 bg-purple-100 rounded-full flex-shrink-0 ml-4">
                                <!-- แสดงชื่อประเภทผลงาน (ภาษาอังกฤษจาก DB) -->
                                <?php echo htmlspecialchars($row['type_name']); ?>
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-500 mb-3">
                            <span class="font-semibold text-gray-600">ผู้แต่ง:</span> <?php echo htmlspecialchars($row['authors_list'] ?: 'ไม่ระบุ'); ?> | 
                            <span class="font-semibold text-gray-600">ปี:</span> <?php echo toBuddhistYear($row['publish_date']); ?>
                        </p>
                        
                        <!-- Actions -->
<div class="flex space-x-4">
    <?php if (!empty($row['file'])): ?>
    <?php endif; ?>
    <a href="javascript:void(0);" 
       onclick="showModal('<?php echo htmlspecialchars($row['title']); ?>', '<?php echo htmlspecialchars($row['abstract']); ?>', '<?php echo htmlspecialchars($row['authors_list'] ?: 'ไม่ระบุ'); ?>', '<?php echo toBuddhistYear($row['publish_date']); ?>', '<?php echo htmlspecialchars($row['file']); ?>')" 
       class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info mr-1"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        รายละเอียดเพิ่มเติม
    </a>
    
</div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl shadow-lg">
                    <p class="text-xl text-gray-500">ไม่พบผลงานตีพิมพ์ที่ตรงกับเงื่อนไขการค้นหาของคุณ</p>
                </div>
            <?php endif; ?>
<!-- หลังจบผลการค้นหา -->
<div class="flex justify-end mt-4 space-x-2 items-center">
    <a href="feedback.php" 
       class="text-blue-600 hover:text-blue-800 font-semibold flex items-center transition duration-150 underline">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
             class="lucide lucide-message-square mr-1">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z"/>
        </svg>
        เสนอแนะเกี่ยวกับระบบ
    </a>

    <span class="text-gray-400">|</span>

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
</div>


        </div>

    </main>
    <!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 relative">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 font-bold text-xl">&times;</button>
        <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>
        <p id="modalAuthors" class="text-sm text-gray-600 mb-2"></p>
        <p id="modalYear" class="text-sm text-gray-600 mb-4"></p>
        <p id="modalAbstract" class="text-gray-700"></p>

       <!-- ปุ่มดาวน์โหลด -->
<a id="modalDownload" href="#" target="_blank"
   class="flex items-center justify-center w-full px-4 py-3 mt-4 
          bg-gradient-to-r from-blue-500 to-blue-600 
          text-white font-semibold rounded-xl shadow-lg 
          hover:from-blue-600 hover:to-blue-700 
          focus:outline-none focus:ring-2 focus:ring-blue-400 
          transition duration-200 ease-in-out">
    <!-- ไอคอนดาวน์โหลด -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
    </svg>
    ดาวน์โหลดไฟล์
</a>


    </div>
    
</div>
<script>
function showModal(title, abstract, authors, year) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalAbstract').innerText = abstract;
    document.getElementById('modalAuthors').innerText = "ผู้แต่ง: " + authors;
    document.getElementById('modalYear').innerText = "ปี: " + year;
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modal').classList.add('flex');
    let downloadBtn = document.getElementById('modalDownload');
    if (file && file !== '') {
        downloadBtn.href = 'uploads/' + file; // สมมติไฟล์เก็บในโฟลเดอร์ uploads/
        downloadBtn.style.display = 'inline-block';
    } else {
        downloadBtn.style.display = 'none';
    }

}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('modal').classList.remove('flex');
}
</script>

<script>
function showModal(title, abstract, authors, year, file) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalAbstract').innerText = abstract;
    document.getElementById('modalAuthors').innerText = "ผู้แต่ง: " + authors;
    document.getElementById('modalYear').innerText = "ปี: " + year;
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modal').classList.add('flex');

    let downloadBtn = document.getElementById('modalDownload');
    if (file && file !== '') {
        downloadBtn.href = 'uploads/' + file; // สมมติว่าไฟล์อยู่ในโฟลเดอร์ uploads/
        downloadBtn.setAttribute("download", file); // บังคับให้ดาวน์โหลด
        downloadBtn.style.display = 'flex';
    } else {
        downloadBtn.style.display = 'none';
    }
}
</script>

</body>
</html>