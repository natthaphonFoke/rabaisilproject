<?php
/*if($open_connect != 1){
    die(header('Location: appointment.php'));
}*/

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';
$port = NULL;
$socket = NULL;
$connect = mysqli_connect($hostname, $username, $password, $database);

if(!$connect){
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว" . mysqli_connect_error($connect));
}
else{
    mysqli_set_charset($connect, 'utf8');

    // ตรวจสอบว่าลบข้อมูลในวันนี้แล้วหรือยัง
    session_start(); // เริ่ม session
    if (!isset($_SESSION['last_cleanup']) || time() - $_SESSION['last_cleanup'] > 86400) {
        // คำสั่ง SQL ลบข้อมูล
        $sql = "DELETE FROM consultation WHERE event_date < CURDATE() AND consult_case IS NULL";

        // ดำเนินการลบข้อมูล
        if (mysqli_query($connect, $sql)) {
            // บันทึกเวลาที่ลบข้อมูลครั้งล่าสุด
            $_SESSION['last_cleanup'] = time();
        }
    }
}
?>
