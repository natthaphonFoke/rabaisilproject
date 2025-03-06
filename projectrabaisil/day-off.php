<?php

include('connect.php');


// ลบข้อมูลในกรณีที่มีคำขอ POST มาจาก JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_date']) && isset($_POST['start_time'])) {
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];

    $sql = "DELETE FROM calendar WHERE event_date = ? AND start_time = ? AND app_id IS NULL";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param('ss', $event_date, $start_time);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    exit; // สิ้นสุดการทำงานหลังจากส่ง JSON กลับ
}

// ดึงข้อมูลวันที่ปิดรับนัดหมายที่ยังไม่ผ่าน
$sql = "SELECT event_date, start_time, end_time 
        FROM calendar 
        WHERE app_id IS NULL AND event_date >= CURDATE()"; // เพิ่มเงื่อนไขกรองวันที่
$result = $connect->query($sql);

$closedAppointments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $closedAppointments[] = $row;
    }
    $result->close();
}

$connect->close();
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
            <div class="title">เลือกวันเพื่อปิดรับการนัดหมาย</div><br><br>
            <div class="calendar-navigation">
                <button id="prev-month" onclick="changeMonth(-1)" disabled>ก่อนหน้า</button>
                <span id="current-month"></span>
                <button id="next-month" onclick="changeMonth(1)">ถัดไป</button>
            </div>

            <div id="calendar" class="calendar"></div>

            <!---------------------------------------------------->
            <form action="process-calendar.php" method="POST">
                <div class="appointment-form">
                    <label for="event_date">กดที่ปฏิทินเพื่อเลือกวันนัด:</label>
                    <input type="date" name="event_date" id="event_date" required> <!-- เพิ่ม required -->
                    <br>
                    <label for="start-time">ตั้งแต่เวลา:</label>
                    <select id="start-time" name="start_time" required>
                        <option value="">-- เลือกเวลาเริ่มต้น --</option>
                    </select>
                    <br>
                    <label for="end-time">ถึงเวลา:</label>
                    <select id="end-time" name="end_time" required>
                        <option value="">-- เลือกเวลาสิ้นสุด --</option>
                    </select>
                    <div class="button-container">
                        <button type="submit" class="save-button">บันทึก >></button> <!-- เปลี่ยนจาก type="button" เป็น type="submit" -->
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="container mt-5">
        <h3>วันที่ทำการปิดรับนัดหมายทั้งหมด</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>เวลาเริ่มต้น</th>
                    <th>เวลาสิ้นสุด</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($closedAppointments)): ?>
                    <?php foreach ($closedAppointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['start_time']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['end_time']); ?></td>
                            <td>
                                <button class="btn btn-danger delete-btn"
                                    data-date="<?php echo htmlspecialchars($appointment['event_date']); ?>"
                                    data-start="<?php echo htmlspecialchars($appointment['start_time']); ?>">
                                    ลบ
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">ไม่มีข้อมูลการปิดรับนัดหมาย</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // วันที่ที่มีการจองแล้ว
        let bookedDates = [];

        // ดึงข้อมูลการจองจากฐานข้อมูลผ่านไฟล์ PHP
        fetch('fatch_calendar.php')

            .then(response => response.json())
            .then(data => {
                bookedDates = data;
                generateCalendar(currentMonth, currentYear);
            })
            .catch(error => console.error('Error fetching data:', error));

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
            "17:00:00",
            "17:30:00"
        ];

        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let selectedDate = null; // ตัวแปรเก็บวันที่ที่เลือก

        function generateCalendar(month, year) {
            const calendar = document.getElementById('calendar');
            calendar.innerHTML = ''; // ล้างเนื้อหาก่อนหน้า

            document.getElementById('prev-month').disabled = (month === 0 && year <= new Date().getFullYear());
            document.getElementById('next-month').disabled = (month === 11 && year >= new Date().getFullYear() + 1);

            const monthNames = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม",
                "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];
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

                // เพิ่มสีให้วันเสาร์-อาทิตย์เหมือนวันที่ผ่านมาแล้ว
                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    cell.classList.add('past'); // ใช้สีเหมือนวันที่ผ่านมาแล้ว
                } else if (currentDate < today) {
                    cell.classList.add('past'); // สีสำหรับวันที่ผ่านมาแล้ว
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
                            document.getElementById('event_date').value = dateString;
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

        // ไฮไลต์วันที่ที่เลือก
        function highlightSelectedDate() {
            const cells = document.querySelectorAll('#calendar td');
            cells.forEach(cell => cell.classList.remove('selected'));

            cells.forEach(cell => {
                if (cell.textContent && selectedDate) {
                    const [year, month, day] = selectedDate.split('-');
                    if (cell.textContent === String(Number(day))) {
                        cell.classList.add('selected');
                    }
                }
            });
        }

        function updateAvailableTimes(selectedDate) {
            const startTimeSelect = document.getElementById('start-time');
            const endTimeSelect = document.getElementById('end-time');

            startTimeSelect.innerHTML = '<option value="">-- เลือกเวลาเริ่มต้น --</option>';
            endTimeSelect.innerHTML = '<option value="">-- เลือกเวลาสิ้นสุด --</option>';

            // กรองเวลาที่ถูกจองในวันที่ที่เลือก
            const unavailableTimes = bookedDates
                .filter(appointment => appointment.date === selectedDate)
                .reduce((times, appointment) => {
                    let startIndex = appointmentTimes.indexOf(appointment.start_time);
                    let endIndex = appointmentTimes.indexOf(appointment.end_time);

                    // ตรวจสอบว่า start_time และ end_time เป็นเวลาเดียวกันหรือไม่
                    if (startIndex === endIndex) {
                        times.push(appointment.start_time); // เพิ่มเวลานั้นใน unavailableTimes
                    } else if (startIndex !== -1 && endIndex !== -1) {
                        for (let i = startIndex; i < endIndex; i++) {
                            times.push(appointmentTimes[i]);
                        }
                    }
                    return times;
                }, []);

            const availableTimes = appointmentTimes.filter(time => !unavailableTimes.includes(time));

            // สร้างตัวเลือกเวลาที่ว่างใน start_time
            availableTimes.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                startTimeSelect.appendChild(option);
            });

            // อัปเดต end_time เมื่อเลือก start_time
            startTimeSelect.addEventListener('change', () => {
                const selectedStartTime = startTimeSelect.value;
                endTimeSelect.innerHTML = '<option value="">-- เลือกเวลาสิ้นสุด --</option>';

                availableTimes.forEach(time => {
                    if (time > selectedStartTime) {
                        const option = document.createElement('option');
                        option.value = time;
                        option.textContent = time;
                        endTimeSelect.appendChild(option);
                    }
                });
            });
        }


        generateCalendar(currentMonth, currentYear);

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
            document.getElementById("event_date").setAttribute("min", today);
        }
        $(document).ready(function() {
            // เมื่อคลิกปุ่มลบ
            $('.delete-btn').click(function() {
                const eventDate = $(this).data('date');
                const startTime = $(this).data('start');

                if (confirm('คุณต้องการลบการปิดรับนัดหมายนี้หรือไม่?')) {
                    // ส่งคำขอผ่าน Ajax
                    $.ajax({
                        url: '', // ใช้ URL เดิมของไฟล์ปัจจุบัน
                        type: 'POST',
                        data: {
                            event_date: eventDate,
                            start_time: startTime
                        },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                alert('ลบข้อมูลสำเร็จ');
                                location.reload(); // โหลดหน้าใหม่เพื่ออัปเดตตาราง
                            } else {
                                alert('เกิดข้อผิดพลาด: ' + (res.error || 'ไม่ทราบสาเหตุ'));
                            }
                        },
                        error: function() {
                            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>