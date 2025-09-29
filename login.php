<?php
session_start();
require_once "db_connect.php"; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // เตรียมคำสั่ง SQL
    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่านที่เข้ารหัสไว้ใน DB
        if (password_verify($password, $row["password"])) {
            $_SESSION["user"] = $row["username"];
            $_SESSION["user_id"] = $row["UID"];
            $_SESSION["user_type"] = $row["user_type_ID"];

            // ✅ กำหนดเส้นทางไปยังหน้าเฉพาะของแต่ละประเภทผู้ใช้
            switch ($row["user_type_ID"]) {
                case 1: // ถ้าเป็นแอดมิน
                    header("Location: admin.php");
                    break;
                case 2: // ถ้าเป็นเจ้าหน้าที่ (Officer)
                    header("Location: officer.php");
                    break;
                case 3: // ถ้าเป็นอาจารย์ (Lecturer)
                    header("Location: lecturer.php");
                    break;
                case 4: // ถ้าเป็นนักศึกษา (Student)
                    header("Location: student.php");
                    break;
                default:
                    // หาก user_type_ID ไม่ตรงกับที่กำหนด ให้ไปหน้า default
                    header("Location: dashboard.php");
                    break;
            }
            exit();

        } else {
            $error = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "ไม่พบบัญชีผู้ใช้";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบบริหารจัดการผลงานตีพิมพ์</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Noto Sans Thai', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(to bottom, #FFFFFF 19%, #B1D3FE 54%, #CAE2FF 92%);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80%;
            max-width: 1100px;
        }

        .left {
            flex: 1;
            text-align: left;
        }

        .left h1 {
            font-size: 2.5rem;
            color: #1a2a4e;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.15);
        }

        .right {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }

        .login-box {
            background: white;
            width: 360px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .login-box h2 {
            font-size: 1.2rem;
            color: #2d3a6b;
            margin-bottom: 20px;
            text-align: center;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        .btn {
            width: 100%;
            background: #0b2e5b;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .btn:hover {
            background: #143e7a;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-align: center;
        }

        .small-link {
            font-size: 0.9rem;
            text-align: right;
            margin-bottom: 10px;
        }

        .small-link a {
            color: #3b5fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="left">
            <h1>ระบบบริหารจัดการผลงานตีพิมพ์</h1>
        </div>
        <div class="right">
            <div class="login-box">
                <h2>เข้าสู่ระบบ</h2>
                <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
                <form method="POST" action="">
                    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                    <input type="password" name="password" placeholder="รหัสผ่าน" required>

                    <div class="small-link">
                        <a href="request_account.php">ส่งคำร้องขอบัญชี</a>
                    </div>

                    <button type="submit" class="btn">เข้าสู่ระบบ</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
