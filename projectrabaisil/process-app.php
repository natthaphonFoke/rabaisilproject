<?php 
session_start();
header('Content-Type: application/json');

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';

$connect = mysqli_connect($hostname, $username, $password, $database);

if (!$connect) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ข้อมูลผู้ขอรับการปรึกษา
    $student_id = $_POST['std_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $nickname = $_POST['nickname'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    if (strlen($student_id) === 9) {
        $faculty_code = substr($student_id, 2, 4);
    } elseif (strlen($student_id) === 8) {
        $faculty_code = substr($student_id, 0, 2);
    }
    $major = $_POST['major'];
    $admission_year = $_POST['admissionYear'];

    $date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : null;
    $time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : null;

    if (empty($date) || empty($time)) {
        echo json_encode(['error' => 'กรุณากรอกวันที่และเวลานัดหมาย']);
        exit();
    }

    $query = "SELECT hn_id FROM consultation_recipients WHERE std_id = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hn_id);
        $stmt->fetch();
    } else {
        $stmt->close();

        $query = "INSERT INTO student (std_id, first_name, last_name, nickname, phone, gender, faculty_id, major, year) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("sssssssss", $student_id, $first_name, $last_name, $nickname, $phone, $gender, $faculty_code, $major, $admission_year);
        $stmt->execute();
        $stmt->close();

        $query = "INSERT INTO consultation_recipients (std_id) VALUES (?)";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $hn_id = $stmt->insert_id;
        $stmt->close();
    }

    $channel = $_POST['channel'];
    $origin = $_POST['origin'];
    $referral_from = isset($_POST['forward_from']) ? $_POST['forward_from'] : null;
    $status = 2;

    $query = "INSERT INTO consultation (event_date, event_time, hn_id, channel_id, origin_id, Forward_from, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ssisssi", $date, $time, $hn_id, $channel, $origin, $referral_from, $status);
    $stmt->execute();
    $app_id = $stmt->insert_id;
    $stmt->close();

    $query = "INSERT INTO calendar (event_date, app_id, start_time, end_time) VALUES (?, ?, ?, ?)";
    $calendar_stmt = $connect->prepare($query);
    $calendar_stmt->bind_param("siss", $date, $app_id, $time, $time);
    $calendar_stmt->execute();
    $calendar_stmt->close();

    
    $_SESSION['std_id'] = $student_id;
    $_SESSION['appointment_date'] = $date;
    $_SESSION['appointment_time'] = $time;
    $_SESSION['channel'] = $channel;
   
    header('Location: send_email_app2.php');
    exit();
}

mysqli_close($connect);
?>
