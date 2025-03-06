<?php
header('Content-Type: application/json');
include '../../dbconnect.php';

try {
    // คำสั่ง SQL เพื่อดึงข้อมูลสาขาที่มีอยู่จากตาราง student
    $sql = "SELECT DISTINCT major 
            FROM student 
            ORDER BY major";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error in query: " . $conn->error);
    }

    // สร้าง array สำหรับเก็บข้อมูลสาขาที่ได้จากฐานข้อมูล
    $majors = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $majors[] = [
                "major" => $row['major'] // เก็บค่าของ major
            ];
        }
    }

    // ส่งข้อมูลกลับเป็น JSON
    echo json_encode($majors);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching majors.']);
} finally {
    $conn->close();
}
?>
