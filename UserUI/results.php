<?php
session_start();
include '../dbconnect.php';

if (!isset($_SESSION['student_id'])) {
    die("กรุณาเข้าสู่ระบบก่อนทำแบบทดสอบ");
}

$std_id = intval($_SESSION['student_id']);
$survey_id = isset($_POST['survey_id']) ? intval($_POST['survey_id']) : 0;
$responses = isset($_POST['response']) ? $_POST['response'] : [];

if ($survey_id === 0 || empty($responses) || !is_array($responses)) {
    header("Location: survey.php?error=invalid_data");
    exit();
}

$total_score = array_sum($responses);
if ($total_score <= 5) {
    $risk_level = "ปกติ";
} elseif ($total_score <= 10) {
    $risk_level = "ต่ำ";
} elseif ($total_score <= 15) {
    $risk_level = "ปานกลาง";
} elseif ($total_score <= 20) {
    $risk_level = "รุนแรง";
} else {
    $risk_level = "รุนแรงที่สุด";
}

try {
    $stmt = $conn->prepare("INSERT INTO test_results (std_id, survey_id, total_score, severity) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiis", $std_id, $survey_id, $total_score, $risk_level);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // ส่งข้อมูลผ่าน Query String
    header("Location: result_summary.php?survey_id=$survey_id&total_score=$total_score&risk_level=" . urlencode($risk_level));
    exit();

} catch (Exception $e) {
    error_log($e->getMessage());
    die("เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองอีกครั้ง");
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
