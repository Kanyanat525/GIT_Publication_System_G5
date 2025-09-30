<?php
// กำหนดค่าคงที่สำหรับการเชื่อมต่อฐานข้อมูล (DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME)
// **โปรดตรวจสอบว่าค่า DB_PASSWORD ตรงกับรหัสผ่านจริงบน MySQL/MariaDB ใหม่ของคุณ**

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); 
define('DB_NAME', 'publication_db');

// สร้างการเชื่อมต่อฐานข้อมูล
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// ตรวจสอบการเชื่อมต่อ
if($conn === false){
    // แสดงข้อผิดพลาดและหยุดการทำงานทันทีหากเชื่อมต่อไม่ได้
    die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}

// กำหนดให้การเชื่อมต่อใช้ภาษาไทย (UTF-8) เพื่อป้องกันปัญหาภาษาไทยเพี้ยน
mysqli_set_charset($conn, "utf8mb4");

// ตอนนี้ตัวแปร $conn พร้อมใช้งานในไฟล์ index.php
?>
