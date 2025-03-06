<?php
session_start();
header('Content-Type: application/json');

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

// ตรวจสอบการเชื่อมต่อ
if (!$connect) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// ดึงข้อมูลการจองจากฐานข้อมูล
$sql = "SELECT event_date, start_time, end_time FROM calendar"; // ใช้ table calendar
$result = $connect->query($sql);

// ตรวจสอบว่ามีผลลัพธ์หรือไม่
$appointments = array();
if ($result) {
    if ($result->num_rows > 0) {
        // เก็บข้อมูลการนัดหมายใน array
        while ($row = $result->fetch_assoc()) {
            // ตรวจสอบเงื่อนไขเวลาพิเศษ
            $status = ($row['start_time'] === '08:00:00' && $row['end_time'] === '17:00:00') ? 'unavailable' : 'available';

            $appointments[] = array(
                'date' => $row['event_date'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'status' => $status // เพิ่มฟิลด์สถานะ
            );
        }
    }
    // ปิดการเชื่อมต่อฐานข้อมูล
    $result->close();
} else {
    echo json_encode(['error' => 'Query failed: ' . $connect->error]);
    exit;
}

$connect->close();

// ส่งข้อมูลการนัดหมายในรูปแบบ JSON
echo json_encode($appointments);
?>
