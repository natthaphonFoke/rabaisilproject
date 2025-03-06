<?php
header('Content-Type: application/json');
include '../../dbconnect.php';

$major = isset($_GET['major']) ? $_GET['major'] : ''; // รับค่า major จาก query string

try {
    // คำสั่ง SQL ดึงข้อมูลตามสาขาที่เลือก
    $sql = "SELECT c.follow_id, COUNT(*) as count 
            FROM consultation c
            LEFT JOIN student s ON c.std_id = s.std_id
            LEFT JOIN consultation_recipients cr ON s.std_id = cr.std_id";
    
    // ถ้ามีการเลือกสาขา (major) จะเพิ่มเงื่อนไข WHERE
    $params = [];
    $types = "";

    if (!empty($major)) {
        $sql .= " WHERE s.major = ?";
        $params[] = $major;
        $types .= "s";  // ระบุชนิดข้อมูลที่ใช้ใน parameter (s = string)
    }

    $sql .= " GROUP BY c.follow_id"; // Group by follow_id

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    if (!empty($major)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Error getting result: " . $stmt->error);
    }

    $data = [1 => 0, 2 => 0, 3 => 0, 4 => 0]; // ค่าเริ่มต้น

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $follow_id = $row['follow_id'] ?? 0;
            $data[$follow_id] = $row['count'];
        }
    }

    echo json_encode($data); // ส่งข้อมูลกลับเป็น JSON

    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching data.']);
} finally {
    $conn->close();
}
?>
