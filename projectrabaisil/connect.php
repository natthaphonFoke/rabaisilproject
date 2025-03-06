<?php
/*if($open_connect != 1){
    die(header('Location: appointment.php'));
}*/

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rabaisrin_db';
$port = NULL;
$socket = NULL;
$connect = mysqli_connect($hostname, $username, $password, $database);

if(!$connect){
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว" . mysqli_connect_error($connect));
}
else{
    mysqli_set_charset($connect,'utf8');
}
?>