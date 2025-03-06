<?php
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // รับข้อมูลจากฟอร์ม
    $std_id = $_POST['std_id'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $nickname = trim($_POST['nickname']);
    $faculty_id = $_POST['faculty_id'];
    $major = $_POST['major'];
    $year = $_POST['year'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'] ?? null;

    // ตรวจสอบข้อมูลที่ขาด
    if (empty($std_id) || empty($password) || empty($confirm_password) || empty($first_name) || 
        empty($last_name) || empty($nickname) || empty($faculty_id) || empty($major) || 
        empty($year) || empty($gender)) {
        die("กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    // ตรวจสอบรหัสผ่านว่าตรงกันหรือไม่
    if ($password !== $confirm_password) {
        die("รหัสผ่านไม่ตรงกัน");
    }

    // ตรวจสอบว่า std_id มีอยู่ในตาราง student หรือไม่
    $sql_check = "SELECT password FROM student WHERE std_id = ?";
    $stmt_check = $connect->prepare($sql_check);
    $stmt_check->bind_param("s", $std_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($existing_password);
        $stmt_check->fetch();
        $stmt_check->close();

        // ตรวจสอบว่ามี password หรือไม่
        if (!empty($existing_password)) {
            die("บัญชีนี้มีอยู่แล้ว");
        }

        // อัปเดตเฉพาะ password ในกรณีที่ password ไม่มีค่า
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql_update = "UPDATE student SET password = ? WHERE std_id = ?";
        $stmt_update = $connect->prepare($sql_update);
        $stmt_update->bind_param("ss", $hashed_password, $std_id);

        if ($stmt_update->execute()) {
            // อัปเดตสำเร็จ
            header("Location: login.php?success=password_updated");
            exit();
        } else {
            die("เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $stmt_update->error);
        }
    } else {
        // ถ้าไม่พบ std_id ในระบบ ให้ทำการ insert ข้อมูลใหม่
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql_insert = "INSERT INTO student (std_id, password, first_name, last_name, nickname, faculty_id, major, year, gender, phone) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $connect->prepare($sql_insert);
        $stmt_insert->bind_param("ssssssssss", $std_id, $hashed_password, $first_name, $last_name, $nickname, $faculty_id, $major, $year, $gender, $phone);

        if ($stmt_insert->execute()) {
            // บันทึกข้อมูลใหม่สำเร็จ
            header("Location: login.php?success=registered");
            exit();
        } else {
            die("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt_insert->error);
        }

        $stmt_insert->close();
    }

    // ปิดการเชื่อมต่อ
    $connect->close();
} else {
    die("Invalid request method");
}
?>
