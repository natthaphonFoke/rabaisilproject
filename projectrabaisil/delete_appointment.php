<?php
session_start();
header('Content-Type: application/json');

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

if (!$connect) {
    echo json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing appointment ID']);
    exit;
}

$app_id = intval($_GET['id']);

mysqli_begin_transaction($connect);

try {
    // 🔹 1. ดึง hn_id จาก consultation
    $sql_hn = "SELECT hn_id FROM consultation WHERE app_id = ?";
    $stmt_hn = $connect->prepare($sql_hn);
    $stmt_hn->bind_param('i', $app_id);
    $stmt_hn->execute();
    $result_hn = $stmt_hn->get_result();

    if ($row_hn = $result_hn->fetch_assoc()) {
        $hn_id = $row_hn['hn_id'];

        // 🔹 2. ดึง std_id จาก consultation_recipients
        $sql_std = "SELECT std_id FROM consultation_recipients WHERE hn_id = ?";
        $stmt_std = $connect->prepare($sql_std);
        $stmt_std->bind_param('s', $hn_id);
        $stmt_std->execute();
        $result_std = $stmt_std->get_result();

        if ($row_std = $result_std->fetch_assoc()) {
            $std_id = $row_std['std_id'];
        } else {
            throw new Exception("ไม่พบ std_id ที่เกี่ยวข้อง");
        }
    } else {
        throw new Exception("ไม่พบ hn_id ที่เกี่ยวข้อง");
    }

    // 🔹 3. อัปเดตสถานะเป็น 3
    $sql1 = "UPDATE consultation SET status = 3 WHERE app_id = ?";
    $stmt1 = $connect->prepare($sql1);
    $stmt1->bind_param('i', $app_id);
    $stmt1->execute();

    // 🔹 4. ลบการนัดหมายใน calendar
    $sql2 = "DELETE FROM calendar WHERE app_id = ?";
    $stmt2 = $connect->prepare($sql2);
    $stmt2->bind_param('i', $app_id);
    $stmt2->execute();

    if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0) {
        mysqli_commit($connect); // ✅ ยืนยันการเปลี่ยนแปลง

        // 🔹 5. ส่งไปหน้า `send_email_cancel.php`
        header("Location: send_email_cancel.php?std_id=$std_id");
        exit();
    } else {
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'error' => 'No rows affected']);
    }

} catch (Exception $e) {
    mysqli_rollback($connect);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$connect->close();
?>
