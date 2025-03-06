<?php
include('connect.php');

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // ตรวจสอบข้อมูลที่ได้รับ
    if (!empty($event_date) && !empty($start_time) && !empty($end_time)) {
        // คำสั่ง SQL สำหรับบันทึกข้อมูล
        $sql = "INSERT INTO calendar (event_date, start_time, end_time) VALUES (?, ?, ?)";

        if ($stmt = $connect->prepare($sql)) {
            $stmt->bind_param("sss", $event_date, $start_time, $end_time);
            if ($stmt->execute()) {
                header('Location: http://localhost/projectrabaisil/day-off.php');
                exit();
            } else {
                echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>ไม่สามารถเตรียมคำสั่ง SQL ได้</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>กรุณากรอกข้อมูลให้ครบถ้วน</div>";
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$connect->close();
?>
