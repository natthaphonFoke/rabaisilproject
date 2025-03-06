<?php
session_start(); // เริ่มต้น Session

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอิน ส่งกลับไปยังหน้า login
    exit();
}

// เชื่อมต่อฐานข้อมูล
include 'connect.php';

function getThaiDay($date)
{
    $days = [
        'Sunday' => 'วันอาทิตย์',
        'Monday' => 'วันจันทร์',
        'Tuesday' => 'วันอังคาร',
        'Wednesday' => 'วันพุธ',
        'Thursday' => 'วันพฤหัสบดี',
        'Friday' => 'วันศุกร์',
        'Saturday' => 'วันเสาร์'
    ];
    $dayInEnglish = date('l', strtotime($date)); // ดึงชื่อวันภาษาอังกฤษจากวันที่
    return $days[$dayInEnglish];
}

// ดึง student_id จาก Session
$student_id = $_SESSION['student_id'];

// Query หาข้อมูล hn_id จาก consultation_recipients โดยใช้ std_id
$query_hn = "SELECT hn_id FROM consultation_recipients WHERE std_id = ?";
$stmt_hn = $connect->prepare($query_hn);
$stmt_hn->bind_param("i", $student_id);
$stmt_hn->execute();
$result_hn = $stmt_hn->get_result();

$hn_id = null;
if ($result_hn->num_rows > 0) {
    $hn_row = $result_hn->fetch_assoc();
    $hn_id = $hn_row['hn_id'];  // กำหนดค่า hn_id ถ้ามีข้อมูล
}

