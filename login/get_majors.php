<?php
include '../dbconnect.php';

$faculty_id = $_GET['faculty_id'];
$sql = "SELECT major_name FROM major WHERE faculty_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

$majors = [];
while ($row = $result->fetch_assoc()) {
    $majors[] = $row;
}

echo json_encode($majors);

$stmt->close();
$conn->close();

?>
