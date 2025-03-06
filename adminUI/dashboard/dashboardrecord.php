<?php
session_start();
include '../../dbconnect.php';

// ตรวจสอบค่าที่ส่งมาจากฟอร์ม
$selected_survey = isset($_GET['survey']) ? intval($_GET['survey']) : 0;
$selected_faculty = isset($_GET['faculty']) ? $_GET['faculty'] : '';
$selected_major = isset($_GET['major']) ? $_GET['major'] : '';
$selected_gender = isset($_GET['gender']) ? $_GET['gender'] : '0';

// ดึงข้อมูลสำหรับ Dropdown คณะ
$faculty_result = $conn->query("SELECT faculty_id, faculty_name FROM faculties");
// ตรวจสอบว่ามีคณะไหนที่ทำแบบทดสอบที่เลือกบ้าง
$sql_faculty_by_survey = "SELECT f.faculty_id, f.faculty_name, COUNT(*) AS total
                          FROM test_results tr
                          JOIN student s ON tr.std_id = s.std_id
                          JOIN faculties f ON s.faculty_id = f.faculty_id";

if ($selected_survey) {
    $sql_faculty_by_survey .= " WHERE tr.survey_id = ?";
}
$sql_faculty_by_survey .= " GROUP BY f.faculty_id, f.faculty_name HAVING total > 0";
$stmt_faculty = $conn->prepare($sql_faculty_by_survey);
if ($selected_survey) {
    $stmt_faculty->bind_param('i', $selected_survey);
}
$stmt_faculty->execute();
$result_faculty = $stmt_faculty->get_result();

// ดึงข้อมูลสำหรับ Dropdown สาขา
$major_result = $conn->query("SELECT major_id, major_name FROM major");
// ตรวจสอบว่าสาขาไหนที่ทำแบบทดสอบที่เลือกและเกี่ยวข้องกับคณะที่เลือก
$sql_major_by_survey = "SELECT m.major_id, m.major_name, COUNT(*) AS total
                        FROM test_results tr
                        JOIN student s ON tr.std_id = s.std_id
                        JOIN major m ON s.major = m.major_name";

$conditions = [];
$params = [];
$types = '';
if ($selected_survey) {
    $conditions[] = "tr.survey_id = ?";
    $params[] = $selected_survey;
    $types .= 'i';
}
if ($selected_faculty) {
    $conditions[] = "s.faculty_id = ?";
    $params[] = $selected_faculty;
    $types .= 's';
}
if ($conditions) {
    $sql_major_by_survey .= " WHERE " . implode(" AND ", $conditions);
}
$sql_major_by_survey .= " GROUP BY m.major_id, m.major_name HAVING total > 0";
$stmt_major = $conn->prepare($sql_major_by_survey);
if ($params) {
    $stmt_major->bind_param($types, ...$params);
}
$stmt_major->execute();
$result_major = $stmt_major->get_result();

// SQL Query สำหรับข้อมูลกราฟวงกลมของ "แบบทดสอบ"
$sql_survey_chart = "SELECT tr.severity, COUNT(*) AS count
                     FROM test_results tr
                     JOIN student s ON tr.std_id = s.std_id
                     JOIN faculties f ON s.faculty_id = f.faculty_id";

if ($selected_survey) {
    $sql_survey_chart .= " WHERE tr.survey_id = ?";
}
$sql_survey_chart .= " GROUP BY tr.severity";
$stmt_survey_chart = $conn->prepare($sql_survey_chart);
if ($selected_survey) {
    $stmt_survey_chart->bind_param('i', $selected_survey);
}
$stmt_survey_chart->execute();
$result_survey_chart = $stmt_survey_chart->get_result();
$survey_labels = [];
$survey_data = [];
while ($row = $result_survey_chart->fetch_assoc()) {
    $survey_labels[] = $row['severity'];
    $survey_data[] = $row['count'];
}

// SQL Query สำหรับข้อมูลกราฟวงกลมของ "คณะ"
$sql_faculty_chart = "SELECT tr.severity, COUNT(*) AS count
                      FROM test_results tr
                      JOIN student s ON tr.std_id = s.std_id
                      JOIN faculties f ON s.faculty_id = f.faculty_id";

if ($selected_faculty) {
    $sql_faculty_chart .= " WHERE f.faculty_id = ?";
}
$sql_faculty_chart .= " GROUP BY tr.severity";
$stmt_faculty_chart = $conn->prepare($sql_faculty_chart);
if ($selected_faculty) {
    $stmt_faculty_chart->bind_param('s', $selected_faculty);
}
$stmt_faculty_chart->execute();
$result_faculty_chart = $stmt_faculty_chart->get_result();
$faculty_labels = [];
$faculty_data = [];
while ($row = $result_faculty_chart->fetch_assoc()) {
    $faculty_labels[] = $row['severity'];
    $faculty_data[] = $row['count'];
}


// SQL Query สำหรับข้อมูลกราฟวงกลมของ "สาขา"
$sql_major_chart = "SELECT tr.severity, COUNT(*) AS count
                    FROM test_results tr
                    JOIN student s ON tr.std_id = s.std_id
                    JOIN major m ON s.major = m.major_name";

