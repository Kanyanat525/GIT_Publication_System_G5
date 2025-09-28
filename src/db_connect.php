<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'publication_db');

/* พยายามเชื่อมต่อฐานข้อมูล MySQLi */
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// ตรวจสอบการเชื่อมต่อ
if($conn === false){
    die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}
?>