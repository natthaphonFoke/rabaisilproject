<?php
session_start();
header('Content-Type: application/json');

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

if (!$connect) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

if (isset($_POST['faculty_id'])) {
    $faculty_id = (int)$_POST['faculty_id']; // แปลงเป็นตัวเลขเพื่อป้องกัน SQL Injection

    // Prepare statement
    $stmt = $connect->prepare("SELECT major_name FROM major WHERE faculty_id = ?");
    $stmt->bind_param("i", $faculty_id); // binding parameter

    // Execute query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $majors = [];
        while ($row = $result->fetch_assoc()) {
            $majors[] = $row['major_name']; // เก็บเฉพาะชื่อสาขา
        }
        // ส่งข้อมูลออกมาในรูปแบบ JSON
        echo json_encode(['majors' => $majors]);
    } else {
        echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลสาขาได้']);
    }
    $stmt->close(); // ปิด statement
} else {
    echo json_encode(['error' => 'ไม่พบรหัสคณะ']);
}

mysqli_close($connect);
?>
