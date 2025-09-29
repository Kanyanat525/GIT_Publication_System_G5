<?php
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
        body {
            background: #d0e6ff;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        .history-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 600px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        h2 {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="history-box">
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
</body>
</html>
