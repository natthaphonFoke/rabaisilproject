<?php
header('Content-Type: application/json');
include '../../dbconnect.php';

try {
    // คำสั่ง SQL เพื่อดึงข้อมูลปีการศึกษาที่มีอยู่จากตาราง student
    $sql = "SELECT DISTINCT year 
            FROM student 
            ORDER BY year";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error in query: " . $conn->error);
    }

    // สร้าง array สำหรับเก็บข้อมูลปีการศึกษาที่ได้จากฐานข้อมูล
    $years = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $years[] = [
                "year" => $row['year'] // เก็บค่าของ year
            ];
        }
    }

    // ส่งข้อมูลกลับเป็น JSON
    echo json_encode($years);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching years.']);
} finally {
    $conn->close();
}
?>
