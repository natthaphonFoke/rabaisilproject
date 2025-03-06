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
$channel_id = $_SESSION['channel'];

/*echo $appointment_date ,
$appointment_time ,
$channel_id;*/

// เชื่อมต่อกับฐานข้อมูล
include 'connect.php';

// โหลด PHPMailer และใช้ namespace (คำสั่ง use ต้องอยู่ด้านบนสุด)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ดึงอีเมลของนักศึกษาจากตาราง student โดยใช้ std_id
$query_email = "SELECT mail FROM student WHERE std_id = ?";
$stmt_email = $connect->prepare($query_email);
$stmt_email->bind_param("i", $std_id);
$stmt_email->execute();
$result_email = $stmt_email->get_result();
if ($result_email->num_rows > 0) {
    $row_email = $result_email->fetch_assoc();
    $student_email = $row_email['mail'];
} else {
    $student_email = "";
}
if ($channel_id == 1) {
    $channel_text = "face to face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)";
} elseif ($channel_id == 2) {
    $channel_text = "ออนไลน์";
} elseif ($channel_id == 3) {
    $channel_text = "สายด่วน";
} 
$stmt_email->close();



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

    $mail->addAddress($student_email);
    $mail->isHTML(true);
    $mail->CharSet = "UTF-8";
    $mail->Subject = "นัดหมายเปลี่ยนแปลง";
    $mail->Body = "เปลี่ยนแปลงนัดหมายเป็นวันที่: $appointment_date<br>
                   เวลา: $appointment_time<br>
                   ช่องทาง: $channel_text ";

    $mail->send();
    header("Location: app.php");
    exit();
} catch (Exception $e) {
   // echo "เกิดข้อผิดพลาดในการส่งอีเมล: " . $mail->ErrorInfo;
   header("Location: app.php");
}

$connect->close();
?>
