<?php
// เชื่อมต่อฐานข้อมูล
include('connect.php');


$sql_notification = "SELECT COUNT(*) AS new_requests FROM consultation WHERE status = 1";
$result_notification = $connect->query($sql_notification);
$notification_count = 0;

if ($result_notification->num_rows > 0) {
    $row_notification = $result_notification->fetch_assoc();
    $notification_count = $row_notification['new_requests'];
}
// รับ `app_id` จาก URL หรือพารามิเตอร์อื่น
$app_id = $_GET['app_id'];

// ดึงข้อมูลที่ต้องการจาก `consultation`
$query = "SELECT hn_id, event_date, event_time, channel_id, origin_id, forward_from FROM consultation WHERE app_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();

// เก็บข้อมูลในตัวแปร
$data = $result->fetch_assoc();

// ตรวจสอบหากไม่มีข้อมูล
if (!$data) {
    echo "ไม่พบข้อมูล";
    exit;
}
?>
<?php
include 'database_connection.php';
$sql_notification = "SELECT COUNT(*) AS new_requests FROM consultation WHERE status = 1";
$result_notification = $connect->query($sql_notification);
$notification_count = 0;

if ($result_notification->num_rows > 0) {
    $row_notification = $result_notification->fetch_assoc();
    $notification_count = $row_notification['new_requests'];
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
        <div class="container-test">
            <div class="title">แก้ไขนัดหมาย</div><br><br>
            <div class="calendar-navigation">
                <button id="prev-month" onclick="changeMonth(-1)" disabled>ก่อนหน้า</button>
                <span id="current-month"></span>
                <button id="next-month" onclick="changeMonth(1)">ถัดไป</button>
            </div>

            <div id="calendar" class="calendar"></div>

            <!---------------------------------------------------->
            <form action="update_appointment.php?app_id=<?php echo $app_id; ?>" method="POST">
                <div class="appointment-form">
                    <label for="appointment-date">กดที่ปฏิทินเพื่อเลือกวันนัด:</label>
                    < <input type="date" id="appointment-date" name="appointment_date" value="<?php echo htmlspecialchars($data['event_date']); ?>">
                        <br>
                        <label for="appointment-time">เลือกเวลา:</label>
                        <select id="appointment-time" name="appointment_time"> <!-- แก้ชื่อเป็น appointment_time -->
                            <?php
                            // สมมติว่าเราดึงเวลาออกจากฐานข้อมูลมาแสดง
                            $times = ["08:00:00", "09:00:00", "10:00:00", "11:00:00", "13:00:00", "14:00:00", "15:00:00"]; // หรือดึงจากฐานข้อมูล
                            foreach ($times as $time) {
                                echo "<option value=\"$time\" " . ($time == $data['event_time'] ? "selected" : "") . ">$time</option>";
                            }
                            ?>
                        </select>
                        <br><br>
                  

                        <div class="channel-details">
                            <span class="channel-title">ช่องทางการปรึกษา</span>
                            <div class="category">
                                <select name="channel" id="channel">
                                    <option value="1" <?php echo $data['channel_id'] == 1 ? 'selected' : ''; ?>>face to face หอพักเพชรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)</option>
                                    <option value="2" <?php echo $data['channel_id'] == 2 ? 'selected' : ''; ?>>ช่องทางออนไลน์</option>
                                    <option value="3" <?php echo $data['channel_id'] == 3 ? 'selected' : ''; ?>>สายด่วน</option>
                                </select>
                            </div>
                        </div>
                        <!-- แก้ไขปุ่มบันทึก -->
                        <div class="button-container">
                            <button type="submit" name="submit" class="save-button">บันทึก ></button>
                        </div>
                </div>

            </form>
        </div>

        <script>
            function submitForm() {
                document.getElementById("myForm").submit();
            }

            function toggleReferralInput(value) {
                var referralInput = document.getElementById("referral-input");
                if (value === "3") {
                    referralInput.style.display = "block";
                } else {
                    referralInput.style.display = "none";
                }
            }
            // วันที่ที่มีการจองแล้ว (เดี่ยวต้องมาเปลี่ยนให้มันดึงมาจากdatabase)
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
            setMinDate();

            function setMinDate() {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById("appointment-date").setAttribute("min", today);
            }

            /*--------------------------------------------ส่วนของปฏิทิน--------------------------------------------------------*/
            function validateForm() {
                const requiredFields = [{
                        id: 'appointment-date',
                        name: 'กรุณาเลือกวันนัดหมาย'
                    },
                    {
                        id: 'appointment-time',
                        name: 'กรุณาเลือกเวลานัดหมาย'
                    },
                    {
                        id: 'std_id',
                        name: 'กรุณากรอกรหัสนักศึกษา'
                    },
                    {
                        id: 'first_name',
                        name: 'กรุณากรอกชื่อ'
                    },
                    {
                        id: 'last_name',
                        name: 'กรุณากรอกนามสกุล'
                    },
                    {
                        id: 'nickname',
                        name: 'กรุณากรอกชื่อเล่น'
                    },
                    {
                        id: 'phone',
                        name: 'กรุณากรอกเบอร์โทร'
                    }
                ];

                let isValid = true;
                let errorMessage = 'ข้อมูลไม่สมบูรณ์:\n';

                // ตรวจสอบช่องกรอกที่จำเป็น
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    if (!element.value.trim()) {
                        isValid = false;
                        errorMessage += `- ${field.name}\n`;
                    }
                });

                // ตรวจสอบการเลือกสาขา
                const major = document.getElementById('major');
                if (!major.value.trim()) {
                    isValid = false;
                    errorMessage += 'กรุณาเลือกสาขาวิชา\n';
                }

                // ตรวจสอบเพศ
                const gender = document.querySelector('input[name="gender"]:checked');
                if (!gender) {
                    isValid = false;
                    errorMessage += 'กรุณากรอกเพศ\n';
                }

                // ตรวจสอบช่องทางการปรึกษา
                const channel = document.getElementById('channel');
                if (!channel.value.trim()) {
                    isValid = false;
                    errorMessage += 'กรุณากรอกช่องทางการปรึกษา\n';
                }

                // หากข้อมูลไม่ครบถ้วน แสดงป๊อปอัพแจ้งเตือน
                if (!isValid) {
                    alert(errorMessage);
                } else {
                    // หากข้อมูลครบถ้วน ส่งฟอร์ม
                    document.querySelector('form').submit();
                }
            }
        </script>
</body>
</html>