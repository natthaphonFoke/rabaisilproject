<?php
session_start();
require_once('../dbconnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ตรวจสอบหากมีการกรอกข้อมูลไม่ครบ
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอก Student ID และ Password";
        header("Location: login.php");
        exit();
    }

    // ใช้ตาราง userid แทน student
    $sql = "SELECT username, password, user_type FROM userid WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในคำสั่ง SQL: " . $conn->error;
        header("Location: login.php");
        exit();
    }

    $stmt->bind_param("s", $username); // ใช้ $username แทน $student_id
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // ถ้า user_type เป็น Psychologist
            if ($row['user_type'] === 'Psychologist') {
                // ดึงข้อมูลนักจิตวิทยาจากตาราง psychologist
                $psychologist_sql = "SELECT first_name, last_name FROM psychologist WHERE user_id = ?";
                $psychologist_stmt = $conn->prepare($psychologist_sql);
                $psychologist_stmt->bind_param("s", $username); // ใช้ $username แทน $student_id
                $psychologist_stmt->execute();
                $psychologist_result = $psychologist_stmt->get_result();
                $psychologist_data = $psychologist_result->fetch_assoc();
            
                // เก็บข้อมูลนักจิตวิทยาใน session
                $_SESSION['user_name'] = $psychologist_data['first_name'] . " " . $psychologist_data['last_name'];
                $_SESSION['user_id'] = $username; // เก็บ user_id
                $_SESSION['role'] = 'Psychologist';
            
                // ส่งไปที่หน้า dashboard ของนักจิตวิทยา
                header("Location: ../adminUI/dashboard/dashboard.php?username=" . urlencode($username)); // ส่ง username เป็นพารามิเตอร์
                exit();

            } elseif ($row['user_type'] === 'Student') {
                // ถ้า user_type เป็น Student
                // ดึงข้อมูลนักศึกษาจากตาราง student
                $student_sql = "SELECT first_name, last_name FROM student WHERE std_id = ?";
                $student_stmt = $conn->prepare($student_sql);
                $student_stmt->bind_param("s", $username); // ใช้ $username แทน $student_id
                $student_stmt->execute();
                $student_result = $student_stmt->get_result();
                $student_data = $student_result->fetch_assoc();

                // เก็บข้อมูลนักศึกษาใน session
                $_SESSION['user_name'] = $student_data['first_name'] . " " . $student_data['last_name'];
                $_SESSION['student_id'] = $username; // เก็บ student_id
                $_SESSION['role'] = 'Student';

                // ส่งไปที่หน้า user app details
                header("Location: ../UserUI/dashboard.php?username=" . urlencode($username)); // ส่ง username เป็นพารามิเตอร์
                exit();
            }
        } else {
            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "ไม่พบ Student ID ในระบบ!";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
