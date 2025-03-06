<?php
session_start();
include '../../dbconnect.php';

// รับ survey_id ของแบบสอบถามจาก URL
$survey_id = $_GET['id'];

// ดึงข้อมูลคำถามที่เกี่ยวข้องกับแบบสอบถาม
$sql = "SELECT * FROM questions WHERE survey_id = $survey_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำถาม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="question.css">
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


    <div class="container mt-5">
        <h1>จัดการคำถามในแบบสอบถาม</h1>

        <!-- Section เพิ่มคำถามใหม่ -->
        <div class="section-card">
            <h2>เพิ่มคำถามใหม่</h2>
            <form id="add-question-form">
                <div class="mb-3">
                    <label for="question_text" class="form-label">คำถาม</label>
                    <input type="text" class="form-control" id="question_text" name="question_text" placeholder="กรอกคำถาม" required>
                </div>
                <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">
                <button type="submit" class="btn btn-success">เพิ่มคำถาม</button>
            </form>
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            แก้ไขคำถามสำเร็จ
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Section คำถามในแบบสอบถาม -->
        <div class="section-card">
            <h2>คำถามในแบบสอบถาม</h2>
            <?php if ($result->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="question-text" data-id="<?php echo $row['question_id']; ?>">
                                <?php echo htmlspecialchars($row['question_text']); ?>
                            </span>
                            <div>
                                <a href="#" class="btn btn-sm btn-warning edit-question" data-id="<?php echo $row['question_id']; ?>">แก้ไข</a>
                                <a href="#" class="btn btn-sm btn-danger delete-question" data-id="<?php echo $row['question_id']; ?>">ลบ</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-center">ยังไม่มีคำถามในแบบสอบถามนี้</p>
            <?php endif; ?>
        </div>
    </div>



    <!-- Modal ยืนยันการลบคำถาม -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบคำถาม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณแน่ใจหรือว่าต้องการลบคำถามนี้?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ยืนยันการแก้ไขคำถาม -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">ยืนยันการแก้ไขคำถาม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" id="editInput" placeholder="แก้ไขคำถาม">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" id="confirmEdit">ตกลง</button>
                </div>
            </div>
        </div>
    </div>



    <script>
        // Function to show toast
        function showToast() {
            const toastElement = document.getElementById('successToast');
            const toast = new bootstrap.Toast(toastElement); // ใช้ Bootstrap Toast
            toast.show(); // แสดง Toast
        }

        // Example: เรียกใช้ Toast หลังจากแก้ไขคำถามสำเร็จ
        $('#confirmEdit').on('click', function() {
            const updatedText = $('#editInput').val().trim(); // รับข้อความที่แก้ไข
            $.post('edit-question.php', {
                question_id: currentQuestionId,
                question_text: updatedText
            }, function(response) {
                $('#editModal').modal('hide'); // ปิด Modal
                if (response.success) {
                    $(`.question-text[data-id="${currentQuestionId}"]`).text(updatedText); // อัปเดตข้อความในหน้า
                    showToast(); // แสดง Toast แจ้งเตือน
                } else {
                    alert('เกิดข้อผิดพลาด: ' + response.message);
                }
            }, 'json');
        });

        // เพิ่มคำถามใหม่
        $('#add-question-form').on('submit', function(e) {
            e.preventDefault();
            $.post('add-question.php', $(this).serialize(), function(response) {
                $('#add-alert').fadeIn().delay(2000).fadeOut(); // แสดงข้อความแจ้งเตือน
                location.reload(); // รีโหลดหน้าใหม่
            }, 'json');
        });

        // ลบคำถาม
        $('.delete-question').on('click', function(e) {
            e.preventDefault();
            const questionId = $(this).data('id');
            if (confirm('คุณต้องการลบคำถามนี้หรือไม่?')) {
                $.post('delete-question.php', {
                    question_id: questionId
                }, function(response) {
                    alert(response.message);
                    location.reload();
                }, 'json');
            }
        });

        // แก้ไขคำถาม
        $('.edit-question').on('click', function(e) {
            e.preventDefault();
            const questionId = $(this).data('id');
            const questionTextElement = $(`.question-text[data-id="${questionId}"]`);
            const currentText = questionTextElement.text().trim(); // ตัดช่องว่างข้างหน้า/ข้างหลัง

            // เปลี่ยนเป็น input field
            const inputField = `<input type="text" class="form-control edit-input" value="${currentText}">`;
            const saveButton = `<button class="btn btn-sm btn-success save-question" data-id="${questionId}">บันทึก</button>`;

            questionTextElement.html(inputField);
            $(this).hide(); // ซ่อนปุ่ม "แก้ไข"
            questionTextElement.after(saveButton);

            // บันทึกการแก้ไข
            $('.save-question').on('click', function() {
                const updatedText = questionTextElement.find('.edit-input').val().trim(); // ตัดช่องว่าง
                $.post('edit-question.php', {
                    question_id: questionId,
                    question_text: updatedText
                }, function(response) {
                    alert(response.message);
                    if (response.success) {
                        questionTextElement.text(updatedText); // อัปเดตข้อความใหม่
                        $('.save-question').remove(); // ลบปุ่ม "บันทึก"
                        $('.edit-question[data-id="' + questionId + '"]').show(); // แสดงปุ่ม "แก้ไข" อีกครั้ง
                    }
                }, 'json');
            });
        });

        let currentQuestionId = null;

        // ลบคำถาม
        $('.delete-question').on('click', function(e) {
            e.preventDefault();
            currentQuestionId = $(this).data('id'); // เก็บ question_id ที่ต้องการลบ
            $('#deleteModal').modal('show'); // แสดง Modal
        });

        // ยืนยันการลบ
        $('#confirmDelete').on('click', function() {
            $.post('delete-question.php', {
                question_id: currentQuestionId
            }, function(response) {
                $('#deleteModal').modal('hide'); // ปิด Modal
                if (response.success) {
                    location.reload(); // โหลดหน้าใหม่เมื่อสำเร็จ
                } else {
                    alert('เกิดข้อผิดพลาด: ' + response.message);
                }
            }, 'json');
        });

        // แก้ไขคำถาม
        $('.edit-question').on('click', function(e) {
            e.preventDefault();
            currentQuestionId = $(this).data('id'); // เก็บ question_id ที่ต้องการแก้ไข
            const currentText = $(`.question-text[data-id="${currentQuestionId}"]`).text().trim();
            $('#editInput').val(currentText); // กรอกข้อความใน Input
            $('#editModal').modal('show'); // แสดง Modal
        });

        // ยืนยันการแก้ไข
        $('#confirmEdit').on('click', function() {
            const updatedText = $('#editInput').val().trim(); // ข้อความที่แก้ไข
            $.post('edit-question.php', {
                question_id: currentQuestionId,
                question_text: updatedText
            }, function(response) {
                $('#editModal').modal('hide'); // ปิด Modal
                if (response.success) {
                    $(`.question-text[data-id="${currentQuestionId}"]`).text(updatedText); // อัปเดตข้อความในหน้า
                } else {
                    alert('เกิดข้อผิดพลาด: ' + response.message);
                }
            }, 'json');
        });
    </script>
</body>

</html>