// เงื่อนไขสำหรับกรองข้อมูลตามชื่อสาขา
if ($selected_major && $selected_major !== "0") { // ตรวจสอบว่ามีการเลือกสาขา
    $sql_major_chart .= " WHERE m.major_name = ?";
    $params[] = $selected_major; // ใช้ตัวหนังสือ
    $types .= 's'; // 's' สำหรับข้อความ (string)
}
$sql_major_chart .= " GROUP BY tr.severity";
$stmt_major_chart = $conn->prepare($sql_major_chart);
if ($selected_major) {
    $stmt_major_chart->bind_param('s', $selected_major);
}
$stmt_major_chart->execute();
$result_major_chart = $stmt_major_chart->get_result();
// ดึงข้อมูลสำหรับกราฟ
$major_labels = [];
$major_data = [];
while ($row = $result_major_chart->fetch_assoc()) {
    $major_labels[] = $row['severity'];
    $major_data[] = $row['count'];
}




// SQL Query สำหรับดึงข้อมูลเพศ (ตามเงื่อนไขคณะและสาขา)
$sql_gender_by_filters = "
    SELECT DISTINCT 
        CASE s.gender 
            WHEN 1 THEN 'male'
            WHEN 2 THEN 'female'
            WHEN 3 THEN 'lgbtqa'
        END AS gender
    FROM student s
    JOIN test_results tr ON s.std_id = tr.std_id
    JOIN faculties f ON s.faculty_id = f.faculty_id
    JOIN major m ON s.major = m.major_name";

// เงื่อนไขสำหรับกรองข้อมูลตามคณะและสาขา
$conditions = [];
$params = [];
$types = '';

if ($selected_faculty) {
    $conditions[] = "f.faculty_id = ?";
    $params[] = $selected_faculty;
    $types .= 'i';
}
if ($selected_major && $selected_major !== "0") {
    $conditions[] = "m.major_name = ?";
    $params[] = $selected_major;
    $types .= 's';
}

if ($conditions) {
    $sql_gender_by_filters .= " WHERE " . implode(" AND ", $conditions);
}

$stmt_gender_filters = $conn->prepare($sql_gender_by_filters);

if ($params) {
    $stmt_gender_filters->bind_param($types, ...$params);
}

$stmt_gender_filters->execute();
$result_gender_filters = $stmt_gender_filters->get_result();

// เตรียมข้อมูล Dropdown เพศ
$available_genders = [];
while ($row = $result_gender_filters->fetch_assoc()) {
    $available_genders[] = $row['gender'];
}

// ปิด Statement
$stmt_gender_filters->close();







// SQL Query สำหรับกราฟแท่งของเพศ พร้อมเงื่อนไขกรอง
$sql_gender_chart = "
    SELECT 
        CASE s.gender 
            WHEN 1 THEN 'male'
            WHEN 2 THEN 'female'
            WHEN 3 THEN 'lgbtqa'
        END AS gender,
        tr.severity, 
        COUNT(*) AS count
    FROM test_results tr
    JOIN student s ON tr.std_id = s.std_id
    JOIN faculties f ON s.faculty_id = f.faculty_id
    JOIN major m ON s.major = m.major_name";

// เงื่อนไขการกรอง
$conditions = [];
$params = [];
$types = '';

if ($selected_faculty) { // กรองตามคณะ
    $conditions[] = "f.faculty_id = ?";
    $params[] = $selected_faculty;
    $types .= 'i';
}

if ($selected_major && $selected_major !== "0") { // กรองตามสาขา
    $conditions[] = "m.major_name = ?";
    $params[] = $selected_major;
    $types .= 's';
}

if ($conditions) {
    $sql_gender_chart .= " WHERE " . implode(" AND ", $conditions);
}

$sql_gender_chart .= "
    GROUP BY gender, tr.severity
    ORDER BY FIELD(gender, 'male', 'female', 'lgbtqa')";

// เตรียม Statement
$stmt_gender_chart = $conn->prepare($sql_gender_chart);

if ($params) {
    $stmt_gender_chart->bind_param($types, ...$params);
}

$stmt_gender_chart->execute();
$result_gender_chart = $stmt_gender_chart->get_result();

// เตรียมข้อมูลเริ่มต้นสำหรับเพศและความรุนแรง
$gender_data = ['male' => [], 'female' => [], 'lgbtqa' => []];
$severities = ['ปกติ', 'ปานกลาง', 'รุนแรง', 'รุนแรงที่สุด']; // ระดับความรุนแรง

// ตั้งค่าเริ่มต้นเป็น 0
foreach ($gender_data as $gender => &$data) {
    foreach ($severities as $severity) {
        $data[$severity] = 0;
    }
}

// เติมข้อมูลจากผลลัพธ์ SQL
while ($row = $result_gender_chart->fetch_assoc()) {
    $gender = $row['gender'];
    $severity = $row['severity'];
    $count = $row['count'];

    if (isset($gender_data[$gender]) && isset($gender_data[$gender][$severity])) {
        $gender_data[$gender][$severity] = $count;
    }
}


