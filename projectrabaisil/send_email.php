<?php
session_start();

// ตรวจสอบว่ามีข้อมูลที่จำเป็นสำหรับการส่งอีเมลหรือไม่
if (!isset($_SESSION['std_id'])) {
    echo "ข้อมูลไม่ครบถ้วนสำหรับการส่งอีเมล";
    exit();
}

$std_id = $_SESSION['std_id'];
$appointment_date = $_SESSION['appointment_date'];
$appointment_time = $_SESSION['appointment_time'];
$channel_id = $_SESSION['channel_id'];

// เชื่อมต่อกับฐานข้อมูล
include 'connect.php';

// โหลด PHPMailer และใช้ namespace (คำสั่ง use ต้องอยู่ด้านบนสุด)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// กำหนดข้อความช่องทางการนัดหมาย
$channel_text = "";
if ($channel_id == 1) {
    $channel_text = "face to face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)";
} elseif ($channel_id == 2) {
    $channel_text = "ออนไลน์";
} elseif ($channel_id == 3) {
    $channel_text = "สายด่วน";
}

// ดึงอีเมลของนักศึกษา
$email_list = [];
$query_email = "SELECT mail FROM student WHERE std_id = ?";
$stmt_email = $connect->prepare($query_email);
$stmt_email->bind_param("i", $std_id);
$stmt_email->execute();
$result_email = $stmt_email->get_result();
if ($result_email->num_rows > 0) {
    $row_email = $result_email->fetch_assoc();
    $email_list[] = $row_email['mail']; // เพิ่มอีเมลนักศึกษาเข้าอาร์เรย์
}
$stmt_email->close();

// ดึงอีเมลของเจ้าหน้าที่หรืออาจารย์ที่เกี่ยวข้อง
$query_staff_email = "SELECT email FROM psychologist";
$stmt_staff_email = $connect->prepare($query_staff_email);
$stmt_staff_email->execute();
$result_staff_email = $stmt_staff_email->get_result();
while ($row_staff = $result_staff_email->fetch_assoc()) {
    $email_list[] = $row_staff['email']; // เพิ่มอีเมลของเจ้าหน้าที่เข้าอาร์เรย์
}
$stmt_staff_email->close();

// สร้างและส่งอีเมลแจ้งเตือน
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

    // วนลูปเพิ่มอีเมลผู้รับทั้งหมด
    foreach ($email_list as $email) {
        $mail->addAddress($email);
    }

    $mail->isHTML(true);
    $mail->CharSet = "UTF-8";
    $mail->Subject = "รายการคำขอนัดหมายใหม่";
    $mail->Body = "มีรายการคำขอนัดหมายใหม่ในวันที่: $appointment_date<br>
                   เวลา: $appointment_time<br>
                   ช่องทาง: $channel_text";

    $mail->send();
    header("Location: user_appdetails.php");
    exit();
} catch (Exception $e) {
    //echo "เกิดข้อผิดพลาดในการส่งอีเมล: " . $mail->ErrorInfo;
    header("Location: user_appdetails.php");
}

$connect->close();

?>
