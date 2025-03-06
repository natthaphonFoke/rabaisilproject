<?php
session_start();

// ดึงข้อมูลจาก Query String
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
$total_score = isset($_GET['total_score']) ? intval($_GET['total_score']) : 0;
$risk_level = isset($_GET['risk_level']) ? htmlspecialchars($_GET['risk_level']) : 'ไม่ระบุ';

// แปลง Survey ID เป็นชื่อแบบทดสอบ
$survey_name = '';
switch ($survey_id) {
    case 1:
        $survey_name = 'แบบทดสอบซึมเศร้า';
        break;
    case 2:
        $survey_name = 'แบบทดสอบวิตกกังวล';
        break;
    case 3:
        $survey_name = 'แบบทดสอบความเครียด';
        break;
    default:
        $survey_name = 'ไม่พบชื่อแบบทดสอบ';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการประเมินความซึมเศร้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }
        .result-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .result-header h1 {
            color:rgb(48, 185, 71);
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .result-content {
            text-align: center;
            font-size: 1.2rem;
        }
        .result-content .score {
            font-size: 2.5rem;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }
        .result-content .risk-level {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
        }
        .action-buttons {
            margin-top: 20px;
            text-align: center;
        }
        .action-buttons .btn {
            margin: 5px;
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
                <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">แบบทดสอบ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../projectrabaisil/user_appdetails.php">นัดหมาย</a></li>
                </ul>

                <div class="d-flex align-items-center">
                    <span class="user-profile me-2"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../../login/logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="result-container">
        <div class="result-header">
            <h1>ผลการประเมิน<p><?php echo $survey_name; ?></p></h1>
            
        </div>
        <div class="result-content">
            <p>คะแนนรวม:</p>
            <div class="score"><?php echo $total_score; ?></div>
            <p>ระดับความเสี่ยง:</p>
            <div class="risk-level"><?php echo $risk_level; ?></div>
        </div>
        <div class="action-buttons">
            <a href="https://www.facebook.com/SilpakornPsycho" class="btn btn-primary">ขอรับคำปรึกษา</a>
            <a href="index.php" class="btn btn-secondary">กลับไปหน้าแบบสอบถาม</a>
        </div>
    </div>
</body>
</html>
