<?php
include('../../dbconnect.php');  // รวมไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการกรองข้อมูลเดือนและปีหรือไม่
$month = isset($_POST['month']) ? $_POST['month'] : null;
$year = isset($_POST['year']) ? $_POST['year'] : null;

// กำหนดเงื่อนไข SQL สำหรับการกรองข้อมูล
$sql_conditions = [];

if ($month) {
    $sql_conditions[] = "MONTH(c.event_date) = ?";
}

if ($year) {
    $sql_conditions[] = "YEAR(c.event_date) = ?";
}

// สร้าง SQL ที่กรองตามเดือนหรือปี หรือทั้งสอง
$sql = "SELECT c.hn_id, c.event_date, c.event_time, s.first_name, s.last_name, s.major, s.year AS student_year, s.gender, c.consult_case, c.follow_id, c.forward_from, c.forward_des
        FROM consultation c
        JOIN consultation_recipients cr ON c.hn_id = cr.hn_id
        JOIN student s ON cr.std_id = s.std_id";

// ถ้ามีการกรองข้อมูล (เดือนหรือปี) ให้เพิ่มเงื่อนไข WHERE
if (!empty($sql_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $sql_conditions);
}

$sql .= " ORDER BY c.event_date DESC";

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);

// ผูกพารามิเตอร์กับ SQL
$bind_params = [];
if ($month) {
    $bind_params[] = $month;
}
if ($year) {
    $bind_params[] = $year;
}

if ($bind_params) {
    // ผูกพารามิเตอร์
    $stmt->bind_param(str_repeat("i", count($bind_params)), ...$bind_params);
}

$stmt->execute();
$result = $stmt->get_result();

// ดึงข้อมูลเดือนและปีที่มีอยู่ในฐานข้อมูล
$sql_month_year = "SELECT DISTINCT MONTH(event_date) AS month, YEAR(event_date) AS year
                   FROM consultation
                   ORDER BY year DESC, month DESC";
$month_year_result = $conn->query($sql_month_year);

// ฟังก์ชันสำหรับสร้างไฟล์ CSV
if (isset($_POST['download_csv'])) {
    // ตั้งค่าการส่งข้อมูลไปยังไฟล์ CSV
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="consultation_report.csv"');
    $output = fopen('php://output', 'w');

    // เขียนข้อมูลหัวข้อ CSV (ทำให้รองรับภาษาไทย)
    fputs($output, "\xEF\xBB\xBF"); // BOM for UTF-8
    fputcsv($output, ['เลขทะเบียน', 'คณะ', 'ชั้นปี', 'เพศ', 'ปัญหา/หัวข้อเรื่อง', 'ระดับความรุนแรง', 'ส่งต่อ', 'หมายเหตุเพิ่มเติม']);

    // เขียนข้อมูลในตารางเป็น CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['hn_id'],
            $row['major'],
            $row['student_year'],
            $row['gender'] == 1 ? 'ชาย' : 'หญิง',
            $row['consult_case'],
            // กำหนดระดับความรุนแรง
            $row['follow_id'] == 1 ? 'ระดับ 1 (สีเขียว)' : ($row['follow_id'] == 2 ? 'ระดับ 2 (สีเหลือง)' : ($row['follow_id'] == 3 ? 'ระดับ 3 (สีส้ม)' : 'ระดับ 4 (สีแดง)')) ,
            $row['forward_from'],
            $row['forward_des']
        ]);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานข้อมูลคำปรึกษา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <style>
        .table-container table {
            font-size: 0.95rem;
        }

        .table-container table th,
        .table-container table td {
            vertical-align: middle;
        }

        .table-container table th {
            background-color: #f8f9fa;
        }

        .table-container table td {
            text-align: center;
        }
    </style>
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
    <div class="container my-5 table-container">
        <h2 class="text-center mb-4">รายงานข้อมูลคำปรึกษา</h2>

        <!-- ฟอร์มกรอกเดือนและปี -->
