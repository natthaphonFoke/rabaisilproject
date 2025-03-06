<?php
// เชื่อมต่อฐานข้อมูล
include '../../dbconnect.php';

// รับค่า std_id จากพารามิเตอร์ URL
$std_id = isset($_GET['std_id']) ? intval($_GET['std_id']) : 0;

// ดึงข้อมูลนักศึกษาและผลการทดสอบ
$sql = "
    SELECT 
        s.std_id, s.first_name, s.last_name, s.major,
       (SELECT COUNT(*) FROM test_results tr WHERE tr.std_id = s.std_id) AS total_tests,
        tr.result_id, tr.survey_id, tr.total_score, tr.severity
    FROM student s
    LEFT JOIN test_results tr ON s.std_id = tr.std_id
    WHERE s.std_id = ?
    ORDER BY tr.total_score DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $std_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการทำแบบทดสอบ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .table th, .table td {
            text-align: center;
        }
        .severity-low {
            color: green;
            font-weight: bold;
        }
        .severity-medium {
            color: orange;
            font-weight: bold;
        }
        .severity-high {
            color: red;
            font-weight: bold;
        }
        .sortable:hover {
            cursor: pointer;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <h2 class="text-center mb-4">ผลการทำแบบทดสอบของนักศึกษา</h2>
        <a href="students.php" class="btn btn-primary mb-3">กลับไปเลือกนักศึกษา</a>
        <?php if ($student) { ?>
            <div class="mb-3 p-3 bg-light rounded">
                <p><strong>รหัสนักศึกษา:</strong> <?= htmlspecialchars($student['std_id']) ?></p>
                <p><strong>ชื่อ:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
                <p><strong>คณะ:</strong> <?= htmlspecialchars($student['major']) ?></p>
                <p><strong>จำนวนครั้งที่ทำแบบทดสอบ:</strong> <?= htmlspecialchars($student['total_tests']) ?> ครั้ง</p>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="testTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="sortable" data-column="0">ครั้งที่ <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="1">Survey ID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="2">คะแนนรวม <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="3">ระดับความรุนแรง <i class="fas fa-sort"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1; 
                        while ($row = $result->fetch_assoc()) { 
                            $severity_class = ($row['severity'] === 'สูง') ? 'severity-high' : (($row['severity'] === 'ปานกลาง') ? 'severity-medium' : 'severity-low');
                        ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($row['survey_id']) ?></td>
                                <td><?= htmlspecialchars($row['total_score']) ?></td>
                                <td class="<?= $severity_class ?>"><?= htmlspecialchars($row['severity']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-danger text-center">ไม่พบนักศึกษาหรือไม่มีผลการทดสอบ</p>
        <?php } ?>
    </div>
    <script>
        $(document).ready(function() {
            $(".sortable").click(function() {
                var table = $("#testTable tbody");
                var rows = table.find("tr").toArray().sort(comparator($(this).index()));
                this.asc = !this.asc;
                if (!this.asc) rows = rows.reverse();
                table.append(rows);
            });
            function comparator(index) {
                return function(a, b) {
                    var valA = $(a).children("td").eq(index).text();
                    var valB = $(b).children("td").eq(index).text();
                    return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
                };
            }
        });
    </script>
</body>
</html>
