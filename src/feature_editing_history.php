<?php
require_once "session_check.php";
require_once 'db_connect.php';
// ----------------------
// mock data (จำลอง)
// ----------------------
$histories = [
    [
        "edited_at" => "2025-09-28 14:30:00",
        "action" => "แก้ไขชื่อไฟล์",
        "edited_by" => "อาจารย์ A"
    ],
    [
        "edited_at" => "2025-09-27 18:45:00",
        "action" => "ลบชื่อไฟล์",
        "edited_by" => "อาจารย์ B"
    ],
    [
        "edited_at" => "2025-09-27 10:15:00",
        "action" => "ลบข้อมูล",
        "edited_by" => "อาจารย์ C"
    ]
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการแก้ไข</title>
    <style>
        /* ✅ Noto Sans Thai */
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 400;
            font-stretch: 100%;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');
            unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
        }
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(to bottom, #FFFFFF 19%, #B1D3FE 54%, #CAE2FF 92%);
            display: flex;
            flex-direction: column;
            font-family: 'Noto Sans Thai', sans-serif;
        }
        .navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            font-size: 20px;
            margin: 0;
        }
        .navbar h1 a {
            color: #0000FF; /* เปลี่ยนเป็นสีน้ำเงิน */
            text-decoration: none;
        }
        .btn-group {
            display: flex;
            gap: 10px;
        }
        .back-btn {
            background: white;
            color: #2d2adbff;
            border: 2px solid #0d0ac2ff;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Noto Sans Thai', sans-serif;
        }
        .back-btn:hover {
            background: #f0f0f0;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Noto Sans Thai', sans-serif;
        }
        .container {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 800px;
            box-shadow: 0 4px 12px rgba(41, 12, 203, 0.1);
        }
        .card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 10px;
        }
        th {
            background: #6b7280;
            color: white;
            padding: 12px;
            text-align: center;
            border-radius: 12px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(7, 7, 7, 0.02);
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            background: #fff;
            border-radius: 6px;
        }
        tr:hover td {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1><a href="index.php">ระบบบริหารจัดการผลงานตีพิมพ์</a></h1>
        <div class="btn-group">
            <button type="button" class="back-btn" onclick="history.back()">ย้อนกลับ</button>
            <form action="index.php" method="get" style="margin: 0;">
                <button type="submit" class="logout-btn">ออกจากระบบ</button>
            </form>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="card">
            <h2>ประวัติการแก้ไข</h2>
            <table>
                <tr>
                    <th>วันที่</th>
                    <th>รายละเอียดการแก้ไข</th>
                    <th>ผู้แก้ไข</th>
                </tr>
                <?php foreach ($histories as $row): ?>
                <tr>
                    <td><?php echo date("d/m/Y | H.i", strtotime($row['edited_at'])) . " น."; ?></td>
                    <td><?php echo htmlspecialchars($row['action']); ?></td>
                    <td><?php echo htmlspecialchars($row['edited_by']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>