<?php 
include 'connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST['std_id']);
    $password = $_POST['password'];

    if (empty($student_id) || empty($password)) {
        die("กรุณากรอก Student ID และ Password");
    }

    $sql = "SELECT * FROM student WHERE std_id = ?";
    $stmt = $connect->prepare($sql);

    if (!$stmt) {
        die("เกิดข้อผิดพลาดในคำสั่ง SQL: " . $connect->error);
    }

    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // บันทึกข้อมูลลงใน Session
            $_SESSION['user_name'] = $row['first_name'] . " " . $row['last_name'];
            $_SESSION['student_id'] = $row['std_id'];
            $_SESSION['role'] = $row['role']; // เก็บ role ไว้ใน Session

            // ส่งค่า std_id ผ่าน URL เพื่อไปยังหน้าอื่น ๆ
            if ($row['role'] === 'admin') {
                header("Location:app.php?std_id=" );
            } else if ($row['role'] === 'student') {
                header("Location:user_appdetails.php?std_id=" );
            }
            exit();
        } else {
            echo "รหัสผ่านไม่ถูกต้อง!";
        }
    } else {
        echo "ไม่พบ Student ID ในระบบ!";
    }

    $stmt->close();
    $connect->close();
}
?>