// Query ข้อมูลการนัดหมายจาก consultation โดยใช้ std_id และถ้ามี hn_id ก็ใช้แทน
if ($hn_id) {
    // ถ้ามี hn_id ให้ค้นหาจาก hn_id
    $sql = "SELECT app_id, event_date, event_time, channel_id, status FROM consultation WHERE hn_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $hn_id);
} else {
    // ถ้าไม่มี hn_id ให้ค้นหาจาก std_id
    $sql = "SELECT app_id, event_date, event_time, channel_id, status FROM consultation WHERE std_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $student_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการนัดหมายของคุณ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="appstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .appointment-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            background-color: white;
            border-radius: 10px;
            padding: 10px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .appointment-card>div {
            flex: 1;
        }

        .text-start {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .text-muted {
            color: #6c757d;
            font-size: 14px;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => { 
    // Array เก็บ app_id ของคำขอที่ถูกปฏิเสธ
    let rejectedAppointments = [];
    let changedAppointments = []; // เก็บคำขอที่มีการเปลี่ยนแปลง

    // ค้นหา element ที่มี data-status="3" (คำขอถูกปฏิเสธ) และ data-status="5" (คำขอที่มีการเปลี่ยนแปลง)
    document.querySelectorAll(".appointment-card").forEach(card => {
        const status = card.dataset.status;
        const appId = card.dataset.appId;

        if (status == 3) { // ถ้าสถานะคือถูกปฏิเสธ
            rejectedAppointments.push(card); // เก็บ element ของการ์ดที่ถูกปฏิเสธ
        }

    });

    // ถ้ามีคำขอที่ถูกปฏิเสธ
    if (rejectedAppointments.length > 0) {
        const message = `คำขอของคุณถูกปฏิเสธ กรุณาเลือกวันนัดหมายใหม่อีกครั้ง หรือทำการติดต่อมาที่ 
        <a href="https://www.facebook.com/SilpakornPsycho" target="_blank">Facebook Page</a> 
        เรารอที่จะพบคุณเสมอ`;

        // สร้าง element สำหรับแสดงข้อความ
        const confirmationBox = document.createElement("div");
        confirmationBox.innerHTML = message;
        confirmationBox.style.padding = "20px";
        confirmationBox.style.backgroundColor = "#ffeeba";
        confirmationBox.style.border = "1px solid #ccc";
        confirmationBox.style.borderRadius = "8px";
        confirmationBox.style.marginBottom = "15px";

        // เพิ่มกล่องข้อความไปที่ body หรือ container ที่ต้องการ
        document.body.prepend(confirmationBox);

        // ปิดการแสดงข้อความหลังจากคลิก
        confirmationBox.addEventListener("click", () => {
            confirmationBox.remove(); // ลบข้อความออกเมื่อผู้ใช้คลิก
        });

        // ลบข้อมูลในแต่ละการ์ดที่ถูกปฏิเสธ
        rejectedAppointments.forEach(card => {
            const appId = card.dataset.appId; // ดึง app_id ของการ์ด
            // ส่งคำขอ AJAX ไปยังเซิร์ฟเวอร์เพื่อลบ
            fetch('delete_userapp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        app_id: appId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // ลบการ์ดออกจาก DOM
                        card.remove();
                        console.log(`ลบ app_id: ${appId} สำเร็จ`);
                    } else {
                        console.error(`ลบ app_id: ${appId} ไม่สำเร็จ`);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }

    // ถ้ามีคำขอที่มีการเปลี่ยนแปลง
    if (changedAppointments.length > 0) {
        const message = `วันนัดหมายของคุณมีการเปลี่ยนแปลง กรุณาตรวจสอบข้อมูลใหม่ในระบบ`;

        // สร้าง element สำหรับแสดงข้อความ
        const infoBox = document.createElement("div");
        infoBox.innerHTML = message;
        infoBox.style.padding = "20px";
        infoBox.style.backgroundColor = "#fff3cd";
        infoBox.style.border = "1px solid #ffeeba";
        infoBox.style.borderRadius = "8px";
        infoBox.style.marginBottom = "15px";

        // เพิ่มกล่องข้อความไปที่ body หรือ container ที่ต้องการ
        document.body.prepend(infoBox);

        // ปิดการแสดงข้อความหลังจากคลิก
        infoBox.addEventListener("click", () => {
            infoBox.remove(); // ลบข้อความออกเมื่อผู้ใช้คลิก
        });
    }
});


        function cancelAppointment(appId) {
            if (confirm('คุณแน่ใจว่าต้องการยกเลิกการนัดหมายนี้หรือไม่?')) {
                fetch('delete_userapp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            app_id: appId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('ยกเลิกการนัดหมายสำเร็จ');
                            location.reload(); // โหลดหน้าใหม่เพื่อแสดงผลลัพธ์ที่เปลี่ยนแปลง
                        } else {
                            alert('การยกเลิกการนัดหมายไม่สำเร็จ');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
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
                    <li class="nav-item">
                        <a class="nav-link" href="#">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_appdetails.php">นัดหมาย</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">แบบทดสอบ</a>
                    </li>
                    
                </ul>
                <div class="d-flex align-items-center">
                    <span class="user-profile me-2">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Appointment List -->

    <div class="container mt-4">
        <div style="display: flex; align-items: center;">
            <h2 style="margin: 0;">รายการนัดหมายของคุณ</h2>
            <a href="http://localhost/projectrabaisil/app_user.php" class="btn btn-primary mb-3"
                style="margin-left: auto;">ยื่นคำขอนัดหมายใหม่ &#128393;</a>
        </div><br>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <?php if ($row['status'] != 4): // ไม่แสดงข้อมูลที่สถานะเป็น 4 
                ?>
                    <div class="appointment-card d-flex justify-content-between align-items-center p-3 mb-2"
                        data-status="<?php echo $row['status']; ?>"
                        data-app-id="<?php echo $row['app_id']; ?>">
                        <!-- ฝั่งซ้าย: วันและวันที่ -->
                        <div class="text-start">
                            <div><strong><?php echo getThaiDay($row['event_date']); ?></strong></div>
                            <div><?php echo date("d/m/Y", strtotime($row['event_date'])); ?></div>
                        </div>

                        <!-- ตรงกลาง: เวลา และ ช่องทาง -->
                        <div class="text-center">
                        <div>เวลา <?php echo date("H:i", strtotime($row['event_time'])); ?></div>
                            <div>
                                <?php
                                switch ($row['channel_id']) {
                                    case 1:
                                        echo "หอพักเพชรรัตน 2 ชั้น 1";
                                        break;
                                    case 2:
                                        echo "ช่องทางออนไลน์";
                                        break;
                                    case 3:
                                        echo "สายด่วน";
                                        break;
                                    default:
                                        echo "ไม่พบข้อมูลช่องทาง";
                                        break;
                                }
                                ?>
                            </div>
                        </div>

                        <!-- ฝั่งขวา: สถานะ -->
                        <div class="text-end">
                            <span class="text-muted">
                                <?php
                                switch ($row['status']) {
                                    case 2:
                                        echo "สถานะ: ทำการนัดหมายสำเร็จ";
                                        break;
                                    case 1:
                                        echo "สถานะ: รอการยืนยัน";
                                        break;
                                    case 3:
                                        echo "สถานะ: คำขอถูกปฏิเสธกรุณาเลือกใหม่อีกครั้ง";
                                        break;
                                    case 5:
                                        echo "สถานะ: ทำการนัดหมายสำเร็จ";
                                }
                                ?><br>
                            </span>
                            <!-- ปุ่มยกเลิก -->
                            <?php if ($row['status'] == 1): // เฉพาะสถานะที่สามารถยกเลิกได้ 
                            ?>
                                <button class="btn btn-danger btn-sm mt-2" onclick="cancelAppointment(<?php echo $row['app_id']; ?>)">ยกเลิก</button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p>ยังไม่มีข้อมูลการนัดหมาย</p>
        <?php endif; ?>
    </div>

    <!-- Consultation History -->
    <div class="container mt-4">
        <h2>ประวัติการเข้ารับการปรึกษา</h2>
        <?php
        // Query ข้อมูลประวัติการเข้ารับการปรึกษาจากฐานข้อมูล
        $query_hn = "SELECT hn_id FROM consultation_recipients WHERE std_id = ?";
$stmt_hn = $connect->prepare($query_hn);
$stmt_hn->bind_param("i", $student_id);
$stmt_hn->execute();
$result_hn = $stmt_hn->get_result();

$hn_id = null;
if ($result_hn->num_rows > 0) {
    $hn_row = $result_hn->fetch_assoc();
    $hn_id = $hn_row['hn_id'];  // กำหนดค่า hn_id ถ้ามีข้อมูล
}

// ถ้ามี hn_id ใช้การดึงข้อมูลจาก hn_id ถ้าไม่มีก็ให้ดึงจาก std_id
if ($hn_id) {
    // ถ้ามี hn_id ให้ค้นหาจาก hn_id
    $history_query = "SELECT event_date, event_time, channel_id, status 
                      FROM consultation 
                      WHERE hn_id = ? AND status = 4 
                      ORDER BY event_date DESC";
    $stmt_history = $connect->prepare($history_query);
    $stmt_history->bind_param("i", $hn_id);
} else {
    // ถ้าไม่มี hn_id ให้ค้นหาจาก std_id
    $history_query = "SELECT event_date, event_time, channel_id, status 
                      FROM consultation 
                      WHERE std_id = ? AND status = 4 
                      ORDER BY event_date DESC";
    $stmt_history = $connect->prepare($history_query);
    $stmt_history->bind_param("i", $student_id);
}

$stmt_history->execute();
$history_result = $stmt_history->get_result();

        if ($history_result && $history_result->num_rows > 0): ?>
            <?php while ($history_row = $history_result->fetch_assoc()) : ?>
                <div class="appointment-card d-flex justify-content-between align-items-center p-3 mb-2">
                    <!-- ฝั่งซ้าย: วันและวันที่ -->
                    <div class="text-start">
                        <div><strong><?php echo getThaiDay($history_row['event_date']); ?></strong></div>
                        <div><?php echo date("d/m/Y", strtotime($history_row['event_date'])); ?></div>
                    </div>

                    <!-- ตรงกลาง: เวลา และ ช่องทาง -->
                    <div class="text-center">
                    <div>เวลา <?php echo htmlspecialchars(substr($history_row['event_time'], 0, 5)); ?></div>
                        <div>
                            <?php
                            switch ($history_row['channel_id']) {
                                case 1:
                                    echo "หอพักเพชรรัตน 2 ชั้น 1";
                                    break;
                                case 2:
                                    echo "ช่องทางออนไลน์";
                                    break;
                                case 3:
                                    echo "สายด่วน";
                                    break;
                                default:
                                    echo "ไม่พบข้อมูลช่องทาง";
                                    break;
                            }
                            ?>
                        </div>
                    </div>

                    <!-- ฝั่งขวา: สถานะ -->
                    <div class="text-end">
                        <span class="text-muted">
                            <?php
                            switch ($history_row['status']) {
                                case 1:
                                    echo "สถานะ: รอการยืนยัน";
                                    break;
                                case 2:
                                    echo "สถานะ: ทำการนัดหมายสำเร็จ";
                                    break;
                                case 3:
                                    echo "สถานะ: คำขอถูกปฏิเสธ";
                                    break;
                                case 4:
                                    echo "สถานะ: เสร็จสิ้น";
                                    break;
                                default:
                                    echo "สถานะ: ไม่ทราบสถานะ";
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>ยังไม่มีประวัติการเข้ารับการปรึกษา</p>
        <?php endif; ?>
    </div>



</body>

</html>


