<?php
session_start();
include '../dbconnect.php';

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
// จำนวนรวมทั้งหมด
$totalCountSql = "SELECT COUNT(DISTINCT faculty_id) AS total FROM student";
$totalCount = getTotalCount($conn, $totalCountSql);

$selectedCountSql = "SELECT COUNT(DISTINCT s.faculty_id) AS total FROM student s
                     JOIN faculties f ON s.faculty_id = f.faculty_id";
$conditions = [];
$params = [];
$types = '';


//นับจำนวนชายหณิงหญิงและ lgbtqa
$genderCountSql = "SELECT 
                    SUM(CASE WHEN gender = 1 THEN 1 ELSE 0 END) AS male_count,
                    SUM(CASE WHEN gender = 2 THEN 1 ELSE 0 END) AS female_count,
                    SUM(CASE WHEN gender = 3 THEN 1 ELSE 0 END) AS lgbtq_count
                   FROM student";

$stmt = $conn->prepare($genderCountSql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$maleCount = $row['male_count'];
$femaleCount = $row['female_count'];
$lgbtqCount = $row['lgbtq_count'];

//นับจำนวนปี
$yearCountSql = "SELECT 
                    CASE 
                        WHEN year < 100 THEN CONCAT('25', LPAD(year, 2, '0')) 
                        ELSE year 
                    END AS formatted_year,
                    COUNT(*) AS total
                 FROM student
                 GROUP BY formatted_year
                 ORDER BY formatted_year ASC";

$stmt = $conn->prepare($yearCountSql);
$stmt->execute();
$result = $stmt->get_result();


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
              JOIN faculties f ON s.faculty_id = f.faculty_id";

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
    <title>ผลการทดสอบและตารางข้อมูล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="navbardash.css">
    <script>
        function filterResults() {
            const surveyId = document.getElementById('survey').value;
            const facultyId = document.getElementById('faculty').value;
            const majorId = document.getElementById('major').value; // ดึงค่า Major
            window.location.href = `?survey=${surveyId}&faculty=${facultyId}&major=${majorId}`; // อัปเดต query string
        }

        const genderLabels = <?php echo json_encode($severities); ?>;
        const genderDatasets = [{
                label: 'ชาย',
                data: <?php echo json_encode(array_values($gender_data['male'])); ?>,
                backgroundColor: '#4E79A7',
            },
            {
                label: 'หญิง',
                data: <?php echo json_encode(array_values($gender_data['female'])); ?>,
                backgroundColor: '#F28E2B',
            },
            {
                label: 'LGBTQA+',
                data: <?php echo json_encode(array_values($gender_data['lgbtqa'])); ?>,
                backgroundColor: '#76B7B2',
            }
        ];

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
                    <li class="nav-item"><a class="nav-link" href="">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">แบบทดสอบ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../projectrabaisil/user_appdetails.php">นัดหมาย</a></li>
                </ul>

                <div class="d-flex align-items-center">
                    <span class="user-profile me-2"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../login/logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <header>
        <h1>dashboardระบบระบายศิลป์</h1>
    </header>
    <div class="container py-4">
        <!-- ส่วนข้อมูลสรุป -->
        <div class="row justify-content-center g-3">
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>จำนวนคณะ 🏫</h5>
                    <p id="Totalamount"><?php echo $totalCount; ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>จำนวนชั้นปี 🎓</h5>
                    <p id="Numbergrades"><?php while ($row = $result->fetch_assoc()) {
                                                echo "ปีการศึกษา " . $row['formatted_year'] . ": " . $row['total'] . " คน<br>";
                                            } ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom p-3 text-center">
                    <h5>ชาย/หญิง 🚻</h5>
                    <p id="gender"><?php echo "ชาย: $maleCount คน<br>";
                                    echo "หญิง: $femaleCount คน<br>";
                                    echo "LGBTQ+: $lgbtqCount คน<br>"; ?></p>
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
                        <label for="survey" class="form-label">เลือกแบบทดสอบ</label>
                        <select name="survey" id="survey" class="form-select" onchange="filterResults()">
                            <option value="0">-- ทั้งหมด --</option>
                            <option value="1" <?php if ($selected_survey == 1) echo 'selected'; ?>>แบบทดสอบซึมเศร้า</option>
                            <option value="2" <?php if ($selected_survey == 2) echo 'selected'; ?>>แบบทดสอบวิตกกังวล</option>
                            <option value="3" <?php if ($selected_survey == 3) echo 'selected'; ?>>แบบทดสอบความเครียด</option>
                        </select>
                        <div class="chart-container">
                            <canvas id="surveyChart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- กราฟที่ 2 -->
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 mb-4">
                        <label for="faculty" class="form-label">เลือกคณะ</label>
                        <select name="faculty" id="faculty" class="form-select" onchange="filterResults()">
                            <option value="0">-- ทั้งหมด --</option>
                            <?php while ($row = $result_faculty->fetch_assoc()): ?>
                                <option value="<?php echo $row['faculty_id']; ?>" <?php if ($selected_faculty == $row['faculty_id']) echo 'selected'; ?>>
                                    <?php echo $row['faculty_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="chart-container">
                            <canvas id="facultyChart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- กราฟที่ 3 -->
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 mb-4">
                        <label for="major" class="form-label">เลือกสาขา</label>
                        <select name="major" id="major" class="form-select" onchange="filterResults()">
                            <option value="0">-- ทั้งหมด --</option>
                            <?php while ($row = $result_major->fetch_assoc()): ?>
                                <option value="<?php echo $row['major_name']; ?>" <?php if ($selected_major == $row['major_name']) echo 'selected'; ?>>
                                    <?php echo $row['major_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="chart-container">
                            <canvas id="MajorChart"></canvas>
                        </div>
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
                        <label for="gender" class="form-label">เลือกเพศ</label>
                        <select name="gender" id="gender" class="form-select">
                            <option value="0">-- ทั้งหมด --</option>
                            <?php foreach ($available_genders as $gender): ?>
                                <option value="<?php echo htmlspecialchars($gender); ?>">
                                    <?php echo htmlspecialchars($gender); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <canvas id="genderBarChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <script>
            // Colors configuration
            const severityColors = {
                'ปกติ': '#28B463',
                'ปานกลาง': '#FFC300',
                'รุนแรง': '#FF5733',
                'รุนแรงที่สุด': '#C70039'
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

            // Example Chart.js Configurations (แสดงผลเหมือนเดิม)
            const surveyLabels = <?php echo json_encode($survey_labels); ?>;
            const surveyData = <?php echo json_encode($survey_data); ?>;
            const surveyBackgroundColors = surveyLabels.map(label => severityColors[label]);

            const surveyCtx = document.getElementById('surveyChart').getContext('2d');
            new Chart(surveyCtx, {
                type: 'pie',
                data: {
                    datasets: [{
                        label: 'ระดับความรุนแรง (แบบทดสอบ)',
                        data: surveyData,
                        backgroundColor: surveyBackgroundColors,
                    }]
                },
                options: {
                    responsive: true,
                }
            });

            const facultyLabels = <?php echo json_encode($faculty_labels); ?>;
            const facultyData = <?php echo json_encode($faculty_data); ?>;
            const facultyBackgroundColors = facultyLabels.map(label => severityColors[label]);

            const facultyCtx = document.getElementById('facultyChart').getContext('2d');
            new Chart(facultyCtx, {
                type: 'pie',
                data: {
                    datasets: [{
                        label: 'ระดับความรุนแรง (คณะ)',
                        data: facultyData,
                        backgroundColor: facultyBackgroundColors,
                    }]
                },
                options: {
                    responsive: true,
                }
            });

            const majorLabels = <?php echo json_encode($major_labels); ?>;
            const majorData = <?php echo json_encode($major_data); ?>;
            const majorBackgroundColors = majorLabels.map(label => severityColors[label]);

            const majorCtx = document.getElementById('MajorChart').getContext('2d');
            new Chart(majorCtx, {
                type: 'pie',
                data: {
                    datasets: [{
                        label: 'ระดับความรุนแรง (สาขา)',
                        data: majorData,
                        backgroundColor: majorBackgroundColors,
                    }]
                },
                options: {
                    responsive: true,
                }
            });
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