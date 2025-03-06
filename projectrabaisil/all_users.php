<?php
include 'database_connection.php';
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
// ตรวจสอบจำนวนคำขอนัดหมายใหม่
$sql_notification = "SELECT COUNT(*) AS new_requests FROM consultation WHERE status = 1";
$result_notification = $connect->query($sql_notification);
$notification_count = 0;

if ($result_notification->num_rows > 0) {
    $row_notification = $result_notification->fetch_assoc();
    $notification_count = $row_notification['new_requests'];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดนักศึกษา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="detailstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .badge {
            font-size: 0.75rem;
            padding: 0.25em 0.5em;
            border-radius: 50%;
            color: white;
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
                    <li class="nav-item">
                        <a class="nav-link" href="#">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="app.php">นัดหมาย</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">แบบทดสอบ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="all_users.php">คำปรึกษา</a>
                    </li>
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="app_manage.php">คำขอนัดหมาย</a>
                        <?php if ($notification_count > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                <?= $notification_count ?>
                            </span>
                        <?php endif; ?>
                    </li>
                </ul>

                <!-- โปรไฟล์ผู้ใช้ -->
                <div class="d-flex align-items-center">
                    <span class="user-profile me-2">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
    
        <input type="text" id="searchInput" placeholder="ค้นหา" class="form-control mb-2">
           
        <!-- ฟิลด์กรองข้อมูล -->
        
        <div class="search-bar">
        <div class="header">รายการผู้รับคำปรึกษา</div><br>
        <select id="yearFilter" class="form-select mb-2">
    <option value="">ปีการศึกษา</option>
    <?php
    // เชื่อมต่อฐานข้อมูล
    $hostname = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'rabaisrin_db';

    $connect = mysqli_connect($hostname, $username, $password, $database);

    if ($connect) {
        // ดึงข้อมูลปีการศึกษาจากตาราง student
        $yearQuery = "SELECT DISTINCT year FROM student ORDER BY year DESC";
        $yearResult = mysqli_query($connect, $yearQuery);

        if ($yearResult && mysqli_num_rows($yearResult) > 0) {
            while ($row = mysqli_fetch_assoc($yearResult)) {
                // แสดงปีการศึกษาที่ดึงมาใน select
                echo "<option value='{$row['year']}'>{$row['year']}</option>";
            }
        }

        mysqli_close($connect);
    }
    ?>
</select>
            <select id="facultyFilter" class="form-select mb-2">
                <option value="">คณะ</option>
                <?php
                // ดึงข้อมูลชื่อคณะจากฐานข้อมูล
                $hostname = 'localhost';
                $username = 'root';
                $password = '';
                $database = 'rabaisrin_db';

                $connect = mysqli_connect($hostname, $username, $password, $database);

                if ($connect) {
                    $facultyQuery = "SELECT faculty_name FROM faculties";
                    $facultyResult = mysqli_query($connect, $facultyQuery);

                    if ($facultyResult && mysqli_num_rows($facultyResult) > 0) {
                        while ($row = mysqli_fetch_assoc($facultyResult)) {
                            echo "<option value='{$row['faculty_name']}'>{$row['faculty_name']}</option>";
                        }
                    }

                    mysqli_close($connect);
                }
                ?>
            </select>
            <button id="filterButton" class="btn btn-primary">เลือก</button>
                
        </div>
        

        <!-- แสดงผลข้อมูล -->
        <div id="records" class="mt-3">
            <!-- ข้อมูลจากฐานข้อมูลจะถูกเพิ่มที่นี่ผ่าน JavaScript -->
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const yearFilter = document.getElementById('yearFilter');
        const facultyFilter = document.getElementById('facultyFilter');
        const filterButton = document.getElementById('filterButton');
        const recordsContainer = document.getElementById('records');

        // ฟังก์ชันสำหรับดึงข้อมูลจากฐานข้อมูล
        function fetchRecords() {
            const search = searchInput.value;
            const year = yearFilter.value;
            const faculty = facultyFilter.value;

            // ส่งค่าผ่าน GET ไปยัง fetch_data.php
            fetch(`fetch_data.php?search=${search}&year=${year}&faculty=${faculty}`)
                .then(response => response.json())
                .then(data => {
                    recordsContainer.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(record => {
                            const recordDiv = document.createElement('div');
                            recordDiv.classList.add('record', 'mb-3', 'p-2', 'border', 'rounded');
                            recordDiv.innerHTML = `
                                <div class="record-id">ID: ${record.hn_id}</div>
                                <div class="record-details">${record.std_id} - ${record.first_name} ${record.last_name}</div>
                                <button class="detail-button btn btn-secondary" data-std-id="${record.std_id}" data-hn-id="${record.hn_id}">รายละเอียด</button>
                            `;
                            recordsContainer.appendChild(recordDiv);
                        });

                        // เพิ่ม Event Listener ให้กับปุ่ม "รายละเอียด"
                        document.querySelectorAll('.detail-button').forEach(button => {
                            button.addEventListener('click', () => {
                                const std_id = button.getAttribute('data-std-id');
                                const hn_id = button.getAttribute('data-hn-id');
                                window.location.href = `detail2.php?std_id=${std_id}&hn_id=${hn_id}`;
                            });
                        });
                    } else {
                        recordsContainer.innerHTML = '<div class="alert alert-warning">ไม่พบข้อมูล</div>';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // เรียก fetchRecords() เมื่อโหลดหน้า
        fetchRecords();

        // Event Listeners
        filterButton.addEventListener('click', fetchRecords);
        searchInput.addEventListener('input', fetchRecords);
    });
    </script>
</body>
</html>
