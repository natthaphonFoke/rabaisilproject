<?php
include('connect.php'); // ไฟล์เชื่อมต่อฐานข้อมูล
session_start();


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

// ตรวจสอบว่ามีการกดปุ่มยืนยันหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $app_id = $_POST['app_id']; // รับค่า app_id จากฟอร์ม

    // ตรวจสอบว่า app_id มี std_id ที่เกี่ยวข้องใน consultation หรือไม่
    $sql_check_std_id = "SELECT std_id FROM consultation WHERE app_id = ?";
    $stmt_check_std_id = $connect->prepare($sql_check_std_id);
    $stmt_check_std_id->bind_param("s", $app_id);
    $stmt_check_std_id->execute();
    $result_check_std_id = $stmt_check_std_id->get_result();

    if ($result_check_std_id->num_rows > 0) {
        $row_std_id = $result_check_std_id->fetch_assoc();
        $std_id = $row_std_id['std_id'];

        $_SESSION['std_id'] = $std_id;

        // ตรวจสอบว่า std_id มีอยู่ใน consultation_recipients หรือไม่
        $sql_check_recipient = "SELECT hn_id FROM consultation_recipients WHERE std_id = ?";
        $stmt_check_recipient = $connect->prepare($sql_check_recipient);
        $stmt_check_recipient->bind_param("s", $std_id);
        $stmt_check_recipient->execute();
        $result_recipient = $stmt_check_recipient->get_result();

        if ($result_recipient->num_rows === 0) {
            // ถ้าไม่มี std_id ใน consultation_recipients: เพิ่มข้อมูลใหม่
            $sql_insert_recipient = "INSERT INTO consultation_recipients (std_id) VALUES (?)";
            $stmt_insert_recipient = $connect->prepare($sql_insert_recipient);
            $stmt_insert_recipient->bind_param("s", $std_id);
            $stmt_insert_recipient->execute();

            // ดึง hn_id ที่เพิ่งเพิ่ม
            $hn_id = $connect->insert_id;
        } else {
            // หากมี std_id ใน consultation_recipients อยู่แล้ว ให้ดึง hn_id
            $row_recipient = $result_recipient->fetch_assoc();
            $hn_id = $row_recipient['hn_id'];
        }

        // อัปเดต hn_id และ status ใน consultation เฉพาะ app_id
        $sql_update_consultation = "UPDATE consultation SET hn_id = ?, status = 2 WHERE app_id = ?";
        $stmt_update_consultation = $connect->prepare($sql_update_consultation);
        $stmt_update_consultation->bind_param("is", $hn_id, $app_id);
        $stmt_update_consultation->execute();

        // Query เพื่อดึง event_date และ event_time
        $sql_get_event = "SELECT event_date, event_time, channel_id FROM consultation WHERE app_id = ?";
        $stmt_get_event = $connect->prepare($sql_get_event);
        $stmt_get_event->bind_param("s", $app_id);
        $stmt_get_event->execute();
        $result_get_event = $stmt_get_event->get_result();

        if ($result_get_event->num_rows > 0) {
            $event = $result_get_event->fetch_assoc();
            $event_date = $event['event_date'];
            $event_time = $event['event_time'];
            $channel_id = $event['channel_id'];

            // เก็บข้อมูลที่ดึงมาใน session
            $_SESSION['event_date'] = $event_date;
            $_SESSION['event_time'] = $event_time;
            $_SESSION['channel_id'] = $channel_id;

            // Insert ข้อมูลลงตาราง calendar
            $sql_insert_calendar = "INSERT INTO calendar (app_id, event_date, start_time, end_time) VALUES (?, ?, ?, ?)";
            $stmt_insert_calendar = $connect->prepare($sql_insert_calendar);
            $stmt_insert_calendar->bind_param("ssss", $app_id, $event_date, $event_time, $event_time);
            $stmt_insert_calendar->execute();
        }
    }

    // Redirect หลังจากประมวลผลเสร็จ
    header("Location: send_email_app.php");
    exit();
}


