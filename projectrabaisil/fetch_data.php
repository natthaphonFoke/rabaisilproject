<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

if (!$connect) {
    echo json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

// รับค่าพารามิเตอร์
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$year = isset($_GET['year']) ? mysqli_real_escape_string($connect, $_GET['year']) : '';
$faculty = isset($_GET['faculty']) ? mysqli_real_escape_string($connect, $_GET['faculty']) : '';

// เขียนเงื่อนไข SQL
$sql = "
    SELECT 
        cr.hn_id, 
        s.std_id, 
        s.first_name, 
        s.last_name,
        f.faculty_name
    FROM 
        consultation_recipients AS cr
    INNER JOIN 
        student AS s
    ON 
        cr.std_id = s.std_id
    INNER JOIN 
        faculties AS f
    ON 
        s.faculty_id = f.faculty_id
    WHERE 1 = 1
";

// เพิ่มเงื่อนไขตามพารามิเตอร์ (ถ้ามี)
if (!empty($search)) {
    $sql .= " AND (s.std_id LIKE '%$search%' OR s.first_name LIKE '%$search%' OR s.last_name LIKE '%$search%' OR cr.hn_id LIKE '%$search%')";
}
if (!empty($year)) {
    $sql .= " AND s.year = '$year'";
}
if (!empty($faculty)) {
    $sql .= " AND f.faculty_name = '$faculty'"; // ใช้ faculty_name ในการกรอง
}

$result = mysqli_query($connect, $sql);

// เตรียมข้อมูลส่งกลับเป็น JSON
$records = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
}

echo json_encode($records);
mysqli_close($connect);
?>
