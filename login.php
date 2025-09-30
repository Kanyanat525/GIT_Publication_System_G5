<?php
session_start();
require_once "db_connect.php";

// ถ้าเข้าสู่ระบบแล้ว ไม่ให้กลับมาหน้านี้อีก
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row["password"])) {
            $_SESSION["user"] = $row["username"];
            $_SESSION["user_id"] = $row["UID"];
            $_SESSION["user_type"] = $row["user_type_ID"];

            // ✅ เปลี่ยนเส้นทางหลังเข้าสู่ระบบ
            header("Location: index.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Noto Sans Thai', sans-serif; box-sizing: border-box; }

        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(to bottom, #FFFFFF 15%, #B1D3FE 55%, #CAE2FF 90%);
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

        .left { flex: 1; text-align: left; }

        .left h1 {
            font-size: 2.3rem;
            color: #1a2a4e;
            font-weight: 600;
            line-height: 1.4;
        }

        .right { flex: 1; display: flex; justify-content: flex-end; }

        .login-box {
            background: white;
            width: 380px;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .login-box h2 {
            font-size: 1.3rem;
            color: #1a2a4e;
            margin-bottom: 25px;
            text-align: center;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin-bottom: 18px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease-in-out;
        }

        input:focus {
            border-color: #1d4ed8;
            outline: none;
            box-shadow: 0 0 3px rgba(29, 78, 216, 0.3);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #1d4ed8;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .toggle-password:hover { color: #0b2e5b; }

        .btn {
            width: 100%;
            background: #0b2e5b;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 8px;
            transition: background 0.2s ease-in-out;
        }

        .btn:hover { background: #143e7a; }

        .error {
            color: #b91c1c;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-align: center;
        }

        .small-link {
            font-size: 0.9rem;
            text-align: right;
            margin-bottom: 12px;
        }

        .small-link a {
            color: #2563eb;
            text-decoration: none;
        }

        .small-link a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .wrapper { flex-direction: column; text-align: center; }
            .left { margin-bottom: 35px; }
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
                    <div class="input-group">
                        <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                    </div>

                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="รหัสผ่าน" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>

                    <div class="small-link">
                        <a href="request_account.php">ส่งคำร้องขอบัญชี</a>
                    </div>

                    <button type="submit" class="btn">เข้าสู่ระบบ</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleIcon = document.querySelector(".toggle-password i");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            }
        }
    </script>
</body>
</html>
