<?php
// เริ่มต้นเซสชั่น
session_start();
header('Content-Type: application/json'); // กำหนดประเภทข้อมูลเป็น JSON

// เชื่อมต่อกับฐานข้อมูล
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

// เชื่อมต่อกับฐานข้อมูลด้วย OOP
$connect = new mysqli($hostname, $username, $password, $database);

// ตรวจสอบการเชื่อมต่อ
if ($connect->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $connect->connect_error]));
}

if (isset($_POST['std_id'])) {
    $student_id = $_POST['std_id'];

    // ตรวจสอบรูปแบบรหัสนักศึกษา
    if (preg_match('/^\d{9}$/', $student_id)) {
        $admission_year = '25' . substr($student_id, 0, 2);
        $faculty_code = substr($student_id, 2, 2);
    } elseif (preg_match('/^\d{8}$/', $student_id)) {
        $faculty_code = substr($student_id, 0, 2);
        $admission_year = '25' . substr($student_id, 2, 2);
    } else {
        echo json_encode(['error' => 'รหัสนักศึกษาไม่ถูกต้อง']);
        exit;
    }

    // ค้นหาข้อมูลคณะ
    $sql = "SELECT faculty_name FROM faculties WHERE faculty_id = ?";
    $stmt = $connect->prepare($sql);

    if ($stmt === false) {
        die(json_encode(['error' => 'SQL error: ' . $connect->error]));
    }

    $stmt->bind_param('s', $faculty_code);
    $stmt->execute();
    $stmt->bind_result($faculty_name);
    $stmt->fetch();
    $stmt->close();

    if ($faculty_name) {
        echo json_encode(['faculty' => $faculty_name, 'admissionYear' => $admission_year]);
    } 
} else {
    echo json_encode(['error' => 'ไม่พบรหัสนักศึกษา']);
}

$connect->close();

?>
