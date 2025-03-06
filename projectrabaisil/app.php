<?php
include 'database_connection.php';
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปฏิทินนัดหมาย</title>
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

    <div class="container mt-4">
        <div style="display: flex; align-items: center;">
            <h2 style="margin: 0;">ปฏิทินรายการนัดหมาย</h2>
            <a href="http://localhost/projectrabaisil/appointment.php" class="btn btn-primary mb-3" style="margin-left: auto;">เพิ่มนัดหมายใหม่ &#128393;</a>
        </div><br>

        <div class="calendar-navigation">
            <button id="prev-month" onclick="changeMonth(-1)" disabled>ก่อนหน้า</button>
            <span id="current-month"></span>
            <button id="next-month" onclick="changeMonth(1)">ถัดไป</button>
        </div>
        <div id="calendar" class="calendar"></div>
        <h10>โปรดกดเลือกวันในปฏิทินเพื่อดูรายละเอียดการนัด</h10>
        <div id="appointment-details" class="mt-4" style="display: none;">
            <h3>รายละเอียดนัดหมาย</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>เวลา</th>
                        <th>ชื่อ</th>
                        <th>นามสกุล</th>
                        <th>ช่องทาง</th>
                    </tr>
                </thead>
                <tbody id="appointment-table-body">
                    <!-- ข้อมูลจะถูกเพิ่มด้วย JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    <script>
        let bookedDates = [];
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        // ดึงข้อมูลการจองจากฐานข้อมูล
        fetch('fatch_calendar.php')
            .then(response => response.json())
            .then(data => {
                bookedDates = data;
                generateCalendar(currentMonth, currentYear);
            })
            .catch(error => console.error('Error fetching data:', error));

        let selectedDate = null; // ตัวแปรเก็บวันที่ที่ถูกเลือก

        function showAppointments(date) {
            if (!date) {
                console.error("Date is missing or undefined.");
                return;
            }

            console.log(`Selected date: ${date}`);

            // ลบคลาส 'selected' จากวันที่ก่อนหน้า
            if (selectedDate) {
                const prevSelectedCell = document.querySelector(`td[data-date='${selectedDate}']`);
                if (prevSelectedCell) {
                    prevSelectedCell.classList.remove('selected');
                }
            }

            // เพิ่มคลาส 'selected' ให้กับวันที่ปัจจุบัน
            const selectedCell = document.querySelector(`td[data-date='${date}']`);
            if (selectedCell) {
                selectedCell.classList.add('selected');
            }

            selectedDate = date; // อัปเดตวันที่ปัจจุบันที่เลือก

            // เรียกข้อมูลการนัดหมายจาก PHP
            fetch(`fetch_appointments.php?date=${date}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Response from server:", data);

                    const tableBody = document.getElementById('appointment-table-body');
                    tableBody.innerHTML = ''; // ล้างข้อมูลเก่า

                    data.forEach(appointment => {
                        const row = document.createElement('tr');

                        // เพิ่มข้อมูลในแต่ละเซลล์
                        const timeCell = document.createElement('td');

                        // ตัดเอาแค่ชั่วโมงและนาที
                        const formattedTime = appointment.event_time.substring(0, 5); // ดึงแค่ 'HH:mm' จาก 'HH:mm:ss'
                        timeCell.textContent = formattedTime;

                        row.appendChild(timeCell);

                        const firstNameCell = document.createElement('td');
                        firstNameCell.textContent = appointment.first_name;
                        row.appendChild(firstNameCell);

                        const lastNameCell = document.createElement('td');
                        lastNameCell.textContent = appointment.last_name;
                        row.appendChild(lastNameCell);

                        const methodCell = document.createElement('td');
                        methodCell.textContent = appointment.channel_text;
                        row.appendChild(methodCell);

                        // เพิ่มเซลล์สำหรับปุ่ม "แก้ไข"
                        const editCell = document.createElement('td');
                        const editButton = document.createElement('button');
                        editButton.textContent = 'แก้ไข';
                        editButton.className = 'btn btn-warning btn-sm';

                        // เมื่อกดปุ่ม "แก้ไข" ให้เปลี่ยนหน้าไปยัง editapp.php พร้อมส่ง app_id
                        editButton.onclick = function() {
                            window.location.href = `app_edit.php?app_id=${appointment.app_id}`;
                        };
                        editCell.appendChild(editButton);
                        row.appendChild(editCell);

                        // เพิ่มเซลล์สำหรับปุ่ม "ลบ"
                        const deleteCell = document.createElement('td');
                        const deleteButton = document.createElement('button');
                        deleteButton.textContent = 'ลบ';
                        deleteButton.className = 'btn btn-danger btn-sm';
                        deleteButton.onclick = function() {
                            deleteAppointment(appointment.app_id);
                        };
                        deleteCell.appendChild(deleteButton);
                        row.appendChild(deleteCell);

                        // เพิ่มเซลล์สำหรับปุ่ม "รายละเอียด"
                        const detailCell = document.createElement('td');
                        const detailButton = document.createElement('button');
                        detailButton.textContent = 'รายละเอียด';
                        detailButton.className = 'btn btn-info btn-sm';

                        // เมื่อคลิกปุ่ม "รายละเอียด" ให้เปลี่ยนหน้าไปยังหน้ารายละเอียดพร้อมส่งค่าพารามิเตอร์
                        detailButton.onclick = function() {
                            window.location.href = `detail.php?std_id=${appointment.std_id}&hn_id=${appointment.hn_id}&appointment_id=${appointment.app_id}`;
                        };
                        detailCell.appendChild(detailButton);
                        row.appendChild(detailCell);

                        tableBody.appendChild(row);
                    });

                    document.getElementById('appointment-details').style.display = 'block';
                })
                .catch(error => console.error("Error fetching appointments:", error));
        }

        function generateCalendar(month, year) {
            const calendar = document.getElementById('calendar');
            calendar.innerHTML = ''; // ล้างปฏิทินเก่า

            document.getElementById('prev-month').disabled = (month === 0 && year <= new Date().getFullYear());
            document.getElementById('next-month').disabled = (month === 11 && year >= new Date().getFullYear() + 1);

            const monthNames = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน",
                "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม",
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
                cell.setAttribute('data-date', dateString); // เพิ่ม data-date สำหรับการอ้างอิง

                const currentDate = new Date(dateString);
                const dayOfWeek = new Date(year, month, date).getDay();

                if (currentDate < today || dayOfWeek === 0 || dayOfWeek === 6) {
                    cell.classList.add('past'); // ใช้สไตล์เหมือนวันที่ผ่านมาแล้ว
                } else {
                    const appointmentCount = bookedDates.filter(appointment => appointment.date === dateString).length;
                    const totalSlots = 17;

                    if (appointmentCount === 0) {
                        cell.classList.add('available');
                    } else if (appointmentCount < totalSlots) {
                        cell.classList.add('partial');
                    } else {
                        cell.classList.add('booked');
                    }

                    cell.onclick = () => showAppointments(dateString);
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

        function deleteAppointment(app_id) {
            if (confirm("คุณต้องการจะลบนัดหมายนี้ใช่ไหม")) {
                // ส่งคำขอไปโดยไม่รอการตอบกลับ
                fetch(`delete_appointment.php?id=${app_id}`)
                    .catch(error => console.error("Error deleting appointment:", error));

                // แสดงข้อความแจ้งเตือนสำเร็จทันทีโดยไม่ต้องรอคำตอบ
                location.reload();
                alert("ลบสำเร็จ");
                
                // คุณสามารถทำการรีเฟรชหรือทำอย่างอื่นตามต้องการที่นี่
                // location.reload(); // หากต้องการรีโหลดหน้า
            }
        }
    </script>
</body>

</html>