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
            <div class="title">นัดหมายใหม่</div><br><br>
            <div class="calendar-navigation">
                <button id="prev-month" onclick="changeMonth(-1)" disabled>ก่อนหน้า</button>
                <span id="current-month"></span>
                <button id="next-month" onclick="changeMonth(1)">ถัดไป</button>
            </div>

            <div id="calendar" class="calendar"></div>

            <!---------------------------------------------------->
            <form action="process-app.php" method="POST">
                <div class="appointment-form">
                    <label for="appointment-date">กดที่ปฏิทินเพื่อเลือกวันนัด:</label>
                    <input type="date" name="appointment_date" id="appointment-date">
                    <br>
                    <label for="appointment-time">เลือกเวลา:</label>
                    <select id="appointment-time" name="appointment_time"> 
                        <option value="">-- เลือกช่วงเวลา --</option>
                    </select>
                    <br><br>
                    <div class="title1">
                        <div class="topic">ผู้ขอรับการปรึกษา</div>
                    </div>

                    <div class="user-details">
                        <div class="input-box">
                            <span class="detail">รหัสนักศึกษา</span>
                            <input name="std_id" type="text" id="std_id" required>
                        </div>
                        <div class="input-box">
                            <span class="detail">คณะ</span>
                            <input name="faculty" type="text" id="faculty" readonly>
                        </div>
                        <div class="input-box">
                            <span class="detail">ชื่อ</span>
                            <input type="text" name="first_name" id="first_name" required>
                        </div>
                        <div class="input-box">
                            <span class="detail">สาขาวิชา</span>
                            <select id="major" name="major">
                                <option value="">-- เลือกสาขา --</option>
                            </select>
                        </div>
                        <div class="input-box">
                            <span class="detail">นามสกุล</span>
                            <input type="text" name="last_name" id="last_name" required>
                        </div>
                        <div class="input-box">
                            <span class="detail">ปีการศึกษา</span>
                            <input name="admissionYear" type="text" id="admissionYear" readonly>
                        </div>
                        <div class="input-box">
                            <span class="detail">ชื่อเล่น</span>
                            <input type="text" name="nickname" id="nickname" required>
                        </div>
                        <div class="input-box">
                            <span class="detail">เบอร์โทร</span>
                            <input type="tel" name="phone" id="phone">
                        </div>
                    </div>
                    <div class="gender-details">
                        <div class="gender-title">เพศ</div>
                        <div class="category">
                            <label for="gender-female">
                                <input type="radio" name="gender" id="gender-female" value="1">
                                <span class="gender">หญิง</span>
                            </label>
                            <label for="gender-male">
                                <input type="radio" name="gender" id="gender-male" value="2">
                                <span class="gender">ชาย</span>
                            </label>
                            <label for="gender-lgbtq">
                                <input type="radio" name="gender" id="gender-lgbtq" value="3">
                                <span class="gender">LGBTQ+</span>
                            </label>
                        </div>
                    </div>

                    <div class="channel-details">
                        <span class="channel-title">ช่องทางการปรึกษา</span>
                        <div class="category">
                            <select name="channel" id="channel">
                                <option value="1">face to face หอพักเพชรรัตน 2 ชั้น 1 (ศูนย์ระบายศิลป์)</option>
                                <option value="2">ช่องทางออนไลน์</option>
                                <option value="3">สายด่วน</option>
                            </select>
                        </div>
                    </div>

                    <div class="origin-details">
                        <span class="origin-title">ที่มาของการให้คำปรึกษา</span>
                        <div class="category">
                            <select name="origin" id="origin" onchange="toggleReferralInput(this.value)">
                                <option value="1">สังเกตเห็นและเข้าไปช่วยเหลือเอง</option>
                                <option value="2">ผู้รับการปรึกษามาด้วยตนเอง</option>
                                <option value="3">ส่งต่อมาจาก</option>
                            </select>

                            <!-- Input สำหรับกรณีเลือก "ส่งต่อมาจาก" -->
                            <div id="referral-input" style="display: none; margin-top: 10px;">
                                <input type="text" name="forward_from" id="forward-from" placeholder="ระบุที่มาของการส่งต่อ">
                            </div>
                        </div>
                    </div>
                    <!-- แก้ไขปุ่มบันทึก -->
                    <div class="button-container">
                        <button type="button" class="save-button" onclick="validateForm()">บันทึก >></button>
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
                "17:00:00"
            ];

            let currentMonth = new Date().getMonth();
            let currentYear = new Date().getFullYear();

            let selectedDate = null; // ตัวแปรเก็บวันที่ที่เลือก

            function generateCalendar(month, year) {
                const calendar = document.getElementById('calendar');
                calendar.innerHTML = ''; // ล้างเนื้อหาก่อนหน้า

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

            // ไฮไลต์วันที่ที่เลือก
            function highlightSelectedDate() {
                const cells = document.querySelectorAll('#calendar td');
                cells.forEach(cell => cell.classList.remove('selected')); // ลบ class ที่เลือกก่อนหน้า

                cells.forEach(cell => {
                    if (cell.textContent && selectedDate) {
                        const [year, month, day] = selectedDate.split('-');
                        if (cell.textContent === String(Number(day))) {
                            cell.classList.add('selected'); // เพิ่ม class ที่เลือก
                        }
                    }
                });
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
                document.getElementById("appointment-date").setAttribute("min", today);
            }

            /*--------------------------------------------ส่วนของปฏิทิน--------------------------------------------------------*/

            // ฟังก์ชันเพื่อดึงข้อมูลจากรหัสนักศึกษา
            document.querySelector('input[name="std_id"]').addEventListener('input', async function() {
                const studentId = this.value;
                console.log("Student ID:", studentId);

                if (studentId.length === 9 || studentId.length === 8) {
                    try {
                        const response = await fetch('check_student.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `std_id=${studentId}`
                        });

                        const data = await response.json();
                        console.log(data); // แสดงข้อมูลที่ได้รับกลับมาใน Console
                        if (data.exists) {
                            // เติมข้อมูลนักศึกษา
                            document.querySelector('input[name="first_name"]').value = data.name || '';
                            document.querySelector('input[name="last_name"]').value = data.surname || '';
                            document.querySelector('input[name="nickname"]').value = data.nickname || '';
                            document.querySelector('input[name="phone"]').value = data.phone || '';
                            document.querySelector('input[name="admissionYear"]').value = data.admissionYear || '';
                            document.querySelector('input[name="faculty"]').value = data.facultyName || 'ไม่มีข้อมูล';

                            // ดึงข้อมูลเพศและติ๊ก radio button
                            const gender = data.gender;
                            if (gender === 1) {
                                document.querySelector('#gender-female').checked = true;
                            } else if (gender === 2) {
                                document.querySelector('#gender-male').checked = true;
                            } else if (gender === 3) {
                                document.querySelector('#gender-lgbtq').checked = true;
                            }
                            // ดึงข้อมูลสาขาวิชาโดยใช้ชื่อสาขาที่ได้รับมา
                            document.querySelector('select[name="major"]').innerHTML = `
                    <option value="${data.major}">${data.major || 'ไม่มีข้อมูล'}</option>
                `;
                        } else {
                            // ถ้าไม่พบข้อมูล
                            let facultyId = studentId.length === 9 ? studentId.substring(2, 4) : studentId.substring(0, 2);
                            await handleNoStudentFound(studentId, facultyId);
                        }
                    } catch (error) {
                        console.error('Error:', error);

                    }
                } else {
                    document.getElementById('studentInfo').innerHTML = ''; // ล้างข้อมูลถ้ารหัสไม่ครบ
                }
            });

            async function handleNoStudentFound(studentId, facultyId) {
                const response = await fetch('fetch_student_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `std_id=${studentId}`
                });
                const data = await response.json();
                console.log("No student found data:", data); // แสดงข้อมูลที่ได้รับกลับมาใน Console

                if (data.faculty && data.admissionYear) {
                    document.querySelector('input[name="faculty"]').value = data.faculty;
                    document.querySelector('input[name="admissionYear"]').value = data.admissionYear;
                    fetchMajors(facultyId);
                } else {
                    alert(data.error);
                }
            }


            // ฟังก์ชันดึงข้อมูลสาขาโดยใช้รหัสคณะ
            // ฟังก์ชันดึงข้อมูลสาขาโดยใช้รหัสคณะ
            function fetchMajors(facultyId) {
                fetch('fetch_major.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `faculty_id=${facultyId}` // ส่ง facultyId
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data); // ใช้เพื่อตรวจสอบว่า response ถูกต้องหรือไม่

                        if (data.error) {
                            console.error(data.error); // แสดง error ใน console
                        } else {
                            const majorSelect = document.querySelector('#major');
                            majorSelect.innerHTML = '<option value="">-- เลือกสาขา --</option>'; // ลบตัวเลือกเดิม

                            // สร้างตัวเลือกสำหรับสาขา
                            data.majors.forEach(major => {
                                const option = document.createElement('option');
                                option.value = major; // ใช้ชื่อสาขาโดยตรง
                                option.textContent = major; // แสดงชื่อสาขา
                                majorSelect.appendChild(option); // เพิ่มตัวเลือกใหม่ใน select
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
            /*------------------------- จบการดึงข้อมูล ----------------------------------------*/
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