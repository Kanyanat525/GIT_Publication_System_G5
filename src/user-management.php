
<?php
// 1. เชื่อมต่อฐานข้อมูล
// ต้องเปลี่ยน path ให้ถูกต้องตามโครงสร้างโฟลเดอร์จริงของคุณ
require_once "db_connect.php";

// 2. สร้าง SQL Query เพื่อดึงข้อมูลผู้ใช้
// ใช้ JOIN เพื่อดึงชื่อสิทธิ์ (type_name) มาแสดงแทนตัวเลข (user_type_ID)
$sql = "SELECT 
            U.UID, 
            U.username, 
            U.email, 
            T.type_name 
        FROM 
            User U
        JOIN 
            user_type T ON U.user_type_ID = T.user_type_ID
        ORDER BY 
            U.UID ASC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin - จัดการบัญชีผู้ใช้</title>
    <style>
        /* CSS ง่ายๆ สำหรับการแสดงผล */
        body { font-family: Arial, sans-serif; }
        table { width: 80%; border-collapse: collapse; margin: 20px auto; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; margin: 2px; cursor: pointer; }
        .btn-add { background-color: #4CAF50; color: white; border: none; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">หน้าผู้ดูแลระบบ: จัดการบัญชีผู้ใช้</h2>

    <div style="text-align: center;">
        <a href="add_user.php"><button class="btn btn-add">เพิ่มบัญชีผู้ใช้ใหม่</button></a>
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
            <?php
            if (mysqli_num_rows($result) > 0) {
                // วนลูปแสดงข้อมูลทีละแถว
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row["UID"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["type_name"]) . "</td>";
                    
                    // ปุ่มแก้ไขและลบ (ลิงก์ไปยังไฟล์ที่จะสร้างต่อไป)
                    echo "<td>";
                    echo "<a href='edit_user.php?id=" . $row["UID"] . "'><button class='btn'>แก้ไข</button></a>";
                    echo "<a href='delete_user.php?id=" . $row["UID"] . "' onclick=\"return confirm('คุณแน่ใจหรือไม่ที่จะลบบัญชีนี้?');\"><button class='btn'>ลบ</button></a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align: center;'>ไม่พบข้อมูลผู้ใช้งาน</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
// 3. ปิดการเชื่อมต่อเมื่อใช้งานเสร็จ
mysqli_close($conn);
?>