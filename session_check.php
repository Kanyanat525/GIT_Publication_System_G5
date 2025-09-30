<?php
// session_check.php
session_start();

// ถ้ายังไม่ได้เข้าสู่ระบบ — เด้งกลับไปหน้า login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
?>
