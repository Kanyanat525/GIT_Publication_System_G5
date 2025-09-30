<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: lecturer.php'); exit; }

$id  = isset($_POST['id'])  ? (int)$_POST['id']  : 0;
$uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
if ($id<=0) { header('Location: '.($uid>0?"lecturer.php?uid=$uid":"lecturer.php")); exit; }

/* หา path รูปเพื่อจะลบ */
$st = mysqli_prepare($conn, "SELECT file FROM publication WHERE pub_ID=?");
mysqli_stmt_bind_param($st,"i",$id);
mysqli_stmt_execute($st);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
mysqli_stmt_close($st);
$img = $row['file'] ?? null;

/* ลบความสัมพันธ์ */
$d1 = mysqli_prepare($conn,"DELETE FROM pub_author WHERE pub_ID=?");
mysqli_stmt_bind_param($d1,"i",$id);
mysqli_stmt_execute($d1);
mysqli_stmt_close($d1);

/* ลบผลงาน */
$d2 = mysqli_prepare($conn,"DELETE FROM publication WHERE pub_ID=?");
mysqli_stmt_bind_param($d2,"i",$id);
mysqli_stmt_execute($d2);
mysqli_stmt_close($d2);

/* ลบไฟล์ (เฉพาะที่อยู่ใน uploads/) */
if ($img && preg_match('#^uploads/#',$img) && file_exists($img)) { @unlink($img); }

header('Location: '.($uid>0 ? "lecturer.php?uid=$uid" : "lecturer.php"));
exit;