<form method="POST" action="" class="row g-3 mb-4">
    <div class="col-md-4">
        <label for="month" class="form-label">เดือน</label>
        <select name="month" id="month" class="form-select">
            <option value="" <?= $month === null ? 'selected' : '' ?>>ทั้งหมด</option>
            <?php
            // กำหนดชื่อเดือนเป็นภาษาไทย
            $thai_months = [
                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 
                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 
                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
            ];
            while ($row = $month_year_result->fetch_assoc()):
            ?>
                <option value="<?= $row['month'] ?>" <?= $month == $row['month'] ? 'selected' : '' ?>>
                    <?= $thai_months[$row['month']] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label for="year" class="form-label">ปี</label>
        <select name="year" id="year" class="form-select">
            <option value="" <?= $year === null ? 'selected' : '' ?>>ทั้งหมด</option>
            <?php
            // เปลี่ยนปีจาก ค.ศ. เป็น พ.ศ.
            $years = [];
            // รีเซ็ตตัวชี้ตำแหน่งของผลลัพธ์
            $month_year_result->data_seek(0);
            while ($row = $month_year_result->fetch_assoc()) {
                $years[] = $row['year'];
            }
            $years = array_unique($years);
            foreach ($years as $year_option) : ?>
                <option value="<?= $year_option ?>" <?= $year == $year_option ? 'selected' : '' ?>>
                    <?= $year_option + 543 ?> <!-- แปลงปี ค.ศ. เป็น พ.ศ. -->
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">กรองข้อมูล</button>
    </div>
</form>



        <!-- ปุ่มดาวน์โหลด CSV -->
        <form method="POST" action="">
            <button type="button" class="btn btn-success mb-4" onclick="downloadFilteredCSV()">ดาวน์โหลดเป็นไฟล์ CSV</button>
        </form>

        <!-- ตารางแสดงข้อมูล -->
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>เลขทะเบียน</th>
                        <th>คณะ</th>
                        <th>ชั้นปี</th>
                        <th>เพศ</th>
                        <th>ปัญหา/หัวข้อเรื่อง</th>
                        <th>ระดับความรุนแรง</th>
                        <th>ส่งต่อ</th>
                        <th>หมายเหตุเพิ่มเติม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['hn_id'] ?></td>
                            <td><?= $row['major'] ?></td>
                            <td><?= $row['student_year'] ?></td>
                            <td><?= $row['gender'] == 1 ? 'ชาย' : 'หญิง' ?></td>
                            <td><?= $row['consult_case'] ?></td>
                            <td>
                                <?php
                                // กำหนดระดับความรุนแรงตาม follow_id
                                switch ($row['follow_id']) {
                                    case 1:
                                        echo '<span class="text-success">ระดับ 1 (สีเขียว)</span>';
                                        break;
                                    case 2:
                                        echo '<span class="text-warning">ระดับ 2 (สีเหลือง)</span>';
                                        break;
                                    case 3:
                                        echo '<span class="text-warning">ระดับ 3 (สีส้ม)</span>';
                                        break;
                                    case 4:
                                        echo '<span class="text-danger">ระดับ 4 (สีแดง)</span>';
                                        break;
                                    default:
                                        echo 'ไม่มีข้อมูล';
                                }
                                ?>
                            </td>
                            <td><?= $row['forward_from'] ?></td>
                            <td><?= $row['forward_des'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">ไม่มีข้อมูลในเดือนและปีที่เลือก</p>
        <?php endif; ?>

    </div>

    <script>
    function downloadFilteredCSV() {
        // ดึงค่าจากฟอร์มเดือนและปี
        let month = document.getElementById("month").value;
        let year = document.getElementById("year").value;

        // สร้างฟอร์มเพื่อส่งข้อมูลไปยัง server
        let form = document.createElement("form");
        form.method = "POST";
        form.action = ""; // ส่งข้อมูลกลับมายังหน้าปัจจุบัน

        // เพิ่มค่าตัวแปรเดือนและปี
        let inputMonth = document.createElement("input");
        inputMonth.type = "hidden";
        inputMonth.name = "month";
        inputMonth.value = month;
        form.appendChild(inputMonth);

        let inputYear = document.createElement("input");
        inputYear.type = "hidden";
        inputYear.name = "year";
        inputYear.value = year;
        form.appendChild(inputYear);

        // เพิ่มตัวแปรสำหรับการดาวน์โหลด CSV
        let inputDownload = document.createElement("input");
        inputDownload.type = "hidden";
        inputDownload.name = "download_csv";
        inputDownload.value = "true"; // ใช้เพื่อบอกว่าเราต้องการดาวน์โหลด CSV
        form.appendChild(inputDownload);

        // ส่งฟอร์ม
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
