<?php
header('Content-Type: application/json');
include '../../dbconnect.php';

try {
    $sql = "SELECT DISTINCT f.faculty_id, f.faculty_name 
            FROM student s
            JOIN faculties f ON s.faculty_id = f.faculty_id
            JOIN consultation_recipients cr ON s.std_id = cr.std_id
            ORDER BY f.faculty_id";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error in query: " . $conn->error);
    }
    
    $faculties = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $faculties[] = [
                "id" => $row['faculty_id'],
                "name" => $row['faculty_name']
            ];
        }
    }

    echo json_encode($faculties);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching faculties.']);
} finally {
    $conn->close();
}
?>
