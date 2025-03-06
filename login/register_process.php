<?php
include('../dbconnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $std_id = trim($_POST['std_id']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    $faculty_id = trim($_POST['faculty_id']);
    $major = trim($_POST['major']);
    $year = trim($_POST['year']);
    $gender = trim($_POST['gender']);
    $phone = trim($_POST['phone']);

    // ตรวจสอบรหัสผ่านว่าตรงกันหรือไม่
    if ($password !== $confirm_password) {
        echo "รหัสผ่านไม่ตรงกัน";
        exit;
    }

    // ตรวจสอบว่ามี std_id นี้อยู่แล้วหรือไม่
    $checkStmt = $conn->prepare("SELECT std_id FROM student WHERE std_id = ?");
    $checkStmt->bind_param("s", $std_id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "รหัสนักศึกษานี้ถูกใช้ไปแล้ว";
        exit;
    }
    $checkStmt->close();

    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เริ่มการบันทึกข้อมูลลงในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO student (std_id, first_name, last_name, nickname, email, faculty_id, major, year, gender, phone) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $std_id, $first_name, $last_name, $nickname, $email, $faculty_id, $major, $year, $gender, $phone);

    // ตรวจสอบการบันทึกข้อมูลในตาราง student
    if ($stmt->execute()) {
        // บันทึกลง userid หลังจาก student สำเร็จ
        $stmt2 = $conn->prepare("INSERT INTO userid (username, password, user_type) VALUES (?, ?, 'Student')");
        $stmt2->bind_param("ss", $std_id, $hashed_password);
        
        if ($stmt2->execute()) {
            echo "ลงทะเบียนสำเร็จ";
            header("Location: login.php");
            exit;
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลผู้ใช้: " . $stmt2->error;
        }

        $stmt2->close(); // ปิดการเชื่อมต่อ stmt2
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลนักศึกษา: " . $stmt->error;
    }

    $stmt->close(); // ปิดการเชื่อมต่อ stmt
    $conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
}
?>
