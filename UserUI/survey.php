<?php
session_start();
include '../dbconnect.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_name'])) {
    header("Location: ../login/login.php");
    exit();
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!isset($conn) || $conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบ survey_id
$survey_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($survey_id === 0) {
    echo '<div class="alert alert-warning text-center">ไม่พบแบบสอบถามนี้</div>';
    echo '<a href="index.php" class="btn btn-secondary">กลับไปหน้าหลัก</a>';
    exit();
}

// ใช้ prepared statement เพื่อดึงคำถาม
$stmt = $conn->prepare("SELECT question_id, question_text FROM questions WHERE survey_id = ?");
if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    die('<div class="alert alert-danger">เกิดข้อผิดพลาดในระบบ</div>');
}

$stmt->bind_param("i", $survey_id);
if (!$stmt->execute()) {
    error_log("Statement execution failed: " . $stmt->error);
    die('<div class="alert alert-danger">เกิดข้อผิดพลาดในการดึงข้อมูล</div>');
}

$result = $stmt->get_result();

// ตรวจสอบผลลัพธ์
if ($result->num_rows === 0) {
    echo '<div class="alert alert-warning text-center">ไม่พบคำถามในแบบสอบถามนี้</div>';
    echo '<a href="index.php" class="btn btn-secondary">กลับไปหน้าหลัก</a>';
    exit();
}
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
    <title>แบบสอบถาม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="stylessur.css">
    
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

    <h1>แบบสอบถามเกี่ยวกับความรู้สึก</h1>
    <div class="container">
        <form method="post" action="results.php">
             <!-- เพิ่ม hidden input เพื่อส่ง survey_id -->
             <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">
            <?php
            // แสดงคำถามในแบบสอบถาม
            while ($row = $result->fetch_assoc()) {
                $question_id = $row['question_id'];
                echo '<div class="question-container text-center">';
                echo '<p class="question-text">' . htmlspecialchars($row["question_text"]) . '</p>';

                // Radio buttons สำหรับคำถาม
                echo '<div class="radio-group d-flex justify-content-center align-items-center">
                        <span class="label-left">ไม่เลย</span>

                        <input type="radio" name="response[' . $question_id . ']" id="q' . $question_id . '_size5" value="0" class="radio-size-5" required>
                        <label for="q' . $question_id . '_size5" class="radio-label radio-size-5"></label>

                        <input type="radio" name="response[' . $question_id . ']" id="q' . $question_id . '_size4" value="1" class="radio-size-4">
                        <label for="q' . $question_id . '_size4" class="radio-label radio-size-4"></label>

                        <input type="radio" name="response[' . $question_id . ']" id="q' . $question_id . '_size2" value="2" class="radio-size-2">
                        <label for="q' . $question_id . '_size2" class="radio-label1 radio-size-2"></label>

                        <input type="radio" name="response[' . $question_id . ']" id="q' . $question_id . '_size1" value="3" class="radio-size-1">
                        <label for="q' . $question_id . '_size1" class="radio-label1 radio-size-1"></label>

                        <span class="label-right">เป็นประจำ</span>
                      </div>';
                echo '</div>';
                ?>
                <br><br>    
                <?php
                
            }
            $stmt->close();
            $conn->close();
            ?>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">ยืนยันคำตอบ</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>