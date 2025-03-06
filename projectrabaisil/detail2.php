<?php
// db_connection.php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

// ตรวจสอบการเชื่อมต่อ
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
$sql_notification = "SELECT COUNT(*) AS new_requests FROM consultation WHERE status = 1";
$result_notification = $connect->query($sql_notification);
$notification_count = 0;

if ($result_notification->num_rows > 0) {
    $row_notification = $result_notification->fetch_assoc();
    $notification_count = $row_notification['new_requests'];
}
// รับ std_id และ hn_id จาก URL
$std_id = $_GET['std_id'];
$hn_id = $_GET['hn_id'];  // รับ hn_id จาก URL

// ดึงข้อมูล hn_id, first_name, last_name โดยใช้ std_id
$query = "
    SELECT cr.hn_id, s.first_name, s.last_name 
    FROM consultation_recipients AS cr
    INNER JOIN student AS s ON cr.std_id = s.std_id
    WHERE cr.std_id = ?
";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "s", $std_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

// นับจำนวนครั้งที่ hn_id ปรากฏในตาราง consultation
$count_query = "
    SELECT COUNT(*) AS visit_count 
    FROM consultation 
    WHERE hn_id = ?
";
$count_stmt = mysqli_prepare($connect, $count_query);
mysqli_stmt_bind_param($count_stmt, "s", $student['hn_id']);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$visit_count = $count_row['visit_count'];

// ดึงข้อมูลคำปรึกษาจาก consultation table
// ดึงข้อมูลคำปรึกษาจาก consultation table โดยเรียงลำดับจาก app_id มากไปหาน้อย
$consultation_query = "
    SELECT * FROM consultation
    WHERE hn_id = ? AND status NOT IN (1, 3)
    ORDER BY app_id DESC
";
$consultation_stmt = mysqli_prepare($connect, $consultation_query);
mysqli_stmt_bind_param($consultation_stmt, "s", $hn_id);
mysqli_stmt_execute($consultation_stmt);
$consultation_result = mysqli_stmt_get_result($consultation_stmt);


// ดึง app_id ล่าสุด
$latest_app_query = "
    SELECT app_id FROM consultation
    WHERE hn_id = ?
    ORDER BY app_id DESC LIMIT 1
