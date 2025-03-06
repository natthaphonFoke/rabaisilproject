<?php
// เชื่อมต่อฐานข้อมูล
include('connect.php');

// รับค่า `app_id` และข้อมูลจากฟอร์ม
$app_id = $_GET['app_id']; // รับ `app_id` จาก URL
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$channel = $_POST['channel'];

// เริ่ม session
session_start();

// เก็บข้อมูลใน session
$_SESSION['app_id'] = $app_id;
$_SESSION['appointment_date'] = $appointment_date;
$_SESSION['appointment_time'] = $appointment_time;
$_SESSION['channel'] = $channel;

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if (isset($_POST['submit'])) {
    // เริ่ม transaction
    mysqli_begin_transaction($connect);

    try {
        // อัพเดตข้อมูลในตาราง consultation
        $query = "UPDATE consultation 
                  SET event_date = ?, event_time = ?, channel_id = ?, status = 2 
                  WHERE app_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("sssi", $appointment_date, $appointment_time, $channel, $app_id);
        $stmt->execute();

        // อัพเดต event_date, start_time และ end_time ในตาราง calendar
        $update_calendar_query = "UPDATE calendar 
                                  SET event_date = ?, start_time = ?, end_time = ? 
                                  WHERE app_id = ?";
        $stmt2 = $connect->prepare($update_calendar_query);
        $stmt2->bind_param("sssi", $appointment_date, $appointment_time, $appointment_time, $app_id);
        $stmt2->execute();

        // ยืนยันการเปลี่ยนแปลง
        mysqli_commit($connect);
        
        // เปลี่ยนเส้นทางไปยัง sendemail.php
        header("Location: send_email_edit.php");
        exit();
    } catch (Exception $e) {
        // ยกเลิกการเปลี่ยนแปลงหากเกิดข้อผิดพลาด
        mysqli_rollback($connect);
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
