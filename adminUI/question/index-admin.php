<?php
include '../../dbconnect.php';

$sql = "SELECT ID, name, img FROM form";
$result = $conn->query($sql);
?>
<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_name'])) {
    header("Location: ../../login/login copy.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกแบบสอบถาม</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="card.css">
    <!--icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
/* User Profile Text */
.user-profile {
    font-size: 16px;
    font-weight: 500;
    color:rgb(255, 255, 255); /* สีข้อความ */
}

/* Margin */
.me-2 {
    margin-right: 10px;
}

/* Logout Link */
.d-flex a {
    text-decoration: none; /* เอาเส้นใต้ข้อความออก */
    font-size: 14px;
    color: #dc3545; /* สีแดง */
    font-weight: bold;
    transition: color 0.3s ease;
}

.d-flex a:hover {
    color:rgb(95, 25, 32); /* สีแดงเข้มเมื่อ hover */
}

</style>
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
                    <!-- เมนู Dropdown แบบ Select -->
                    <li class="nav-item">
                        <select class="form-select" onchange="navigateToDashboard(this)">
                            <option value="" disabled selected>เลือก Dashboard</option>
                            <option value="dashboard.php">Dashboard</option>
                            <option value="dashboardrecord.php">Dashboard Record</option>
                        </select>
                    </li>
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

    <script>
        function navigateToDashboard(selectElement) {
            // ตรวจสอบว่าเลือกตัวเลือกหรือไม่
            if (selectElement.value) {
                window.location.href = selectElement.value; // นำผู้ใช้ไปที่ลิงก์ที่เลือก
            }
        }
    </script>
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
                    echo '<div class="col-md-4 mb-4">';
                    echo '<a href="Manage-Questions.php?id=' . $row["ID"] . '" class="text-decoration-none">';
                    echo '<div class="survey-card">';
                    echo '<img src="' . $row["img"] . '" alt="' . $row["name"] . '">';
                    echo '<p class="survey-title">' . $row["name"] . '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo "<p class='text-center'>ไม่มีแบบสอบถามในระบบ</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>