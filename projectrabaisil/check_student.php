<?php  
session_start();
header('Content-Type: application/json'); // กำหนดให้ตอบกลับเป็น JSON

// ปิดการแสดงผล error message ของ PHP บนหน้าเว็บ (หรือเปิดใช้งานเมื่อดีบัก)
ini_set('display_errors', 0); // ตั้งค่าเป็น 1 เมื่อดีบักเพื่อตรวจสอบ
ini_set('display_startup_errors', 0);

// ตั้งค่า Error Reporting ให้สูงสุดเมื่อดีบัก
error_reporting(E_ALL); // ตั้งค่าเป็น 0 เพื่อปิดเมื่อใช้จริง

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

if (!$connect) {
    // ถ้าเชื่อมต่อฐานข้อมูลไม่ได้ ให้ตอบกลับ JSON ที่มี error
    echo json_encode(['exists' => false, 'error' => 'ไม่สามารถเชื่อมต่อกับฐานข้อมูลได้']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['std_id'])) {
        echo json_encode(['exists' => false, 'error' => 'No student ID received']);
        exit;
    }

    $std_id = $_POST['std_id'];
    
    // ตรวจสอบรหัสนักศึกษา และดึงข้อมูลปีการศึกษาและรหัสคณะ
    if (strlen($std_id) === 9) {
        // ถ้ารหัส 9 หลัก ใช้ 2 หลักแรกเป็นปีการศึกษา และ 2 หลักถัดไปเป็นรหัสคณะ
        $admissionYear = substr($std_id, 0, 2); // ปีการศึกษา
        $facultyId = substr($std_id, 2, 2); // รหัสคณะ
    } elseif (strlen($std_id) === 8) {
        // ถ้ารหัส 8 หลัก ใช้ 2 หลักแรกเป็นรหัสคณะ และ 2 หลักถัดไปเป็นปีการศึกษา
        $facultyId = substr($std_id, 0, 2); // รหัสคณะ
        $admissionYear = substr($std_id, 2, 2); // ปีการศึกษา
    } else {
        // ถ้ารหัสนักศึกษาไม่ใช่ 8 หรือ 9 หลัก
        echo json_encode(['exists' => false, 'error' => 'รหัสนักศึกษาผิดรูปแบบ']);
        exit;
    }

    // คิวรีข้อมูลนักศึกษาจากฐานข้อมูล
    $query = "SELECT * FROM student WHERE std_id = ?";
    if ($stmt = $connect->prepare($query)) {
        $stmt->bind_param('s', $std_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                
                // คิวรีชื่อคณะจากรหัสคณะ
                $facultyQuery = "SELECT faculty_name FROM faculties WHERE faculty_id = ?";
                if ($facultyStmt = $connect->prepare($facultyQuery)) {
                    $facultyStmt->bind_param('s', $facultyId);
                    $facultyStmt->execute();
                    $facultyResult = $facultyStmt->get_result();
                    $facultyName = $facultyResult->num_rows > 0 ? $facultyResult->fetch_assoc()['faculty_name'] : 'ไม่พบชื่อคณะ';
                } else {
                    $facultyName = 'ไม่สามารถดึงข้อมูลคณะ';
                }
                
                // ส่งข้อมูลกลับ
                echo json_encode([
                    'exists' => true,
                    'name' => $student['first_name'],
                    'surname' => $student['last_name'],
                    'nickname' => $student['nickname'],
                    'phone' => $student['phone'],
                    'major' => $student['major'],
                    'admissionYear' => $admissionYear,
                    'facultyId' => $facultyId,
                    'facultyName' => $facultyName, // ชื่อคณะ
                    'gender' => $student['gender'] // ข้อมูลเพศ
                ]);
            } else {
                echo json_encode(['exists' => false, 'error' => 'ไม่พบข้อมูลนักศึกษานี้']);
            }
        } else {
            echo json_encode(['exists' => false, 'error' => 'Query execution failed']);
        }
    } else {
        echo json_encode(['exists' => false, 'error' => 'Statement preparation failed']);
    }
}
?>
