<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <style>
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

        .container {
            display: flex;
            width: 900px;
            max-height: 95vh;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
            background-color: white;
        }

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

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
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

        button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left-section">
            <h1>ยินดีต้อนรับ<p>"พื้นที่ปลอดภัยในการเรียนรู้และสร้างความสำเร็จ"</p>
            </h1>
        </div>
        <div class="right-section">
            <h2>สมัครสมาชิกของนักจิตวิทยา</h2>
            <form id="registerForm" action="registerindex.php" method="POST">
                <!-- ชื่อผู้ใช้ -->
                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <!-- รหัสผ่าน -->
                <div class="form-group">
                    <label for="password">รหัสผ่าน:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- ยืนยันรหัสผ่าน -->
                <div class="form-group">
                    <label for="confirmPassword">ยืนยันรหัสผ่าน:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>

                <!-- ชื่อ -->
                <div class="form-group">
                    <label for="firstname">ชื่อ:</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>

                <!-- นามสกุล -->
                <div class="form-group">
                    <label for="lastname">นามสกุล:</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>

                <!-- อีเมล -->
                <div class="form-group">
                    <label for="email">อีเมล:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <!-- ปุ่ม Submit -->
                <button type="submit">Register</button>
                <br>

                <!-- ลิงก์ไปหน้า Login -->
                <a href="login.php" class="login-button">ไปที่หน้า Login</a>
            </form>

        </div>
    </div>

</body>

</html>