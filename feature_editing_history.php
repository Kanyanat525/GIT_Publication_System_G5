<?php
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
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 400;
            font-stretch: 100%;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');
            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 400;
            font-stretch: 100%;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfPI2h.woff2) format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 600;
            font-stretch: 100%;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfKI2hX2g.woff2) format('woff2');
            unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
        }
        @font-face {
            font-family: 'Noto Sans Thai';
            font-style: normal;
            font-weight: 600;
            font-stretch: 100%;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/notosansthai/v29/iJWQBXeUZi_OHPqn4wq6hQ2_hbJ1xyN9wd43SofNWcdfMo2hX2g.woff2) format('woff2');
            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }
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
            color: #2d2adbff;
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
        <h1>ระบบบริหารจัดการผลงานตีพิมพ์</h1>
        <form action="logout.php" method="post" style="margin: 0;">
            <button type="submit" class="logout-btn">ออกจากระบบ</button>
        </form>
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
