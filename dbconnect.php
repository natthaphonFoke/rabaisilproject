<?php
try {
    // กำหนดค่าการเชื่อมต่อฐานข้อมูล
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'rabaisrin_db');

    // สร้างการเชื่อมต่อ
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // ตั้งค่า charset เป็น utf8mb4 (ดีกว่า utf8)
    $conn->set_charset("utf8mb4");
    
    // ตรวจสอบการเชื่อมต่อ
    if ($conn->connect_error) {
        throw new Exception("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
    }

} catch (Exception $e) {
    // บันทึก error ลงใน log
    error_log($e->getMessage());
    // แสดงข้อความ error ที่เหมาะสมกับผู้ใช้
    exit("ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้");
}
?>
