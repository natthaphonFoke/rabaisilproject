<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <title>Register</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Prompt', sans-serif;
        }

        body {
            background-color: #f8f8f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Main Container */
        .container {
            display: flex;
            width: 900px;
            max-height: 95vh;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
            background-color: white;
        }

        /* Left Section */
        .left-section {
            background-color: #1f5e46;
            color: white;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .left-section h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .left-section p {
            font-size: 14px;
            line-height: 1.6;
        }

        /* Right Section - Scrollable Content */
        .right-section {
            flex: 1;
            padding: 40px;
            background-color: white;
            overflow-y: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input,
        select,
        button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #1f5e46;
        }

        button {
            background-color: #1f5e46;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #174932;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
                max-height: 100%;
            }

            .left-section {
                display: none;
            }
        }

        .login-button {
            font-size: 14px;
            /* ปรับขนาดตัวอักษร */
            color: rgba(0, 0, 0, 0.3);
            /* สีดำที่มีความโปร่งใส 30% */
            text-align: center;
            /* จัดให้อยู่ตรงกลาง */
            cursor: pointer;
            /* แสดงเป็นปุ่มกดได้ */
            text-decoration: none;
            /* ตัดเส้นใต้ลิงก์ */
            transition: color 0.3s ease;
            /* ทำให้สีเปลี่ยนอย่างนุ่มนวล */
        }

        .login-button {
            font-size: 14px; /* ปรับขนาดตัวอักษร */
    color: rgba(0, 0, 0, 0.3); /* สีดำที่มีความโปร่งใส 30% */
    text-align: center; /* จัดให้อยู่ตรงกลาง */
    cursor: pointer; /* แสดงเป็นปุ่มกดได้ */
    text-decoration: none; /* ตัดเส้นใต้ลิงก์ */
    transition: color 0.3s ease; /* ทำให้สีเปลี่ยนอย่างนุ่มนวล */
}

.login-button:hover {
    color: rgba(0, 0, 0, 0.5); /* ทำให้สีเข้มขึ้นเมื่อชี้เมาส์ */
}
    </style>
</head>

<body>
    <div class="container">
        
        <div class="left-section">
            <h1>ยินดีต้อนรับ<p>"พื้นที่ปลอดภัยในการเรียนรู้และสร้างความสำเร็จ"</p></h1>
        </div>

        <div class="right-section">
            <h2>Register</h2>
            <form method="POST" action="register_process.php" onsubmit="return validatePasswords()">

                <label>รหัสนักศึกษา</label>
                <input type="text" name="std_id" required>

                <label>รหัสผ่าน</label>
                <input type="password" name="password" id="password" required>

                <label>ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <small id="error-message" style="color: red; display: none;">รหัสผ่านไม่ตรงกัน</small>

                <label>ชื่อ</label>
                <input type="text" name="first_name" required>

                <label>นามสกุล</label>
                <input type="text" name="last_name" required>

                <label>ชื่อเล่น</label>
                <input type="text" name="nickname" required>

                <label>อีเมล</label>
                <input type="text" name="email" required>

                <label>คณะ</label>
                <select name="faculty_id" id="faculty" required>
                    <option value="" disabled selected>เลือกคณะ</option>
                    <?php
                    include('../dbconnect.php');
                    $sql = "SELECT faculty_id, faculty_name FROM faculties";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['faculty_id']}'>{$row['faculty_name']}</option>";
                    }
                    ?>
                </select>

                <label>สาขา</label>
                <select name="major" id="major" required>
                    <option value="" disabled selected>กรุณาเลือกคณะก่อน</option>
                </select>

                <label>ปีการศึกษา</label>
                <input type="text" name="year" required>

                <label>เพศ</label>
                <select name="gender" required>
                    <option value="1">ชาย</option>
                    <option value="2">หญิง</option>
                    <option value="3">LGBTQAI+</option>
                </select>

                <label>เบอร์โทรศัพท์</label>
                <input type="text" name="phone">

                <button type="submit">Register</button>
                <a href="login.php" class="login-button">ไปที่หน้า Login</a>
            </form>
        </div>
    </div>
    <script>
        document.getElementById("faculty").addEventListener("change", function () {
    const facultyId = this.value;
    const majorDropdown = document.getElementById("major");

    // ล้างตัวเลือกเก่า
    majorDropdown.innerHTML = '<option value="" disabled selected>กำลังโหลด...</option>';

    // ตรวจสอบว่าผู้ใช้เลือกคณะหรือยัง
    if (facultyId) {
        fetch(`get_majors.php?faculty_id=${facultyId}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                // ล้างตัวเลือกก่อนหน้าและเพิ่มใหม่
                majorDropdown.innerHTML = '<option value="" disabled selected>เลือกสาขา</option>';
                data.forEach((major) => {
    const option = document.createElement("option");
    option.value = major.major_name; // ใช้ชื่อ major เป็น value
    option.textContent = major.major_name; // แสดงชื่อ major
    majorDropdown.appendChild(option);
});

            })
            .catch((error) => {
                console.error("Error fetching majors:", error);
                majorDropdown.innerHTML = '<option value="" disabled selected>ไม่สามารถโหลดข้อมูลได้</option>';
            });
    }
});

function validatePasswords() {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const errorMessage = document.getElementById("error-message");

    if (!password || !confirmPassword) {
        errorMessage.style.display = "block";
        errorMessage.textContent = "กรุณากรอกรหัสผ่านและยืนยันรหัสผ่าน";
        return false;
    }

    if (password !== confirmPassword) {
        errorMessage.style.display = "block";
        errorMessage.textContent = "รหัสผ่านไม่ตรงกัน";
        return false;
    }

    errorMessage.style.display = "none";
    return true;
}

    </script>
</body>

</html>