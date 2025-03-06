<?php
header('Content-Type: application/json');
include '../../dbconnect.php';

try {
    // คำสั่ง SQL เพื่อดึงข้อมูลตาม gender
    $sql = "SELECT DISTINCT s.gender
            FROM student s
            JOIN consultation_recipients cr ON s.std_id = cr.std_id
            ORDER BY s.gender";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error in query: " . $conn->error);
    }

    $genders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // 1 = Male, 2 = Female, อาจจะเพิ่มเงื่อนไขถ้ามีเพศอื่นๆ
            $genders[] = [
                "id" => $row['gender'], // gender จะเป็น 1 หรือ 2
                "name" => ($row['gender'] == 1) ? "ชาย" : "หญิง" // เปลี่ยนเป็นชื่อเพศ
            ];
        }
    }

    echo json_encode($genders); // ส่งข้อมูลกลับเป็น JSON

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching genders.']);
} finally {
    $conn->close();
}
?>
