<?php
// เริ่ม session และกำหนด header JSON
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// เชื่อมต่อกับฐานข้อมูล
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

// ตรวจสอบการเชื่อมต่อ
if (!$connect) {
    echo json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

// รับวันที่จาก URL และตรวจสอบรูปแบบ
if (isset($_GET['date']) && DateTime::createFromFormat('Y-m-d', $_GET['date'])) {
    $date = $_GET['date'];

    // ดึงข้อมูลการนัดหมายรวมถึง app_id (appointment_id)
    $sql = "
    SELECT 
        consultation.app_id,
        consultation.event_date,
        consultation.event_time,
        consultation.hn_id,
        student.std_id,
        student.first_name,
        student.last_name,
        CASE consultation.channel_id
            WHEN 1 THEN 'face to face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)'
            WHEN 2 THEN 'ช่องทางออนไลน์'
            WHEN 3 THEN 'สายด่วน'
            ELSE 'ไม่ทราบข้อมูล'
        END AS channel_text
    FROM consultation
    JOIN consultation_recipients ON consultation.hn_id = consultation_recipients.hn_id
    JOIN student ON consultation_recipients.std_id = student.std_id
    WHERE consultation.event_date = '$date'
    AND consultation.hn_id IS NOT NULL  -- ตรวจสอบให้มีค่า hn_id
    AND consultation.status != 3    
    AND consultation.status != 1     -- กรองสถานะที่เป็น 3
    ORDER BY consultation.event_time ASC";

    $result = mysqli_query($connect, $sql);

    // ตรวจสอบผลลัพธ์ของการคิวรี
    if ($result) {
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }

        // ส่งข้อมูลในรูปแบบ JSON
        echo json_encode($appointments, JSON_PRETTY_PRINT);
    } else {
        // แสดงข้อผิดพลาดหากคิวรีไม่สำเร็จ
        echo json_encode(['error' => 'Query execution failed: ' . mysqli_error($connect)]);
    }
} else {
    echo json_encode(['error' => 'Invalid or missing date']);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$connect->close();
