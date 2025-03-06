<?php
session_start();
include '../dbconnect.php';



if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง!";
    } elseif ($new_password !== $confirm_password) {
        $error = "รหัสผ่านใหม่ไม่ตรงกับการยืนยันรหัสผ่าน!";
    } else {
        // ดึงรหัสผ่านเดิมจากฐานข้อมูล
        $stmt = $conn->prepare("SELECT password FROM userid WHERE username = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // ตรวจสอบรหัสผ่านเดิม
        if (!password_verify($current_password, $hashed_password)) {
            $error = "รหัสผ่านเดิมไม่ถูกต้อง!";
        } else {
            // แฮชรหัสผ่านใหม่
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // อัปเดตรหัสผ่านใหม่ลงฐานข้อมูล
            $update_stmt = $conn->prepare("UPDATE userid SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $new_hashed_password, $student_id);

            if ($update_stmt->execute()) {
                $success = "เปลี่ยนรหัสผ่านสำเร็จ! กำลังกลับไปหน้า Login...";
                session_destroy();
                header("refresh:3; url=login.php"); // Redirect หลังจาก 3 วินาที
                exit();
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน!";
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <!-- ✅ ใช้ Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h2 class="text-center mb-4">🔑 เปลี่ยนรหัสผ่าน</h2>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านเดิม</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100">เปลี่ยนรหัสผ่าน</button>
                            <a href="login.php" class="btn btn-link w-100 mt-2">ย้อนกลับ</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ ใช้ Bootstrap JS (ถ้าต้องการใช้ Modal หรือ Alert เพิ่มเติม) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
