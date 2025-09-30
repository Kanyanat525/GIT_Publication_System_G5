<?php
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $user_type_ID = $_POST["user_type_ID"];

    if (empty($username) || empty($email) || empty($user_type_ID)) {
        $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } else {
        // ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
        $check_sql = "SELECT * FROM registration_request WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในคำร้องแล้ว";
        } else {
            // เพิ่มคำร้องลงในฐานข้อมูล (ไม่มีรหัสผ่านแล้ว)
            $sql = "INSERT INTO registration_request (username, email, request_user_type_ID) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $email, $user_type_ID);

            if ($stmt->execute()) {
                $success = "ส่งคำร้องเรียบร้อยแล้ว 🎉 กรุณารอผู้ดูแลระบบตรวจสอบ";
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึกคำร้อง";
            }
        }
    }
}

// ดึงข้อมูลประเภทผู้ใช้
$type_result = $conn->query("SELECT * FROM user_type ORDER BY user_type_ID ASC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ส่งคำร้องขอบัญชีผู้ใช้</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Noto Sans Thai', sans-serif; box-sizing: border-box; }

        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(to bottom, #FFFFFF 19%, #B1D3FE 54%, #CAE2FF 92%);
            display: flex;
            flex-direction: column;
        }

        header {
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        header .title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1D4ED8;
            letter-spacing: 1px;
        }

        /* ปุ่มใน Header */
        .header-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-secondary {
            color: #1D4ED8;
            background: white;
            border: 2px solid #1D4ED8;
            padding: 6px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-secondary:hover {
            background: #1D4ED8;
            color: white;
        }

        .btn-primary {
            color: white;
            background: #1D4ED8;
            border: 2px solid #1D4ED8;
            padding: 6px 18px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-primary:hover {
            background: #2563EB;
            border-color: #2563EB;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-box {
            background: white;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 420px;
            text-align: center;
        }

        h2 {
            color: #1a2a4e;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }

        .btn-submit {
            width: 100%;
            background: #0b2e5b;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover { background: #123d7a; }

        .msg { margin-bottom: 10px; font-size: 0.95rem; }
        .error { color: red; }
        .success { color: green; }

        @media (max-width: 768px) {
            .form-box { width: 90%; padding: 25px; }
            header { flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
    <header>
        <!-- ชื่อระบบ -->
        <span class="title">ระบบบริหารจัดการผลงานตีพิมพ์</span>

        <!-- ปุ่มใน Header -->
        <div class="header-buttons">
            <a href="login.php" class="btn-primary">เข้าสู่ระบบ</a>
        </div>
    </header>

    <main>
        <div class="form-box">
            <h2>ส่งคำร้องขอบัญชีผู้ใช้</h2>

            <?php
                if (!empty($error)) echo "<div class='msg error'>$error</div>";
                if (!empty($success)) echo "<div class='msg success'>$success</div>";
            ?>

            <form method="POST" action="">
                <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                <input type="email" name="email" placeholder="อีเมล" required>

                <select name="user_type_ID" required>
                    <option value="">-- เลือกประเภทผู้ใช้ --</option>
                    <?php while ($row = $type_result->fetch_assoc()): ?>
                        <option value="<?= $row['user_type_ID'] ?>"><?= htmlspecialchars($row['type_name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn-submit">ส่งคำร้อง</button>
            </form>
        </div>
    </main>
</body>
</html>