// ฟังก์ชันสำหรับดึงจำนวนรวม
function getTotalCount($conn, $sql, $types = '', $params = [])
{
    $stmt = $conn->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

// จำนวนรวมทั้งหมดใน consultation_recipients
$totalCountSql = "SELECT COUNT(*) AS total FROM consultation_recipients";
$totalCount = getTotalCount($conn, $totalCountSql);

// จำนวนรวมที่ถูกเลือกตามเงื่อนไข
$selectedCountSql = "SELECT COUNT(*) AS total FROM consultation_recipients cr
                     JOIN student s ON cr.std_id = s.std_id";
$conditions = [];
$params = [];
$types = '';

// เพิ่มเงื่อนไขการกรอง (ถ้ามี)
if (!empty($selected_faculty)) {
    $conditions[] = "s.faculty_id = ?";
    $params[] = $selected_faculty;
    $types .= 's';
}
if (!empty($selected_major) && $selected_major !== "0") {
    $conditions[] = "s.major = ?";
    $params[] = $selected_major;
    $types .= 's';
}
if (!empty($selected_gender) && $selected_gender !== "0") {
    $conditions[] = "s.gender = ?";
    $params[] = $selected_gender;
    $types .= 'i';
}

// ถ้ามีเงื่อนไข ให้เพิ่มเข้า SQL
if ($conditions) {
    $selectedCountSql .= " WHERE " . implode(" AND ", $conditions);
}

// คำนวณจำนวนที่ถูกเลือก
$selectedCount = getTotalCount($conn, $selectedCountSql, $types, $params);



// ฟังก์ชันดึงค่าจำนวนรวม
function getGenderCount($conn, $gender)
{
    $sql = "SELECT COUNT(*) AS total 
            FROM consultation_recipients cr
            JOIN student s ON cr.std_id = s.std_id
            WHERE s.gender = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $gender);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['total'] ?? 0;
}

// นับจำนวนตามเพศ
$totalMale = getGenderCount($conn, 1);    // ชาย
$totalFemale = getGenderCount($conn, 2);  // หญิง
$totalLGBTQ = getGenderCount($conn, 3);   // LGBTQ+




// จำนวนรวมทั้งหมดใน consultation_recipients
$totalCountSql = "SELECT COUNT(*) AS total FROM consultation";
$totalconsult = getTotalCount($conn, $totalCountSql);

// จำนวนรวมที่ถูกเลือกตามเงื่อนไข
$selectedCountSql = "SELECT COUNT(*) AS total FROM consultation cr
                     JOIN student s ON cr.std_id = s.std_id";
$conditions = [];
$params = [];
$types = '';

// เพิ่มเงื่อนไขการกรอง (ถ้ามี)
if (!empty($selected_faculty)) {
    $conditions[] = "s.faculty_id = ?";
    $params[] = $selected_faculty;
    $types .= 's';
}
if (!empty($selected_major) && $selected_major !== "0") {
    $conditions[] = "s.major = ?";
    $params[] = $selected_major;
    $types .= 's';
}
if (!empty($selected_gender) && $selected_gender !== "0") {
    $conditions[] = "s.gender = ?";
    $params[] = $selected_gender;
    $types .= 'i';
}

// ถ้ามีเงื่อนไข ให้เพิ่มเข้า SQL
if ($conditions) {
    $selectedCountSql .= " WHERE " . implode(" AND ", $conditions);
}

// คำนวณจำนวนที่ถูกเลือก
$selectedCount = getTotalCount($conn, $selectedCountSql, $types, $params);


// จำนวนข้อมูลต่อหน้า
$limit = 15;

// หน้าปัจจุบัน (ถ้าไม่มีให้เริ่มจากหน้า 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // ป้องกันไม่ให้ค่า page น้อยกว่า 1

// คำนวณ OFFSET
$offset = ($page - 1) * $limit;

$sql_table = "SELECT s.std_id, 
                     CONCAT(s.first_name, ' ', s.last_name) AS name, 
                     f.faculty_name, 
                     s.major AS major_name
              FROM student s
              JOIN faculties f ON s.faculty_id = f.faculty_id
              LEFT JOIN consultation_recipients cr ON s.std_id = cr.std_id
              WHERE cr.std_id IS NOT NULL";  // ตรวจสอบว่า std_id มีใน consultation_recipients


$sql_count = "SELECT COUNT(*) AS total FROM student s
              JOIN faculties f ON s.faculty_id = f.faculty_id";

// เงื่อนไขการกรอง
$conditions = [];
$params = [];
$types = '';

if (!empty($selected_faculty)) {
    $conditions[] = "f.faculty_id = ?";
    $params[] = $selected_faculty;
    $types .= 's';
}
if (!empty($selected_major) && $selected_major !== "0") {
    $conditions[] = "s.major = ?";
    $params[] = $selected_major;
    $types .= 's';
}
if (!empty($selected_gender) && $selected_gender !== "0") {
    $conditions[] = "s.gender = ?";
    $params[] = $selected_gender;
    $types .= 'i';
}

