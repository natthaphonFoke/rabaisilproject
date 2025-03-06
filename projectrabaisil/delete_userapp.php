<?php
include 'connect.php'; // เชื่อมต่อฐานข้อมูล

// รับข้อมูล JSON จากคำขอ
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['app_id'])) {
    $app_id = intval($data['app_id']);

    // เริ่ม Transaction
    $connect->begin_transaction();

    try {
        // ลบข้อมูลจากตาราง calendar ก่อน
        $delete_calendar_query = "DELETE FROM calendar WHERE app_id = ?";
        $stmt_calendar = $connect->prepare($delete_calendar_query);
        $stmt_calendar->bind_param("i", $app_id);
        $stmt_calendar->execute();
        $stmt_calendar->close();

        // ลบข้อมูลจากตาราง consultation
        $delete_consultation_query = "DELETE FROM consultation WHERE app_id = ?";
        $stmt_consultation = $connect->prepare($delete_consultation_query);
        $stmt_consultation->bind_param("i", $app_id);
        $stmt_consultation->execute();
        $stmt_consultation->close();

        // ถ้าทุกอย่างสำเร็จให้ commit
        $connect->commit();

        echo json_encode(['success' => true, 'message' => 'ลบข้อมูลสำเร็จ']);
    } catch (Exception $e) {
        // ถ้ามีข้อผิดพลาดให้ rollback กลับไปจุดก่อนลบ
        $connect->rollback();
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มี app_id ที่ระบุ']);
}

$connect->close();
?>
