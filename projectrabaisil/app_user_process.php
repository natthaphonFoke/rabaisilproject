<?php
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// เชื่อมต่อกับฐานข้อมูล
include 'connect.php';

// รับข้อมูลจากฟอร์ม
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$channel_id = $_POST['channel'];
$std_id = $_POST['std_id'];

// กำหนดค่า status และ origin_id
$status = 1;
$origin_id = 2;

// ตรวจสอบข้อมูลก่อนบันทึก
if (empty($appointment_date) || empty($appointment_time) || empty($channel_id)) {
    echo "กรุณากรอกข้อมูลให้ครบถ้วน!";
    exit();
}

// ตรวจสอบว่า std_id มีใน consultation_recipients หรือไม่
$query_check = "SELECT hn_id FROM consultation_recipients WHERE std_id = ?";
$stmt_check = $connect->prepare($query_check);
$stmt_check->bind_param("i", $std_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $row = $result_check->fetch_assoc();
    $hn_id = $row['hn_id'];
} else {
    $hn_id = NULL;
}

// บันทึกข้อมูลลงในตาราง consultation
$sql_insert = "INSERT INTO consultation (event_date, event_time, channel_id, std_id, hn_id, status, origin_id) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $connect->prepare($sql_insert);
$stmt_insert->bind_param("ssiiiii", $appointment_date, $appointment_time, $channel_id, $std_id, $hn_id, $status, $origin_id);

if ($stmt_insert->execute()) {
    // รับค่า app_id ที่เพิ่งถูกเพิ่มเข้าไป
    $app_id = $stmt_insert->insert_id;

    // เพิ่มข้อมูลลงในตาราง calendar
    $sql_calendar = "INSERT INTO calendar (app_id, event_date, start_time, end_time) 
                     VALUES (?, ?, ?, ?)";
    $stmt_calendar = $connect->prepare($sql_calendar);
    $stmt_calendar->bind_param("isss", $app_id, $appointment_date, $appointment_time, $appointment_time);

    if ($stmt_calendar->execute()) {
        // ไม่ echo อะไรเพื่อไม่ให้เกิด output ก่อน header()
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกลง calendar: " . $stmt_calendar->error;
    }
    $stmt_calendar->close();

    // เก็บข้อมูลที่จำเป็นสำหรับการส่งอีเมลลงใน session
    $_SESSION['std_id'] = $std_id;
    $_SESSION['appointment_date'] = $appointment_date;
    $_SESSION['appointment_time'] = $appointment_time;
    $_SESSION['channel_id'] = $channel_id;

    // เปลี่ยนหน้าไปยังไฟล์ส่งอีเมล (send_email.php)
    header("Location: send_email.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt_insert->error;
}

$stmt_check->close();
$stmt_insert->close();
$connect->close();
?>
