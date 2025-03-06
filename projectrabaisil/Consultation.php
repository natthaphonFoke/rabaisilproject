<?php

// รับค่าจาก URL
$hn_id = isset($_GET['hn_id']) ? $_GET['hn_id'] : '';
$app_id = isset($_GET['app_id']) ? $_GET['app_id'] : '';

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
    <title>บันทึกคำปรึกษา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="constyle.css">
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

        <form action="process-consult.php" method="POST"> <!-- เปลี่ยน action -->
            <div class="student">
                <label for="student-id">HN ID:</label>
                <input type="text" id="student-id" name="hn_id" value="<?= htmlspecialchars($hn_id) ?>" readonly>
            </div>
            <input type="hidden" name="app_id" value="<?= htmlspecialchars($app_id) ?>">

            <div class="title">แบบฟอร์มบันทึกการให้คำปรึกษา</div><br>

            <div class="options">
                <span class="options-title">กรณีให้คำปรึกษา</span>
                <div class="category">
                    <label><input type="checkbox" name="consult_case[]" value="การเรียน"><span class="choice">การเรียน</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="เพื่อน"><span class="choice">เพื่อน</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="ครอบครัว"><span class="choice">ครอบครัว</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="สุขภาพ"><span class="choice">สุขภาพ</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="การศึกษาต่อ"><span class="choice">การศึกษาต่อ</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="ความรัก"><span class="choice">ความรัก</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="เศรษฐกิจ"><span class="choice">เศรษฐกิจ</span></label>
                    <label><input type="checkbox" name="consult_case[]" value="อาชีพ/หางานพิเศษ"><span class="choice">อาชีพ/หางานพิเศษ</span></label>
                    <label>
                    <input type="checkbox" name="consult_case[]" value="อื่นๆ">
                    <span class="choice">อื่นๆ</span>
                    <input type="text" name="consult_des">
                    </label>
                </div>
            </div>

            <div class="consult-details">
                <div class="input-box">
                    <br><span class="detail">ปัญหา/สาเหตุ การขอรับการให้คำปรึกษา</span><br><br>
                    <textarea name="symptoms" rows="4" cols="50"></textarea><br><br>
                </div>
                <div class="input-box">
                    <span class="detail">การให้คำปรึกษา/แนะนำ/การช่วยเหลือ</span><br><br>
                    <textarea name="advice" rows="4" cols="50"></textarea><br><br>
                </div>
                <div class="input-box">
                    <span class="detail">ผลสรุปของการแก้ปัญหา</span><br><br>
                    <textarea name="test_results" rows="4" cols="50"></textarea><br><br>
                </div>
                <div class="input-box">
                    <span class="detail">การติดตามผล</span><br><br>
                    <textarea name="follow_des" rows="4" cols="50"></textarea><br><br>
                </div>
            </div>

            <div class="violence-Forward-details">
                <div class="category-violence">
                    <span class="violence-title">ระดับความรุนแรง</span>
                    <select name="follow_id">
                        <option value="0">เลือก</option>
                        <option value="1">ปกติ</option>
                        <option value="2">เฝ้าระวัง</option>
                        <option value="3">เสี่ยง</option>
                        <option value="4">รุนแรง</option>
                    </select>
                </div>
                <div class="category-Forward">
                    <span class="Forward-title">การส่งต่อ</span>
                    <select name="forward_id" onchange="toggleReferralInput(this.value)">
                        <option value="0">เลือก</option>
                        <option value="1">ส่งต่อ</option>
                        <option value="2">ไม่ส่งต่อ</option>
                        <option value="3">อื่นๆ</option>
                    </select>
                    <div id="referral-input" style="display: none;">
                        <input type="text" name="forward_des" placeholder="ระบุรายละเอียด">
                    </div>
                </div>
            </div><br>
            <button type="submit" class="save-button">บันทึก >></button>
        </form>
    </div>

    <script>
        function toggleReferralInput(value) {
            const referralInput = document.getElementById('referral-input');
            referralInput.style.display = value === "3" ? 'block' : 'none';
        }
        function validateForm(event) {
        // กำหนดชื่อช่องข้อมูลเป็นภาษาไทยที่ต้องกรอก
        const fieldNames = {
            'hn_id': 'HN ID',
            'app_id': 'App ID',
            'symptoms': 'ปัญหา/สาเหตุ การขอรับการให้คำปรึกษา',
            'advice': 'การให้คำปรึกษา/แนะนำ/การช่วยเหลือ',
            'test_results': 'ผลสรุปของการแก้ปัญหา',
            'follow_des': 'การติดตามผล',
        };

        let isValid = true;

        // ตรวจสอบช่องข้อความทั่วไป
        for (const fieldName in fieldNames) {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field && field.value.trim() === '') {
                alert(`กรุณากรอกข้อมูลในช่อง ${fieldNames[fieldName]}`);
                isValid = false;
                field.focus();
                break; // หยุดการวนลูปหลังจากพบช่องว่าง
            }
        }

        // ตรวจสอบช่อง "ระดับความรุนแรง"
        const followId = document.querySelector('[name="follow_id"]');
        if (followId && followId.value === '0') {
            alert('กรุณาเลือก ระดับความรุนแรง');
            isValid = false;
            followId.focus();
            event.preventDefault();
            return;
        }

        // ตรวจสอบช่อง "การส่งต่อ"
        const forwardId = document.querySelector('[name="forward_id"]');
        if (forwardId && forwardId.value === '0') {
            alert('กรุณาเลือก การส่งต่อ');
            isValid = false;
            forwardId.focus();
            event.preventDefault();
            return;
        }

        // ตรวจสอบ "กรณีการให้คำปรึกษา" (อย่างน้อยต้องเลือกหนึ่งตัวเลือก)
        const consultCases = document.querySelectorAll('input[name="consult_case[]"]:checked');
        if (consultCases.length === 0) {
            alert('กรุณาเลือก อย่างน้อยหนึ่งตัวเลือก ในกรณีการให้คำปรึกษา');
            isValid = false;
            event.preventDefault();
            return;
        }

        // หยุดการส่งฟอร์มหากมีข้อมูลที่ไม่ถูกต้อง
        if (!isValid) {
            event.preventDefault();
        }
    }

    document.querySelector('form').addEventListener('submit', validateForm);
    </script>
</body>

</html>