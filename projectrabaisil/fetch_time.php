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

// เก็บเวลาที่ถูกจอง
$unavailable_times = [];

// 1️⃣ ดึงข้อมูลจากตาราง calendar (ช่วงเวลา)
$sql_calendar = "SELECT event_date, start_time, end_time FROM calendar";
$result_calendar = $connect->query($sql_calendar);

if ($result_calendar) {
    while ($row = $result_calendar->fetch_assoc()) {
        $date = $row['event_date'];
        $start_time = strtotime($row['start_time']);
        $end_time = strtotime($row['end_time']);

        // ไล่ช่วงเวลาเป็นช่วงละ 30 นาที
        for ($time = $start_time; $time < $end_time; $time += 1800) {
            $formatted_time = date('H:i:s', $time);
            $unavailable_times[$date][$formatted_time] = 'unavailable';
        }
    }
}

// 2️⃣ ดึงข้อมูลจากตาราง consultation (เวลาที่ถูกจอง)
$sql_consult = "SELECT event_date, event_time FROM consultation";
$result_consult = $connect->query($sql_consult);

if ($result_consult) {
    while ($row = $result_consult->fetch_assoc()) {
        $date = $row['event_date'];
        $time = $row['event_time'];
        $unavailable_times[$date][$time] = 'unavailable';
    }
}

// 3️⃣ เตรียมส่งข้อมูล JSON
$appointments = [];
foreach ($unavailable_times as $date => $times) {
    foreach ($times as $time => $status) {
        $appointments[] = [
            'date' => $date,
            'time' => $time,
            'status' => $status
        ];
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$connect->close();

// ส่งข้อมูล JSON
echo json_encode($appointments, JSON_PRETTY_PRINT);
?>
