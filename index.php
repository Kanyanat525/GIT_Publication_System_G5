<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แดชบอร์ด</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600&display=swap" rel="stylesheet">
<style>
    * { font-family: 'Noto Sans Thai', sans-serif; box-sizing: border-box; }
    body { background: linear-gradient(to bottom, #FFFFFF 15%, #B1D3FE 55%, #CAE2FF 90%); margin: 0; height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    h1 { color: #1a2a4e; margin-bottom: 20px; }
    .logout-btn {
        background: #0b2e5b;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s ease-in-out;
    }
    .logout-btn:hover { background: #143e7a; }
</style>
</head>
<body>
    <h1>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION["user"]); ?> 🎉</h1>
    <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
</body>
</html>
