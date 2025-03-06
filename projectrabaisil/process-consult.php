<?php
session_start();
header('Content-Type: application/json');

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

if (!$connect) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์มและป้องกัน SQL Injection
    $hn_id = !empty($_POST['hn_id']) ? mysqli_real_escape_string($connect, $_POST['hn_id']) : 'ไม่มี';
    $app_id = !empty($_POST['app_id']) ? mysqli_real_escape_string($connect, $_POST['app_id']) : '';
    $consult_case = !empty($_POST['consult_case']) && is_array($_POST['consult_case'])
        ? mysqli_real_escape_string($connect, implode(',', $_POST['consult_case']))
        : 'ไม่มี';
    $consult_des = !empty($_POST['consult_des']) ? mysqli_real_escape_string($connect, $_POST['consult_des']) : 'ไม่มี';
    $advice = !empty($_POST['advice']) ? mysqli_real_escape_string($connect, $_POST['advice']) : 'ไม่มี';
    $test_results = !empty($_POST['test_results']) ? mysqli_real_escape_string($connect, $_POST['test_results']) : 'ไม่มี';
    $follow_des = !empty($_POST['follow_des']) ? mysqli_real_escape_string($connect, $_POST['follow_des']) : 'ไม่มี';
    $follow_id = !empty($_POST['follow_id']) && is_numeric($_POST['follow_id'])
        ? mysqli_real_escape_string($connect, $_POST['follow_id'])
        : '0';
    $forward_id = !empty($_POST['forward_id']) ? mysqli_real_escape_string($connect, $_POST['forward_id']) : 'ไม่มี';
    $forward_des = !empty($_POST['forward_des']) ? mysqli_real_escape_string($connect, $_POST['forward_des']) : 'ไม่มี';
    $symptoms = !empty($_POST['symptoms']) ? mysqli_real_escape_string($connect, $_POST['symptoms']) : 'ไม่มี';

    // ตรวจสอบว่ามี user_name ใน session หรือไม่
    $admin = isset($_SESSION['user_name']) ? mysqli_real_escape_string($connect, $_SESSION['user_name']) : 'ไม่มี';

    // ตรวจสอบว่า app_id มีค่าหรือไม่
    if (empty($app_id)) {
        echo json_encode(['error' => 'app_id ไม่สามารถเว้นว่างได้']);
        exit;
    }

    // กำหนดค่า status เป็น 4
    $status = 4;

    // ใช้ Prepared Statement สำหรับอัปเดตข้อมูล
    $stmt = $connect->prepare("UPDATE consultation
                              SET 
                                  consult_case = ?, 
                                  consult_des = ?, 
                                  advice = ?, 
                                  test_results = ?, 
                                  follow_des = ?, 
                                  follow_id = ?, 
                                  forward_id = ?, 
                                  forward_des = ?, 
                                  symptoms = ?,
                                  status = ?,
                                  admin = ?
                              WHERE app_id = ?");
    $stmt->bind_param(
        "ssssssissisi",
        $consult_case,
        $consult_des,
        $advice,
        $test_results,
        $follow_des,
        $follow_id,
        $forward_id,
        $forward_des,
        $symptoms,
        $status,
        $admin, // เพิ่มค่า admin ที่มาจาก session
        $app_id
    );

    if ($stmt->execute()) {
        header("Location: detail_consult.php?hn_id=$hn_id&app_id=$app_id");
        exit;
    } else {
        echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $stmt->error]);
    }
    $stmt->close();
}

// ปิดการเชื่อมต่อ
mysqli_close($connect);
