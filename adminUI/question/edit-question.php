<?php
include '../../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['question_id'];
    $question_text = $_POST['question_text'];

    $sql = "UPDATE questions SET question_text = '$question_text' WHERE question_id = $question_id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'แก้ไขคำถามสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
    }
}
?>