";
$latest_app_stmt = mysqli_prepare($connect, $latest_app_query);
mysqli_stmt_bind_param($latest_app_stmt, "s", $hn_id);
mysqli_stmt_execute($latest_app_stmt);
$latest_app_result = mysqli_stmt_get_result($latest_app_stmt);
$latest_app = mysqli_fetch_assoc($latest_app_result);
$latest_app_id = $latest_app['app_id'];

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($connect);
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

    <div class="container mt-4">
        <h2>รายละเอียดนักศึกษา</h2>

        <?php if ($student): ?>
            <p><strong>HN ID:</strong> <?= htmlspecialchars($student['hn_id']) ?></p>
            <p> <?= htmlspecialchars($std_id) ?></p> <?php if ($latest_app_id): ?>              
            <?php endif; ?>
            <p>
                <span><?= htmlspecialchars($student['first_name']) ?></span> &nbsp; <!-- ใช้ &nbsp; เพื่อเว้นระยะห่าง -->
                <span><?= htmlspecialchars($student['last_name']) ?></span>
            </p>
            <p><strong>จำนวนครั้งที่มา:</strong> <?= htmlspecialchars($visit_count) ?> ครั้ง</p>

            <h3 class="mt-4">ข้อมูลคำปรึกษา:</h3>
            <?php if (mysqli_num_rows($consultation_result) > 0): ?>
                <?php
                // สร้างลูปสำหรับแต่ละคำปรึกษา
                while ($consultation = mysqli_fetch_assoc($consultation_result)): ?>
                    <table class="table table-bordered" style="margin-bottom: 20px;"> <!-- เพิ่มระยะห่างระหว่างตาราง -->
                        <tbody>
                            <tr>
                                <th style="background-color: #f1f0f0;">รหัสคำปรึกษา</th>
                                <td style="background-color: #f1f0f0;"><?= htmlspecialchars($consultation['app_id']) ?></td>
                            </tr>
                            <tr>
                                <th>วันที่</th>
                                <td><?= htmlspecialchars($consultation['event_date']) ?></td>
                            </tr>
                            <tr>
                                <th>เวลา</th>
                                <td><?= htmlspecialchars(substr($consultation['event_time'], 0, 5)) ?></td>
                            </tr>
                            <tr>
                                <th>ช่องทาง</th>
                                <td>
                                    <?php
                                    if ($consultation['channel_id'] == 1) {
                                        echo "Face to Face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)";
                                    } elseif ($consultation['channel_id'] == 2) {
                                        echo "ช่องทางออนไลน์";
                                    } elseif ($consultation['channel_id'] == 3) {
                                        echo "สายด่วน";
                                    } else {
                                        echo "ข้อมูลไม่สมบูรณ์";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>แหล่งที่มา</th>
                                <td>
                                    <?php
                                    if ($consultation['origin_id'] == 1) {
                                        echo "สังเกตเห็นและเข้าไปช่วยเหลือเอง";
                                    } elseif ($consultation['origin_id'] == 2) {
                                        echo "ผู้รับการปรึกษามาด้วยตนเอง";
                                    } elseif ($consultation['origin_id'] == 3) {
                                        echo "ส่งต่อมาจาก";
                                    } else {
                                        echo "ข้อมูลไม่สมบูรณ์";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>ผู้ส่งต่อ</th>
                                <td><?= htmlspecialchars($consultation['forward_from']) ?></td>
                            </tr>
                            <tr>
                                <th>ประเภทคำปรึกษา</th>
                                <td><?= htmlspecialchars($consultation['consult_case']) ?></td>
                            </tr>
                            <tr>
                                <th>รายละเอียดคำปรึกษา</th>
                                <td><?= htmlspecialchars($consultation['consult_des']) ?></td>
                            </tr>
                            <tr>
                                <th>ปัญหาและสาเหตุ</th>
                                <td><?= htmlspecialchars($consultation['symptoms']) ?></td>
                            </tr>
                            <tr>
                                <th>คำแนะนำ</th>
                                <td><?= htmlspecialchars($consultation['advice']) ?></td>
                            </tr>
                            <tr>
                                <th>ผลสรุปการแก้ปัญหา</th>
                                <td><?= htmlspecialchars($consultation['test_results']) ?></td>
                            </tr>
                            <tr>
                                <th>รายละเอียดการติดตาม</th>
                                <td><?= htmlspecialchars($consultation['follow_des']) ?></td>
                            </tr>
                            <tr>
                                <th>การส่งต่อ</th>
                                <td>
                                    <?php
                                    if ($consultation['forward_id'] == 1) {
                                        echo "ส่งต่อ";
                                    } elseif ($consultation['forward_id'] == 2) {
                                        echo "ไม่ส่งต่อ";
                                    } elseif ($consultation['forward_id'] == 3) {
                                        echo "อื่นๆ";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>รายละเอียดผู้ส่งต่อ</th>
                                <td><?= htmlspecialchars($consultation['forward_des']) ?></td>
                            </tr>
                            <tr>
                                <th>ระดับความรุนแรง</th>
                                <td>
                                    <?php
                                    if ($consultation['follow_id'] == 1) {
                                        echo "ปกติ <span style='display: inline-block; width: 40px; height: 10px; background-color: green; margin-left: 8px;'></span>";
                                    } elseif ($consultation['follow_id'] == 3) {
                                        echo "เสี่ยง <span style='display: inline-block; width: 40px; height: 10px; background-color: orange; margin-left: 8px;'></span>";
                                    } elseif ($consultation['follow_id'] == 4) {
                                        echo "รุนแรง <span style='display: inline-block; width: 40px; height: 10px; background-color: red; margin-left: 8px;'></span>";
                                    } elseif ($consultation['follow_id'] == 2) {
                                        echo "เฝ้าระวัง <span style='display: inline-block; width: 40px; height: 10px; background-color: yellow; margin-left: 8px;'></span>";
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endwhile; ?>
            <?php else: ?>
                <p>ไม่มีข้อมูลคำปรึกษา</p>
            <?php endif; ?>


        <?php else: ?>
            <p>ไม่พบนักศึกษาที่ต้องการ</p>
        <?php endif; ?>



    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>