<?php
require '../../dbconnect.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="filtered_data.csv"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // ป้องกันภาษาไทยเพี้ยน

// Header ของไฟล์ CSV
fputcsv($output, ['รหัสนักศึกษา', 'ชื่อ', 'นามสกุล', 'คณะ', 'เพศ', 'เบอร์โทร'], ',');

// รับค่าการค้นหาจาก JavaScript
$inputJSON = file_get_contents('php://input');
$inputData = json_decode($inputJSON, true);
$search = isset($inputData['search']) ? $inputData['search'] : "all";

if ($search === "all") {
    $sql = "SELECT std_id, first_name, last_name, faculty_id, gender, phone FROM student";
} else {
    $sql = "SELECT std_id, first_name, last_name, faculty_id, gender, phone FROM student 
            WHERE std_id LIKE '%$search%'
               OR first_name LIKE '%$search%'
               OR last_name LIKE '%$search%'
               OR faculty_id LIKE '%$search%'
               OR phone LIKE '%$search%'";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['gender'] = ($row['gender'] == 1) ? 'ชาย' : 'หญิง';
        fputcsv($output, $row, ',');
    }
}

fclose($output);
exit();
?>