if ($conditions) {
    $where_clause = " WHERE " . implode(" AND ", $conditions);
    $sql_table .= $where_clause;
    $sql_count .= $where_clause;
}

// คำนวณจำนวนข้อมูลทั้งหมด
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_records = $result_count->fetch_assoc()['total'];

// คำนวณจำนวนหน้าทั้งหมด
$total_pages = ceil($total_records / $limit);

// แปลงค่าให้เป็น int ป้องกันข้อผิดพลาด
$limit = intval($limit);
$offset = intval($offset);

// เพิ่ม ORDER BY, LIMIT และ OFFSET ใน Query ตาราง
$sql_table .= " ORDER BY s.std_id ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// เตรียมคำสั่ง SQL สำหรับ Query ตาราง
$stmt_table = $conn->prepare($sql_table);
if (!empty($params)) {
    $stmt_table->bind_param($types, ...$params);
}
$stmt_table->execute();
$result_table = $stmt_table->get_result();



// ดึงรายการคณะที่มีนักศึกษาปรึกษา
$sql_faculty = "SELECT DISTINCT f.faculty_id, f.faculty_name 
                FROM consultation_recipients cr
                JOIN student s ON cr.std_id = s.std_id
                JOIN faculties f ON s.faculty_id = f.faculty_id
                ORDER BY f.faculty_name";
$result_faculty = $conn->query($sql_faculty);

// นับจำนวนคณะทั้งหมด (ค่าเริ่มต้น)
$sql_total_faculty = "SELECT COUNT(DISTINCT s.faculty_id) AS total 
                      FROM consultation_recipients cr
                      JOIN student s ON cr.std_id = s.std_id";
$result_total_faculty = $conn->query($sql_total_faculty);
$totalfaculty = $result_total_faculty->fetch_assoc()['total'];


// ฟังก์ชันดึงค่าจำนวนรวมจาก channel_id
function getChannelCount($conn, $channel_id)
{
    $sql = "SELECT COUNT(*) AS total 
            FROM consultation
            WHERE channel_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $channel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['total'] ?? 0;
}

// นับจำนวนตาม channel_id
$totalFaceToFace = getChannelCount($conn, 1);  // Face to Face
$totalOnline = getChannelCount($conn, 2);      // ออนไลน์
$totalHotline = getChannelCount($conn, 3);     // สายด่วน

// ดึงคณะที่มีการให้คำปรึกษา
$sql_faculty = "SELECT DISTINCT f.faculty_id, f.faculty_name 
                FROM consultation_recipients cr
                JOIN student s ON cr.std_id = s.std_id
                JOIN faculties f ON s.faculty_id = f.faculty_id
                ORDER BY f.faculty_name";

$result_faculty = $conn->query($sql_faculty);

// ดึงจำนวนตาม Follow_id สำหรับคณะที่เลือก
$sql_follow_count = "SELECT 
                        SUM(CASE WHEN c.follow_id = 1 THEN 1 ELSE 0 END) AS normal,
                        SUM(CASE WHEN c.follow_id = 2 THEN 1 ELSE 0 END) AS monitoring,
                        SUM(CASE WHEN c.follow_id = 3 THEN 1 ELSE 0 END) AS risk,
                        SUM(CASE WHEN c.follow_id = 4 THEN 1 ELSE 0 END) AS critical
                    FROM consultation c
                    JOIN consultation_recipients cr ON c.hn_id = cr.hn_id
                    JOIN student s ON cr.std_id = s.std_id
                    WHERE s.faculty_id = ?";  // ใช้คณะที่เลือก




