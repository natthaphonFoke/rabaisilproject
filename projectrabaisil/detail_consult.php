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

// รับ hn_id จาก URL
$hn_id = $_GET['hn_id'];  // รับ hn_id จาก URL

// ดึงข้อมูลคำปรึกษาจาก consultation table
$consultation_query = "
    SELECT * FROM consultation
    WHERE hn_id = ?
    ORDER BY app_id DESC
    LIMIT 1
";
$consultation_stmt = mysqli_prepare($connect, $consultation_query);
mysqli_stmt_bind_param($consultation_stmt, "s", $hn_id);
mysqli_stmt_execute($consultation_stmt);
$consultation_result = mysqli_stmt_get_result($consultation_stmt);

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($connect);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำปรึกษาใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="detailstyle.css">
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
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3 class="mt-4">ข้อมูลคำปรึกษา:</h3>
        <?php if (mysqli_num_rows($consultation_result) > 0): ?>
            <table class="table table-bordered" style="margin-bottom: 20px;">
                <tbody>
                    <?php while ($consultation = mysqli_fetch_assoc($consultation_result)): ?>
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
                                    echo "รับการส่งต่อ";
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
                            <th>รายละเอียดคำปรึกษาอื่นๆ</th>
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
                    <?php endwhile; ?>
                <tbody>
            </table>

        <?php else: ?>
            <p>ไม่มีข้อมูลคำปรึกษา</p>
        <?php endif; ?>

        <!-- ปุ่มย้อนกลับไปหน้าที่ผ่านมา -->
        <button onclick="history.back()" class="btn btn-secondary mt-3">ย้อนกลับ</button>
        <button onclick="window.location.href='all_users.php';" class="btn btn-success mt-3">ยืนยัน</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>