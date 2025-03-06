<?php
include '../../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_id = $_POST['survey_id'];
    $question_text = $_POST['question_text'];

    $sql = "INSERT INTO questions (survey_id, question_text) VALUES ($survey_id, '$question_text')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['message' => 'เพิ่มคำถามสำเร็จ']);
    } else {
        echo json_encode(['message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
    }
}
?>
