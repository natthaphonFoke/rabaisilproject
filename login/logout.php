<?php
session_start();

// ลบข้อมูลทั้งหมดใน session
session_unset(); // ลบตัวแปรทั้งหมดใน session
session_destroy(); // ทำลาย session

// เก็บข้อความแจ้งเตือนในตัวแปร session เพื่อแสดงในหน้า login
session_start();
$_SESSION['logout_message'] = "ออกจากระบบสำเร็จแล้ว";

// ส่งกลับไปยังหน้า login
header("Location: login.php");
exit();
?>
