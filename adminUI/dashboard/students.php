<?php
// เชื่อมต่อฐานข้อมูล
include '../../dbconnect.php';

// รับค่าค้นหาจากฟอร์ม
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ดึงรายชื่อนักศึกษาตามเงื่อนไขค้นหา
$sql_students = "
    SELECT std_id, first_name, last_name, major 
    FROM student 
    WHERE std_id LIKE ? OR first_name LIKE ? OR last_name LIKE ?
    ORDER BY first_name ASC
";

$stmt = $conn->prepare($sql_students);
$search_param = "%$search%";
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result_students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกนักศึกษา</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <h2 class="text-center mb-4">รายชื่อนักศึกษา</h2>
        <form method="GET" class="mb-3 d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="ค้นหาโดยรหัสนักศึกษาหรือชื่อ" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">ค้นหา</button>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>รหัสนักศึกษา</th>
                        <th>ชื่อ</th>
                        <th>นามสกุล</th>
                        <th>คณะ</th>
                        <th>ดูผลทดสอบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_students->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['std_id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['major']) ?></td>
                            <td class="text-center">
                                <a href="student_results.php?std_id=<?= htmlspecialchars($row['std_id']) ?>" class="btn btn-success btn-sm">ดูผล</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>