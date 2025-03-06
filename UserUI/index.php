<?php
session_start();
include '../dbconnect.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_name'])) {
    header("Location: ../login/login.php");
    exit();
}



// ดึงข้อมูลแบบสอบถาม
$sql = "SELECT ID, name, img FROM form";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// ดึง Student ID จาก Session
$student_id = $_SESSION['student_id'];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกแบบสอบถาม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="card.css">
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
                    <a href="../login/logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="title-container">
        <div class="title01">
            <h1>แบบทดสอบ</h1>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $img_src = htmlspecialchars($row["img"], ENT_QUOTES, 'UTF-8');
                    $survey_name = htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8');
                    echo '<div class="col-md-4 mb-4">';
                    echo '<a href="survey.php?id=' . $row["ID"] . '" class="text-decoration-none">';
                    echo '<div class="survey-card">';
                    echo '<img src="' . $img_src . '" alt="' . $survey_name . '">';
                    echo '<p class="survey-title">' . $survey_name . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-warning text-center">ไม่มีแบบสอบถามในระบบ</div>';
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>