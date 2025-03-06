<?php
session_start();
require_once('../dbconnect.php'); 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
</head>

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
                <label for="std_id">ชื่อผู้ใช้</label>
                <input type="text" name="username" id="username"  placeholder="กรุณาใส่ชื่อผู้ใช้" required>

                <label for="password">รหัสผ่าน</label>
                <input type="password" name="password" id="password" placeholder="กรุณาใส่รหัสผ่าน" required>

                <button type="submit">เข้าสู่ระบบ</button>
                <a href="register.php" class="login-button">ไปที่หน้า register</a>
                <a href="change_password.php" class="login-button">เปลี่ยนรหัสผ่าน</a>
            </form>
        </div>
    </div>

    <!-- แสดงแจ้งเตือนถ้ามีข้อผิดพลาด -->
    <?php if (isset($_SESSION['error'])) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'ตกลง'
            });
        </script>
        <?php unset($_SESSION['error']); ?> <!-- ลบ session error -->
    <?php endif; ?>
</body>
</html>
