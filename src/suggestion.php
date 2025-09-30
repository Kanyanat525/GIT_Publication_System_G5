<?php
session_start();
include('db_connect.php');

$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO feedback (sender_email, subject, message, submit_date) 
            VALUES ('$email', '$subject', '$message', NOW())";
    if ($conn->query($sql)) {
        $success = "✅ ขอบคุณสำหรับข้อเสนอแนะ ระบบได้บันทึกไว้เรียบร้อยแล้ว";
    } else {
        $success = "❌ เกิดข้อผิดพลาด: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เสนอแนะเกี่ยวกับระบบ</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(to bottom, #dceeff, #b7dbf7);
            margin: 0;
            min-height: 100vh;
        }

        /* 🔹 Navbar */
        .navbar {
            background: #fff;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .system-title {
            font-size: 22px;
            font-weight: 700;
            color: #1d4ed8;
            text-decoration: none;
        }
        .logout-btn {
            background:#ef4444;
            color:#fff;
            padding:8px 16px;
            border-radius:6px;
            text-decoration:none;
            font-weight:500;
            transition:background .2s;
        }
        .logout-btn:hover { background:#dc2626; }

        /* 🔹 Container */
        .content {
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }
        .container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 500px;
        }
        h2 { text-align: center; margin: 0 0 30px 0; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 500; display: block; margin-bottom: 8px; }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        textarea { resize: vertical; min-height: 120px; }
        button {
            width: 100%;
            padding: 14px;
            background: #1d4ed8;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #2563eb; }
        .success {
            margin-top: 20px;
            padding: 14px;
            background: #b2f0c0;
            color: #0a5722;
            border-radius: 6px;
            text-align: center;
            display: <?php echo $success ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

<!-- 🔹 Navbar -->
<div class="navbar">
    <a href="index.php" class="system-title">ระบบบริหารจัดการผลงานตีพิมพ์</a>
    <a href="?logout=1" class="logout-btn">ออกจากระบบ</a>
</div>

<!-- 🔹 Content -->
<div class="content">
    <div class="container">
        <h2>เสนอแนะเกี่ยวกับระบบ</h2>
        <form method="post">
            <div class="form-group">
                <label>อีเมล</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>หัวข้อ</label>
                <input type="text" name="subject" required>
            </div>
            <div class="form-group">
                <label>ข้อความ</label>
                <textarea name="message" required></textarea>
            </div>
            <button type="submit">ส่งข้อเสนอแนะ</button>
        </form>
        <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>