<?php
session_start();
session_start();

// ตรวจสอบว่ามีข้อมูลที่จำเป็นสำหรับการส่งอีเมลหรือไม่
if (!isset($_SESSION['std_id'])) {
    header("Location: app.php");
    exit();
}

$std_id = $_SESSION['std_id'];
$appointment_date = $_SESSION['appointment_date'];
$appointment_time = $_SESSION['appointment_time'];
$channel_id = $_SESSION['channel_id'];

// เชื่อมต่อกับฐานข้อมูล
include 'connect.php';

// โหลด PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ดึงอีเมลของนักศึกษา
$query_email = "SELECT mail FROM student WHERE std_id = ?";
$stmt_email = $connect->prepare($query_email);
$stmt_email->bind_param("i", $std_id);
$stmt_email->execute();
$result_email = $stmt_email->get_result();
$row_email = $result_email->fetch_assoc();
$stmt_email->close();

$student_email = $row_email['mail'] ?? null; // ใช้ null ถ้าไม่มีอีเมล

// กำหนดข้อความช่องทาง
$channel_text = match ($channel_id) {
    1 => "face to face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)",
    2 => "ออนไลน์",
    3 => "สายด่วน",
    default => "ไม่ระบุ",
};

// ส่งอีเมลถ้ามีอีเมล
if ($student_email) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rabaisil.su@gmail.com'; 
        $mail->Password = 'obquusohzemrgkyr';         
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('rabaisil.su@gmail.com', 'Rabaisil System');

        $mail->addAddress($student_email);
        $mail->isHTML(true);
        $mail->CharSet = "UTF-8";
        $mail->Subject = "นัดหมายของคุณ";
        $mail->Body = "ในวันที่: $appointment_date<br>
                       เวลา: $appointment_time<br>
                       ช่องทาง: $channel_text ";

        $mail->send();
    } catch (Exception $e) {
        // ไม่ต้องแสดง error
    }
}

// ปิดการเชื่อมต่อและกลับไปที่หน้า app.php
$connect->close();
header("Location: app.php");
exit();

?>
