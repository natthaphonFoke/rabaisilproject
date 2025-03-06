<?php
session_start();
//ปุ่มลบ
// ตรวจสอบว่า std_id ถูกตั้งค่าใน session หรือไม่
if (!isset($_SESSION['std_id'])) {
    echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วนสำหรับการส่งอีเมล']);
    exit();
}

$std_id = $_SESSION['std_id'];

// เชื่อมต่อฐานข้อมูล
include 'connect.php';

// โหลด PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// 🔹 ดึงอีเมลของนักศึกษา
$query_email = "SELECT mail FROM student WHERE std_id = ?";
$stmt_email = $connect->prepare($query_email);
$stmt_email->bind_param("s", $std_id);
$stmt_email->execute();
$result_email = $stmt_email->get_result();

if ($result_email->num_rows > 0) {
    $row_email = $result_email->fetch_assoc();
    $student_email = $row_email['mail'];
} else {
    echo json_encode(['success' => false, 'error' => 'ไม่พบอีเมลของนักศึกษา']);
    exit();
}

$stmt_email->close();

// 🔹 ตั้งค่าและส่งอีเมล
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'rabaisil.su@gmail.com'; 
    $mail->Password = 'obquusohzemrgkyr';  // 
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('rabaisil.su@gmail.com', 'Rabaisil System');
    $mail->addAddress($student_email);
    $mail->isHTML(true);
    $mail->CharSet = "UTF-8";
    $mail->Subject = "มีการเปลี่ยนแปลงนัดหมาย";
    $mail->Body = "โปรดตรวจสอบและเลือกเวลานัดหมายใหม่ <br> 
                หรือติดต่อมาที่ <a href='https://www.facebook.com/SilpakornPsycho'>Facebook</a>";

    $mail->send();

    // ส่งการตอบกลับในรูปแบบ JSON
    header("Location: {$_SERVER['HTTP_REFERER']}");
} catch (Exception $e) {
   // echo json_encode(['success' => false, 'error' => 'ส่งอีเมลไม่สำเร็จ: ' . $mail->ErrorInfo]);
   header("Location: {$_SERVER['HTTP_REFERER']}");
}

$connect->close();
?>