// ตรวจสอบว่ามีการกดปุ่มลบหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {  
    $app_id = $_POST['app_id']; // รับค่า app_id จากฟอร์ม

    // เชื่อมต่อกับฐานข้อมูล
    include 'connect.php';

    // ขั้นตอนที่ 1: ค้นหาข้อมูล hn_id จาก consultation โดยใช้ app_id
    $sql_hn = "SELECT hn_id FROM consultation WHERE app_id = ?";
    $stmt_hn = $connect->prepare($sql_hn);
    $stmt_hn->bind_param("s", $app_id);
    $stmt_hn->execute();
    $result_hn = $stmt_hn->get_result();

    if ($row_hn = $result_hn->fetch_assoc()) {
        $hn_id = $row_hn['hn_id']; // ได้ hn_id มาแล้ว

        // ขั้นตอนที่ 2: ใช้ hn_id ไปค้นหา std_id จาก consultation_recipients
        $sql_std = "SELECT std_id FROM consultation_recipients WHERE hn_id = ?";
        $stmt_std = $connect->prepare($sql_std);
        $stmt_std->bind_param("s", $hn_id);
        $stmt_std->execute();
        $result_std = $stmt_std->get_result();

        if ($row_std = $result_std->fetch_assoc()) {
            $std_id = $row_std['std_id']; // ได้ std_id มาแล้ว

            // เก็บ std_id ลงใน SESSION

            $_SESSION['std_id'] = $std_id; // เก็บ std_id ใน SESSION

            // เปลี่ยนสถานะในฐานข้อมูล
            $sql_update_status = "UPDATE consultation SET status = 3 WHERE app_id = ?";
            $stmt_update_status = $connect->prepare($sql_update_status);
            $stmt_update_status->bind_param("s", $app_id);
            $stmt_update_status->execute();

            // ลบข้อมูลจาก calendar โดยใช้ app_id
            $sql_delete_calendar = "DELETE FROM calendar WHERE app_id = ?";
            $stmt_delete_calendar = $connect->prepare($sql_delete_calendar);
            $stmt_delete_calendar->bind_param("s", $app_id);
            $stmt_delete_calendar->execute();

            // Redirect หลังจากประมวลผลเสร็จ
            header("Location: send_email_cancel.php?std_id=$std_id");
            exit();
        } else {
            echo "ไม่พบ std_id ที่เกี่ยวข้องกับ hn_id นี้";
            exit();
        }
    } else {
        echo "ไม่พบ hn_id ที่เกี่ยวข้องกับ app_id นี้";
        exit();
    }

    $stmt_hn->close();
    $stmt_std->close();
    $connect->close();
}


// Query ข้อมูล
$sql = "SELECT 
            c.app_id,
            c.event_date,
            c.event_time,
            c.channel_id,
            s.std_id,
            s.first_name,
            s.last_name
        FROM 
            consultation c
        JOIN 
            student s
        ON 
            c.std_id = s.std_id
        WHERE 
            c.status = 1";

