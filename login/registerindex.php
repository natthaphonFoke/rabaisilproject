<?php
require_once('../dbconnect.php'); // ไฟล์เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // ตรวจสอบรหัสผ่านตรงกัน
        if ($_POST['password'] !== $_POST['confirmPassword']) {
            throw new Exception("รหัสผ่านไม่ตรงกัน");
        }

        // รับค่าจากฟอร์มและกำจัดช่องว่าง
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $email = trim($_POST['email']);

        // ตรวจสอบข้อมูลห้ามเป็นค่าว่าง
        if (empty($username) || empty($password) || empty($firstname) || empty($lastname) || empty($email)) {
            throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
        }

        // เช็คชื่อผู้ใช้ซ้ำ
        $check_sql = "SELECT * FROM userid WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            throw new Exception("ชื่อผู้ใช้นี้ถูกใช้งานแล้ว");
        }

        // เริ่ม Transaction
        $conn->begin_transaction();

        // เพิ่มข้อมูลลงตาราง userid
        $insert_user_sql = "INSERT INTO userid (username, password, user_type) VALUES (?, ?, 'Psychologist')";
        $insert_user_stmt = $conn->prepare($insert_user_sql);
        $insert_user_stmt->bind_param("ss", $username, $password);

        if (!$insert_user_stmt->execute()) {
            throw new Exception("เกิดข้อผิดพลาดในการเพิ่มข้อมูลผู้ใช้");
        }

        // เพิ่มข้อมูลลงตาราง psychologist
        $insert_psychologist_sql = "INSERT INTO psychologist (user_id, first_name, last_name, email) VALUES (?, ?, ?, ?)";
        $insert_psychologist_stmt = $conn->prepare($insert_psychologist_sql);
        $insert_psychologist_stmt->bind_param("ssss", $username, $firstname, $lastname, $email);

        if (!$insert_psychologist_stmt->execute()) {
            throw new Exception("เกิดข้อผิดพลาดในการเพิ่มข้อมูลนักจิตวิทยา");
        }

        // บันทึกข้อมูลทั้งหมด
        $conn->commit();

        // แจ้งเตือนเมื่อสมัครสมาชิกสำเร็จ
        echo "<script>
            alert('สมัครสมาชิกสำเร็จ');
            window.location.href = 'login.php';
        </script>";

    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาดให้ยกเลิก Transaction
        $conn->rollback();

        echo "<script>
            alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    } finally {
        // ปิด statement และ connection
        $insert_user_stmt->close();
        $insert_psychologist_stmt->close();
        $check_stmt->close();
        $conn->close();
    }
}
?>