// ปิดการเชื่อมต่อ
$stmt_table->close();
$stmt_survey_chart->close();
$stmt_faculty_chart->close();
$stmt_major_chart->close();
$stmt_gender_chart->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboardระเบียนระบายศิลป์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="navbardash.css">
    <script>
        function filterTable() {
            // รับค่าจาก input ค้นหา
            const searchInput = document.getElementById("searchInput").value.toLowerCase();
            const table = document.getElementById("dataTable");
            const rows = table.getElementsByTagName("tr");

            // วนลูปเช็คแถวในตาราง
            for (let i = 1; i < rows.length; i++) { // เริ่มจากแถวที่ 1 (ข้ามหัวตาราง)
                const cells = rows[i].getElementsByTagName("td");
                let match = false;

                // วนลูปเช็คแต่ละเซลล์ในแถว
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().indexOf(searchInput) > -1) {
                        match = true;
                        break;
                    }
                }

                // ซ่อนหรือแสดงแถวตามผลการค้นหา
                rows[i].style.display = match ? "" : "none";
            }
        }

        function sortTable(columnIndex, headerElement) {
            const table = document.getElementById("dataTable");
            const rows = Array.from(table.rows).slice(1); // ข้ามแถวหัวตาราง
            const tbody = table.tBodies[0];

            // อ่านสถานะเรียงลำดับปัจจุบัน
            let isAscending = headerElement.getAttribute("data-sort-order") === "asc";

            // Sort ข้อมูล
            rows.sort((rowA, rowB) => {
                const cellA = rowA.cells[columnIndex].textContent.trim();
                const cellB = rowB.cells[columnIndex].textContent.trim();

                if (!isNaN(cellA) && !isNaN(cellB)) {
                    return isAscending ? cellA - cellB : cellB - cellA; // เรียงตัวเลข
                }
                return isAscending ?
                    cellA.localeCompare(cellB, undefined, {
                        numeric: true,
                        sensitivity: "base"
                    }) :
                    cellB.localeCompare(cellA, undefined, {
                        numeric: true,
                        sensitivity: "base"
                    });
            });

            // อัปเดต DOM
            tbody.innerHTML = "";
            rows.forEach(row => tbody.appendChild(row));

            // รีเซ็ต Icon ทุกคอลัมน์
            const headers = table.querySelectorAll("th");
            headers.forEach(th => {
                const icon = th.querySelector("i");
                if (icon) {
                    icon.style.visibility = "hidden"; // ซ่อนไอคอน
                    icon.className = "bi bi-arrow-up"; // รีเซ็ตเป็นลูกศรขึ้น
                }
                th.setAttribute("data-sort-order", "asc"); // รีเซ็ตการเรียงลำดับ
            });

            // แสดง Icon และอัปเดต Icon ในคอลัมน์ที่คลิก
            const icon = headerElement.querySelector("i");
            icon.style.visibility = "visible"; // แสดงไอคอน
            icon.className = isAscending ? "bi bi-arrow-down" : "bi bi-arrow-up"; // อัปเดตไอคอนตามทิศทางการเรียง

            // สลับสถานะเรียงลำดับ
            headerElement.setAttribute("data-sort-order", isAscending ? "desc" : "asc");
        }

        function createLegend(containerId, labels, colors) {
            const container = document.getElementById(containerId);

            if (labels.length > 0) {
                container.innerHTML = labels.map((label, index) => `
            <div class="legend-box">
                <span class="color-indicator" style="background-color: ${colors[index]};"></span>
                <span class="legend-text">${label}</span>
            </div>
        `).join(""); // รวม HTML
            } else {
                container.innerHTML = "<p>ไม่มีข้อมูลที่จะแสดง</p>";
            }
        }

        // ตัวอย่างการเรียกใช้ฟังก์ชัน
        createLegend("legendContainer", Object.keys(severityColors), Object.values(severityColors));
    </script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ระบายศิลป์</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
                    <!-- เมนู Dropdown แบบ Select -->
                    <li class="nav-item">
                        <select class="form-select" onchange="navigateToDashboard(this)">
                            <option value="" disabled selected>เลือก Dashboard</option>
                            <option value="dashboard.php">Dashboard</option>
                            <option value="dashboardrecord.php">Dashboard Record</option>
                        </select>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="index.php">แบบทดสอบ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../projectrabaisil/user_appdetails.php">นัดหมาย</a></li>
                </ul>

                <div class="d-flex align-items-center">
                    <span class="user-profile me-2"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../../login/logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function navigateToDashboard(selectElement) {
            // ตรวจสอบว่าเลือกตัวเลือกหรือไม่
            if (selectElement.value) {
                window.location.href = selectElement.value; // นำผู้ใช้ไปที่ลิงก์ที่เลือก
            }
        }
    </script>

    <header>
        <h1>dashboardระเบียนระบายศิลป์</h1>
    </header>
    <div class="container py-4">
        <!-- ส่วนข้อมูลสรุป -->
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>จำนวนคนทั้งหมด</h5>
                    <p id="Totalamount"><?php echo $totalCount; ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>จำนวนครั้งที่มีคนเข้ามาปรึกษา</h5>
                    <p id="Totalamount"><?php echo $totalconsult; ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>จำนวนนักศึกษาทั้งหมด</h5>
                    <p id="Totalamount"><?php echo "ชาย: $totalMale คน <br>";
                                        echo "หญิง: $totalFemale คน <br>";
                                        echo "LGBTQ+: $totalLGBTQ คน <br>"; ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>จำนวนการให้คำปรึกษาตามช่องทาง</h5>
                    <p>👥 Face to Face: <?php echo $totalFaceToFace; ?></p>
                    <p>💻 ออนไลน์: <?php echo $totalOnline; ?></p>
                    <p>📞 สายด่วน: <?php echo $totalHotline; ?></p>
                </div>
            </div>

        </div>
    </div>


    <div class="container mt-5">
        <div class="mt-3 text-end">
            <button type="button" class="btn btn-custom" onclick="window.location.href='?survey=0&faculty=0&major=0'">
                รีเซ็ตตัวกรอง
            </button>
        </div>
        <div class="container mt-4">

            <div class="row">
                <!-- กราฟที่ 1 -->
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 mb-4">
                        <label for="faculty">เลือกคณะ:</label>
                        <select id="faculty">
                            <option value="">ทั้งหมด</option>
                        </select>
                        <canvas id="pieChart"></canvas>

                        <script>
                            async function fetchFaculties() {
                                try {
                                    const response = await fetch("get_faculties.php");
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    const faculties = await response.json();
                                    const select = document.getElementById("faculty");

                                    select.innerHTML = `<option value="">-- เลือกคณะ --</option>`; // รีเซ็ตก่อน
                                    faculties.forEach(faculty => {
                                        const option = document.createElement("option");
                                        option.value = faculty.id;
                                        option.textContent = faculty.name;
                                        select.appendChild(option);
                                    });

                                    select.value = ""; // ตั้งค่าเริ่มต้น
                                    updateChart(""); // อัปเดตกราฟหลังจากโหลดเสร็จ

                                } catch (error) {
                                    console.error("Error fetching faculties:", error);
                                }
                            }

                            fetchFaculties();
                        </script>
                    </div>
                    <script>
                        const ctx1 = document.getElementById("pieChart").getContext("2d");
                        const followIdLabels = ["ปกติ", "เฝ้าระวัง", "เสี่ยง", "อันตราย"];
                        const followIdColors = ["#4CAF50", "#FFEB3B", "#FF9800", "#F44336"];
                        let chart;

                        async function fetchData(faculty = "") {
                            try {
                                const response = await fetch(`get_data.php?faculty_id=${faculty}`);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return await response.json();
                            } catch (error) {
                                console.error("Error fetching data:", error);
                                return {
                                    1: 0,
                                    2: 0,
                                    3: 0,
                                    4: 0
                                }
                            }
                        }

                        async function updateChart() {
                            const faculty = document.getElementById("faculty").value;
                            const data = await fetchData(faculty);

                            const chartData = [data[1] || 0, data[2] || 0, data[3] || 0, data[4] || 0];

                            if (chart) {
                                chart.data.datasets[0].data = chartData;
                                chart.update();
                            } else {
                                chart = new Chart(ctx1, {
                                    type: "pie",
                                    data: {

                                        datasets: [{
                                            labels: followIdLabels,
                                            data: chartData,
                                            backgroundColor: followIdColors
                                        }]
                                    }
                                });
                            }
                        }

                        // เพิ่ม Event Listener เพื่ออัปเดตกราฟเมื่อเลือกคณะใหม่
                        document.getElementById("faculty").addEventListener("change", updateChart);

                        // เรียกใช้งานฟังก์ชันเพื่อแสดงกราฟเมื่อหน้าโหลด
                        updateChart();
                    </script>

                </div>
                <!-- กราฟที่ 2 -->
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 mb-4">
                        <!-- สำหรับเลือกสาขา -->
                        <label for="major">เลือกสาขา:</label>
                        <select id="major">
                            <option value="">ทั้งหมด</option>
                        </select>
                        <canvas id="pieChartMajor"></canvas> <!-- กราฟสำหรับสาขา -->

                        <script>
                            // ฟังก์ชันดึงข้อมูลสาขา
                            async function fetchMajors() {
                                try {
                                    const response = await fetch("get_majors.php");
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    const majors = await response.json();
                                    const select = document.getElementById("major");

                                    select.innerHTML = `<option value="">-- เลือกสาขา --</option>`; // รีเซ็ตก่อน
                                    majors.forEach(major => {
                                        const option = document.createElement("option");
                                        option.value = major.major; // ใช้ major
                                        option.textContent = major.major; // ใช้ major
                                        select.appendChild(option);
                                    });

                                    select.value = ""; // ตั้งค่าเริ่มต้น
                                    updateChartMajor(""); // อัปเดตกราฟสาขา

                                } catch (error) {
                                    console.error("Error fetching majors:", error);
                                }
                            }

                            fetchMajors(); // เรียกฟังก์ชันดึงข้อมูลสาขา
                        </script>

                        <script>
                            // ตั้งค่ากราฟสำหรับสาขา
                            const ctxMajor = document.getElementById("pieChartMajor").getContext("2d");
                            let chartMajor;

                            // ฟังก์ชันดึงข้อมูลกราฟสาขา
                            async function fetchDataMajor(major = "") {
                                try {
                                    const response = await fetch(`get_datamajor.php?major=${major}`); // ใช้ major
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return await response.json();
                                } catch (error) {
                                    console.error("Error fetching data:", error);
                                    return {
                                        1: 0,
                                        2: 0,
                                        3: 0,
                                        4: 0
                                    };
                                }
                            }

                            // ฟังก์ชันอัปเดตกราฟสาขา
                            async function updateChartMajor() {
                                const major = document.getElementById("major").value;
                                const data = await fetchDataMajor(major);

                                const chartData = [data[1] || 0, data[2] || 0, data[3] || 0, data[4] || 0];

                                if (chartMajor) {
                                    chartMajor.data.datasets[0].data = chartData;
                                    chartMajor.update();
                                } else {
                                    chartMajor = new Chart(ctxMajor, {
                                        type: "pie", // กราฟวงกลม
                                        data: {
                                            datasets: [{
                                                labels: followIdLabels,
                                                data: chartData,
                                                backgroundColor: followIdColors
                                            }]
                                        }
                                    });
                                }
                            }
                            // เพิ่ม Event Listener เพื่ออัปเดตกราฟเมื่อเลือกสาขาใหม่
                            document.getElementById("major").addEventListener("change", updateChartMajor);

                            // เรียกใช้งานฟังก์ชันเพื่อแสดงกราฟเมื่อหน้าโหลด
                            updateChartMajor(); // กราฟสาขา
                        </script>

                    </div>
                </div>
                <!-- กราฟที่ 3 -->
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 mb-4">
                        <!-- สำหรับเลือกปีการศึกษา -->
                        <label for="year">เลือกปีการศึกษา:</label>
                        <select id="year">
                            <option value="">ทั้งหมด</option>
                        </select>
                        <canvas id="pieChartYear"></canvas> <!-- กราฟสำหรับปีการศึกษา -->

                        <script>
                            // ฟังก์ชันดึงข้อมูลปีการศึกษา
                            async function fetchYears() {
                                try {
                                    const response = await fetch("get_years.php");
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    const years = await response.json();
                                    const select = document.getElementById("year");

                                    select.innerHTML = `<option value="">-- เลือกปีการศึกษา --</option>`; // รีเซ็ตก่อน
                                    years.forEach(year => {
                                        const option = document.createElement("option");
                                        option.value = year.year; // ใช้ year
                                        option.textContent = year.year; // ใช้ year
                                        select.appendChild(option);
                                    });

                                    select.value = ""; // ตั้งค่าเริ่มต้น
                                    updateChartYear(""); // อัปเดตกราฟปีการศึกษา

                                } catch (error) {
                                    console.error("Error fetching years:", error);
                                }
                            }

                            fetchYears(); // เรียกฟังก์ชันดึงข้อมูลปีการศึกษา
                        </script>

                        <script>
                            // ตั้งค่ากราฟสำหรับปีการศึกษา
                            const ctxYear = document.getElementById("pieChartYear").getContext("2d");
                            let chartYear;

                            // ฟังก์ชันดึงข้อมูลกราฟปีการศึกษา
                            async function fetchDataYear(year = "") {
                                try {
                                    const response = await fetch(`get_datayear.php?year=${year}`); // ใช้ year
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return await response.json();
                                } catch (error) {
                                    console.error("Error fetching data:", error);
                                    return {
                                        1: 0,
                                        2: 0,
                                        3: 0,
                                        4: 0
                                    };
                                }
                            }

                            // ฟังก์ชันอัปเดตกราฟปีการศึกษา
                            async function updateChartYear() {
                                const year = document.getElementById("year").value;
                                const data = await fetchDataYear(year);

                                const chartData = [data[1] || 0, data[2] || 0, data[3] || 0, data[4] || 0];

                                if (chartYear) {
                                    chartYear.data.datasets[0].data = chartData;
                                    chartYear.update();
                                } else {
                                    chartYear = new Chart(ctxYear, {
                                        type: "pie", // กราฟวงกลม
                                        data: {
                                            datasets: [{
                                                labels: followIdLabels,
                                                data: chartData,
                                                backgroundColor: followIdColors
                                            }]
                                        }
                                    });
                                }
                            }

                            // เพิ่ม Event Listener เพื่ออัปเดตกราฟเมื่อเลือกปีการศึกษาใหม่
                            document.getElementById("year").addEventListener("change", updateChartYear);

                            // เรียกใช้งานฟังก์ชันเพื่อแสดงกราฟเมื่อหน้าโหลด
                            updateChartYear(); // กราฟปีการศึกษา
                        </script>

                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Div สำหรับแสดงความหมายของสี -->
                <div class="col-12">
                    <div class="card shadow-sm p-3 mb-4">
                        <h2 class="text-center mb-3">ความหมายของสี</h2>
                        <div id="legendContainer" class="d-flex flex-wrap justify-content-center align-items-center"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Div สำหรับแสดงความหมายของสี -->
                <div class="col-12">
                    <div class="card shadow-sm p-3 mb-4">
                        <h3 class="text-center mb-4">เปรียบเทียบระดับความแตกต่างตามเพศ</h3>
                        <!-- สำหรับเลือกเพศ -->
                        <label for="gender">เลือกเพศ:</label>
                        <select id="gender">
                            <option value="">ทั้งหมด</option>
                            <option value="1">ชาย</option>
                            <option value="2">หญิง</option>
                        </select>
                        <canvas id="barChartGender"></canvas> <!-- กราฟสำหรับเพศ -->

                        <script>
                            // ฟังก์ชันดึงข้อมูลเพศ
                            async function fetchGenders() {
                                try {
                                    const response = await fetch("get_genders.php");
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    const genders = await response.json();
                                    const select = document.getElementById("gender");

                                    select.innerHTML = `<option value="">-- เลือกเพศ --</option>`; // รีเซ็ตก่อน
                                    genders.forEach(gender => {
                                        const option = document.createElement("option");
                                        option.value = gender.gender; // ใช้ gender
                                        option.textContent = gender.gender; // ใช้ gender
                                        select.appendChild(option);
                                    });

                                    select.value = ""; // ตั้งค่าเริ่มต้น
                                    updateChartGender(""); // อัปเดตกราฟเพศ

                                } catch (error) {
                                    console.error("Error fetching genders:", error);
                                }
                            }

                            fetchGenders(); // เรียกฟังก์ชันดึงข้อมูลเพศ
                        </script>

                        <script>
                            // ตั้งค่ากราฟสำหรับเพศ
                            const ctxGender = document.getElementById("barChartGender").getContext("2d");
                            let chartGender;

                            // ฟังก์ชันดึงข้อมูลกราฟเพศ
                            async function fetchDataGender(gender = "") {
                                try {
                                    const response = await fetch(`get_datagender.php?gender=${gender}`); // ใช้ gender
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return await response.json();
                                } catch (error) {
                                    console.error("Error fetching data:", error);
                                    return {
                                        1: 0,
                                        2: 0,
                                        3: 0,
                                        4: 0
                                    };
                                }
                            }

                            // ฟังก์ชันอัปเดตกราฟเพศ
                            async function updateChartGender() {
                                const gender = document.getElementById("gender").value;
                                const data = await fetchDataGender(gender);

                                const chartData = [data[1] || 0, data[2] || 0, data[3] || 0, data[4] || 0];

                                if (chartGender) {
                                    chartGender.data.datasets[0].data = chartData;
                                    chartGender.update();
                                } else {
                                    chartGender = new Chart(ctxGender, {
                                        type: "bar", // เปลี่ยนเป็นกราฟแท่ง
                                        data: {
                                            labels: ["ปกติ", "เฝ้าระวัง", "เสี่ยง", "อันตราย"], // ป้ายข้อมูลในกราฟ
                                            datasets: [{
                                                label: 'จำนวนผู้ที่ปรึกษาตามสถานะ',
                                                data: chartData,
                                                backgroundColor: ["#4CAF50", "#FFEB3B", "#FF9800", "#F44336"], // สีของแต่ละบาร์
                                                borderColor: ["#4CAF50", "#FFEB3B", "#FF9800", "#F44336"],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    title: {
                                                        display: true,
                                                        text: 'จำนวน'
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            }

                            // เพิ่ม Event Listener เพื่ออัปเดตกราฟเมื่อเลือกเพศใหม่
                            document.getElementById("gender").addEventListener("change", updateChartGender);

                            // เรียกใช้งานฟังก์ชันเพื่อแสดงกราฟเมื่อหน้าโหลด
                            updateChartGender(); // กราฟเพศ
                        </script>

                    </div>
                </div>
            </div>
        </div>

        <div class="table-container mt-5">
            <h2 class="text-center mb-4">ตารางผลการทดสอบ</h2>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-custom" onclick="window.location.href='report.php'">
                    ไปหน้าดาวโหลดข้อมูลรายงาน
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาในตาราง..." onkeyup="filterTable()">
            </div>


            <table class="table modern-table" id="dataTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, this)" data-sort-order="asc">
                            รหัสนักศึกษา <i class="bi bi-arrow-up" style="visibility: hidden;"></i>
                        </th>
                        <th onclick="sortTable(1, this)" data-sort-order="asc">
                            ชื่อ-นามสกุล <i class="bi bi-arrow-up" style="visibility: hidden;"></i>
                        </th>
                        <th onclick="sortTable(2, this)" data-sort-order="asc">
                            คณะ <i class="bi bi-arrow-up" style="visibility: hidden;"></i>
                        </th>
                        <th onclick="sortTable(3, this)" data-sort-order="asc">
                            สาขา <i class="bi bi-arrow-up" style="visibility: hidden;"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_table->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['std_id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['faculty_name']; ?></td>
                            <td><?php echo $row['major_name']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>


            <!-- แสดง Pagination -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>


        <script>
            // Colors configuration
            const severityColors = {
                'ปกติ': '#28B463',
                'เฝ้าระวัง': '#FFC300',
                'เสี่ยง': '#FF5733',
                'รุนแรง': '#C70039'
            };

            // ฟังก์ชันสร้าง Legend กลาง
            function createLegend(containerId, labels, colors) {
                const container = document.getElementById(containerId);

                if (labels.length > 0) {
                    container.innerHTML = labels.map((label, index) => `
            <div class="legend-box">
                <span class="color-indicator" style="background-color: ${colors[index]};"></span>
                <span class="legend-text">${label}</span>
            </div>
        `).join(""); // รวม HTML
                } else {
                    container.innerHTML = "<p>ไม่มีข้อมูลที่จะแสดง</p>";
                }
            }

            // สร้าง Legend กลาง
            createLegend("legendContainer", Object.keys(severityColors), Object.values(severityColors));


            const ctx = document.getElementById('genderBarChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: genderLabels, // ระดับความรุนแรง
                    datasets: genderDatasets // ข้อมูลเพศ
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'การเปรียบเทียบระดับความแตกต่างตามเพศ'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'ระดับความรุนแรง'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'จำนวนผู้เข้าทำแบบทดสอบ'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>

</body>

</html>