$result = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดนักศึกษา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="appstyle.css">
    <style>
        .badge {
            font-size: 0.75rem;
            padding: 0.25em 0.5em;
            border-radius: 50%;
            color: white;
        }

        .appointment-form {
            text-align: center;
            /* จัดข้อความในฟอร์มให้ชิดซ้าย */
        }

        .appointment-form {
            margin-top: 20px;
            text-align: center;
            /* ชิดซ้าย */
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

    <!-- รายการคำขอนัดหมาย -->
    <div class="container mt-4">

        <div style="display: flex; align-items: center;">
            <h2 class="header" style="margin: 0;">รายการคำขอนัดหมาย</h2>
            <a href="http://localhost/projectrabaisil/day-off.php" class="btn btn-primary mb-3" style="margin-left: auto;"> เลือกวันเวลาปิดรับการนัดหมาย&#128393;</a>
        </div><br>
        <div class="calendar-navigation">
            <button id="prev-month" onclick="changeMonth(-1)" disabled>ก่อนหน้า</button>
            <span id="current-month"></span>
            <button id="next-month" onclick="changeMonth(1)">ถัดไป</button>
        </div>

        <!-- Calendar -->
        <div id="calendar" class="calendar"></div>

        <!-- Appointment Form -->
        

            <div class="appointment-form">
                <label for="appointment-date">กดที่ปฏิทินเพื่อเลือกวัน:</label>
                <input type="date" name="appointment_date" id="appointment-date" readonly>

                <label for="appointment-time">ช่วงเวลาว่างที่เหลือ</label>
                <select id="appointment-time" name="appointment_time">
                    <option value="">-- คลิกเพื่อดู --</option>
                </select><br><br>
                <!-- Table Layout -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>รหัสนักศึกษา</th>
                            <th>ชื่อ</th>
                            <th>วันที่</th>
                            <th>เวลา</th>
                            <th>ช่องทาง</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // แสดงข้อมูลในรูปแบบตาราง
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['std_id'] . '</td>';
                                echo '<td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                                echo '<td>' . $row['event_date'] . '</td>';
                                echo '<td>' . htmlspecialchars(substr($row['event_time'], 0, 5)) . '</td>';
                                echo '<td>';

                                // แสดงข้อความตามค่า channel_id
                                if ($row['channel_id'] == 1) {
                                    echo 'Face to Face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)';
                                } elseif ($row['channel_id'] == 2) {
                                    echo 'ช่องทางออนไลน์';
                                } elseif ($row['channel_id'] == 3) {
                                    echo 'สายด่วน';
                                } else {
                                    echo 'ไม่ระบุช่องทาง';
                                }

                                echo '</td>';
                                echo '<td>';

                                // ฟอร์มสำหรับยืนยัน
                                // ฟอร์มสำหรับยืนยัน
                                echo '<form method="POST" action="" style="display:inline; margin-right: 5px;">';
                                echo '<input type="hidden" name="app_id" value="' . $row['app_id'] . '">'; // ใช้ app_id แทน
                                echo '<button type="submit" name="confirm" class="btn btn-success btn-sm">ยืนยัน</button>';
                                echo '</form>';

                                // ปุ่มลบ
                                echo '<form method="POST" action="" style="display:inline;">';
                                echo '<input type="hidden" name="app_id" value="' . $row['app_id'] . '">';
                                echo '<button type="submit" name="delete" class="btn btn-danger btn-sm">ปฏิเสธ</button>';
                                echo '</form>';

                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">ไม่มีข้อมูลคำขอนัดหมาย</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                let bookedDates = [];
                const appointmentTimes = [
                    "08:00:00",
                    "08:30:00",
                    "09:00:00",
                    "09:30:00",
                    "10:00:00",
                    "10:30:00",
                    "11:00:00",
                    "11:30:00",
                    "13:00:00",
                    "13:30:00",
                    "14:00:00",
                    "14:30:00",
                    "15:00:00",
                    "15:30:00",
                    "16:00:00",
                    "16:30:00",
                    "17:00:00"
                ];
                let currentMonth = new Date().getMonth();
                let currentYear = new Date().getFullYear();
                let selectedDate = null;

                // Fetch booked dates
                fetch('fatch_calendar.php')
                    .then(response => response.json())
                    .then(data => {
                        bookedDates = data;
                        generateCalendar(currentMonth, currentYear);
                    })
                    .catch(error => console.error('Error fetching data:', error));

                function generateCalendar(month, year) {
                    const calendar = document.getElementById('calendar');
                    calendar.innerHTML = '';

                    document.getElementById('prev-month').disabled = (month === 0 && year <= new Date().getFullYear());
                    document.getElementById('next-month').disabled = (month === 11 && year >= new Date().getFullYear() + 1);

                    const monthNames = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const firstDay = new Date(year, month, 1).getDay();

                    document.getElementById('current-month').textContent = `${monthNames[month]} ${year}`;

                    const headerRow = document.createElement('tr');
                    ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'].forEach(day => {
                        const th = document.createElement('th');
                        th.textContent = day;
                        headerRow.appendChild(th);
                    });
                    calendar.appendChild(headerRow);

                    let currentRow = document.createElement('tr');
                    for (let i = 0; i < firstDay; i++) {
                        currentRow.appendChild(document.createElement('td'));
                    }

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    for (let date = 1; date <= daysInMonth; date++) {
                        const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        const cell = document.createElement('td');
                        cell.textContent = date;

                        const currentDate = new Date(dateString);
                        const dayOfWeek = currentDate.getDay(); // หาวันในสัปดาห์ (0 = อาทิตย์, 6 = เสาร์)

                        if (dayOfWeek === 0 || dayOfWeek === 6) {
                            // เพิ่มคลาส weekend ให้กับวันเสาร์-อาทิตย์
                            cell.classList.add('past');
                        } else if (currentDate < today) {
                            cell.classList.add('past');
                        } else {
                            const appointmentCount = bookedDates.filter(appointment => appointment.date === dateString).length;
                            const totalSlots = appointmentTimes.length;

                            if (appointmentCount === 0) {
                                cell.classList.add('available');
                            } else if (appointmentCount < totalSlots) {
                                cell.classList.add('partial');
                            } else {
                                cell.classList.add('booked');
                            }

                            cell.onclick = () => {
                                if (appointmentCount < totalSlots) {
                                    document.getElementById('appointment-date').value = dateString;
                                    updateAvailableTimes(dateString);
                                    selectedDate = dateString;
                                    highlightSelectedDate();
                                }
                            };
                        }

                        currentRow.appendChild(cell);

                        if ((date + firstDay) % 7 === 0) {
                            calendar.appendChild(currentRow);
                            currentRow = document.createElement('tr');
                        }
                    }

                    for (let i = (daysInMonth + firstDay) % 7; i < 7 && i !== 0; i++) {
                        currentRow.appendChild(document.createElement('td'));
                    }
                    calendar.appendChild(currentRow);
                }


                function updateAvailableTimes(selectedDate) {
                    const timeSelect = document.getElementById('appointment-time');
                    timeSelect.innerHTML = '<option value="">-- คลิกเพื่อดู --</option>'; // เริ่มต้นใหม่

                    // ดึงข้อมูลการจองจากฐานข้อมูล
                    fetch('fatch_calendar.php')
                        .then(response => response.json())
                        .then(data => {
                            // กรองข้อมูลการจองในวันที่เลือก
                            const bookedTimes = data.filter(appointment => appointment.date === selectedDate);
                            appointmentTimes.forEach(time => {
                                let isBooked = false;

                                // ตรวจสอบว่าช่วงเวลาถูกจองแล้วหรือยัง
                                bookedTimes.forEach(appointment => {
                                    const appointmentStartTime = appointment.start_time;
                                    const appointmentEndTime = appointment.end_time;

                                    // เปรียบเทียบเวลา
                                    if (time === appointmentStartTime && time === appointmentEndTime) {
                                        isBooked = true; // ถ้าเวลา start_time และ end_time เป็นเลขเดียวกัน
                                    } else if (time >= appointmentStartTime && time < appointmentEndTime) {
                                        isBooked = true; // ถ้าเวลาอยู่ในช่วง start_time ถึง end_time
                                    }
                                });

                                // ถ้าไม่ถูกจองแสดงตัวเลือก
                                if (!isBooked) {
                                    const option = document.createElement('option');
                                    option.value = time;
                                    option.textContent = time;
                                    timeSelect.appendChild(option);
                                }
                            });
                        })
                        .catch(error => console.error('Error fetching data:', error));
                }
                document.querySelector('form').addEventListener('submit', function(e) {
                    const date = document.getElementById('appointment-date').value;
                    const time = document.getElementById('appointment-time').value;
                    const channel = document.getElementById('channel').value;

                    if (!date || !time || !channel) {
                        e.preventDefault(); // หยุดการส่งฟอร์ม
                        alert('กรุณากรอกข้อมูลให้ครบถ้วน!');
                    }
                });



                function changeMonth(direction) {
                    currentMonth += direction;
                    if (currentMonth < 0) {
                        currentMonth = 11;
                        currentYear--;
                    } else if (currentMonth > 11) {
                        currentMonth = 0;
                        currentYear++;
                    }
                    generateCalendar(currentMonth, currentYear);
                }
            </script>
</body>

</html>