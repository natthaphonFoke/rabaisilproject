<?php
include '../../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['question_id'];

    $sql = "DELETE FROM questions WHERE question_id = $question_id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['message' => 'ลบคำถามสำเร็จ']);
    } else {
        echo json_encode(['message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
    }
}
?>
