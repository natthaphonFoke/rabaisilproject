<?php
session_start();

// ตรวจสอบ Query String 'error' สำหรับแสดงข้อความผิดพลาด
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $error_message = "รหัสนักศึกษา หรือรหัสผ่านไม่ถูกต้อง!";
            break;
        case 'missing_fields':
            $error_message = "กรุณากรอกข้อมูลให้ครบถ้วน!";
            break;
        default:
            $error_message = "เกิดข้อผิดพลาด โปรดลองอีกครั้ง!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<script>
        // ฟังก์ชันสำหรับซ่อนข้อความแจ้งเตือน
        document.addEventListener("DOMContentLoaded", function () {
            const alerts = document.querySelectorAll(".alert");
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.display = "none";
                }, 5000); // ซ่อนหลังจาก 5 วินาที (5000 มิลลิวินาที)
            });
        });
    </script>
<body>
    <div class="container">
        <!-- Left Section -->
        <div class="left-section">
            <div>
                <h1>ระบายศิลป์</h1>
                <p>
                    "เราพร้อมเคียงข้างคุณ ฟื้นฟูใจ<br>
                    มั่นใจในทุกก้าวเดินของคุณ"
                </p>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <h2>เข้าสู่ระบบ</h2>

            <form method="POST" action="login_process.php">
                <label for="std_id">รหัสนักศึกษา</label>
                <input type="text" name="std_id" id="std_id" placeholder="กรุณาใส่รหัสนักศึกษา" required>

                <label for="password">รหัสผ่าน</label>
                <input type="password" name="password" id="password" placeholder="กรุณาใส่รหัสผ่าน" required>

                <button type="submit">เข้าสู่ระบบ</button>
                <a href="register.php" class="login-button">ไปที่หน้า register</a>
            </form>
        </div>
    </div>
</body>

</html>