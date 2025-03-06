<?php
session_start(); // เริ่ม Session

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
include 'connect.php';
// ดึง student_id จาก Session
$student_id = $_SESSION['student_id'];

// Query ชื่อผู้ใช้จากฐานข้อมูล
$user_query = "SELECT first_name, last_name FROM student WHERE std_id = ?";
$stmt_user = $connect->prepare($user_query);
$stmt_user->bind_param("i", $student_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $user_name = htmlspecialchars($user_row['first_name'] . ' ' . $user_row['last_name']);
} else {
    $user_name = "ไม่พบชื่อผู้ใช้";
}

// Query ข้อมูลการนัดหมายจาก consultation โดยใช้ std_id และดึง app_id, status มาด้วย
$sql = "SELECT app_id, event_date, event_time, channel_id, status 
        FROM consultation 
        WHERE std_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// เช็คว่า std_id นี้มี hn_id ในตาราง consultation_recipients หรือไม่
$sql_check_hn_id = "SELECT hn_id FROM consultation_recipients WHERE std_id = ?";
$stmt_check_hn_id = $connect->prepare($sql_check_hn_id);
$stmt_check_hn_id->bind_param("s", $student_id);
$stmt_check_hn_id->execute();
$result_check_hn_id = $stmt_check_hn_id->get_result();

if ($result_check_hn_id->num_rows > 0) {
    // ถ้ามี hn_id ให้ใช้ hn_id
    $row_hn_id = $result_check_hn_id->fetch_assoc();
    $hn_id = $row_hn_id['hn_id'];

    // ตรวจสอบสถานะจาก hn_id
    $check_query = "SELECT status FROM consultation WHERE hn_id = ?";
    $stmt_check = $connect->prepare($check_query);
    $stmt_check->bind_param("i", $hn_id);
} else {
    // ถ้าไม่มี hn_id ให้ใช้ std_id
    $check_query = "SELECT status FROM consultation WHERE std_id = ?";
    $stmt_check = $connect->prepare($check_query);
    $stmt_check->bind_param("i", $student_id);
}

$stmt_check->execute();
$check_result = $stmt_check->get_result();

$canBook = true; // กำหนดค่าเริ่มต้นว่าสามารถจองได้
while ($row = $check_result->fetch_assoc()) {
    if ($row['status'] != 4 || $row['status'] == 2) {
        // ถ้ามีการนัดหมายที่ยังไม่เสร็จสิ้น
        $canBook = false;
        break;
    }
}

if (!$canBook) {
    echo "<script>alert('ไม่สามารถทำการนัดหมายได้ เนื่องจากยังมีนัดหมายที่ไม่เสร็จสิ้น'); window.location.href = 'user_appdetails.php';</script>";
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นัดหมายใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="appstyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
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
                <span class="ms-auto text-white">
                    <?php echo $user_name; ?>
                </span>
               
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="container-test">
            <div class="title">คำร้องขอนัดหมายรับคำปรึกษา</div><br><br>

            <!-- Calendar Navigation -->
            <div class="calendar-navigation">
                <button id="prev-month" onclick="changeMonth(-1)" disabled>ก่อนหน้า</button>
                <span id="current-month"></span>
                <button id="next-month" onclick="changeMonth(1)">ถัดไป</button>
            </div>

            <!-- Calendar -->
            <div id="calendar" class="calendar"></div>

            <!-- Appointment Form -->
            <form action="app_user_process.php" method="POST">
                <input type="hidden" name="std_id" value="<?php echo htmlspecialchars($student_id); ?>">
                <div class="appointment-form">
                    <label for="appointment-date">กดที่ปฏิทินเพื่อเลือกวันนัด:</label>
                    <input type="date" name="appointment_date" id="appointment-date" readonly>
                    <br>
                    <label for="appointment-time">เลือกเวลา:</label>
                    <select id="appointment-time" name="appointment_time">
                        <option value="">-- เลือกช่วงเวลา --</option>
                    </select>
                    <div class="channel-details"><br>
                        <span class="channel-title">ช่องทางการปรึกษา</span>
                        <div class="category">
                            <select name="channel" id="channel">
                                <option value="1">face to face หอพักเพชรรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)</option>
                                <option value="2">ช่องทางออนไลน์</option>
                                <option value="3">สายด่วน</option>
                            </select>
                        </div>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="save-button">บันทึก >></button>
                    </div>
                </div>
            </form>
        </div>

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
                timeSelect.innerHTML = '<option value="">-- เลือกช่วงเวลา --</option>'; // เริ่มต้นใหม่

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
    </div>
</body>

</html>