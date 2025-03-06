<?php
// เชื่อมต่อฐานข้อมูล
include('connect.php');

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT * FROM consultation ORDER BY hn_id, event_date";
$result = $connect->query($sql);

// สร้างตัวแปรเก็บข้อมูล
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hn_id = $row['hn_id'];
        $month = (int)date('m', strtotime($row['event_date'])); // ดึงเดือนจากวันที่

        if (!isset($data[$hn_id])) {
            $data[$hn_id] = [
                'consult_case' => [],
                'symptoms' => [],
                'advice' => [],
                'test_results' => [],
                'monthly' => array_fill(1, 12, 0) // เตรียมช่องสำหรับข้อมูลรายเดือน
            ];
        }

        // รวมข้อมูลคำปรึกษา (ถ้ามีข้อมูลใหม่ ให้เพิ่มลงใน array)
        if (!in_array($row['consult_case'], $data[$hn_id]['consult_case'])) {
            $data[$hn_id]['consult_case'][] = $row['consult_case'];
        }
        if (!in_array($row['symptoms'], $data[$hn_id]['symptoms'])) {
            $data[$hn_id]['symptoms'][] = $row['symptoms'];
        }
        if (!in_array($row['advice'], $data[$hn_id]['advice'])) {
            $data[$hn_id]['advice'][] = $row['advice'];
        }
        if (!in_array($row['test_results'], $data[$hn_id]['test_results'])) {
            $data[$hn_id]['test_results'][] = $row['test_results'];
        }

        // เพิ่มจำนวนการให้คำปรึกษาในเดือนที่เกี่ยวข้อง
        $data[$hn_id]['monthly'][$month]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปข้อมูลการให้บริการ</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .highlight-yellow { background-color: #ffeb3b; }
        .highlight-green { background-color: #8bc34a; }
        .highlight-red { background-color: #f44336; }
    </style>
</head>
<body>
    <h1>สรุปข้อมูลการให้บริการ</h1>
    <table>
        <thead>
            <tr>
                <th>ลำดับที่</th>
                <th>HN ID</th>
                <th>กรณีให้คำปรึกษา</th>
                <th>อาการ</th>
                <th>ม.ค.</th>
                <th>ก.พ.</th>
                <th>มี.ค.</th>
                <th>เม.ย.</th>
                <th>พ.ค.</th>
                <th>มิ.ย.</th>
                <th>ก.ค.</th>
                <th>ส.ค.</th>
                <th>ก.ย.</th>
                <th>ต.ค.</th>
                <th>พ.ย.</th>
                <th>ธ.ค.</th>
                <th>รวมจำนวนครั้ง</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = 1;
            foreach ($data as $hn_id => $info) {
                echo "<tr>";
                echo "<td>{$index}</td>";
                echo "<td>{$hn_id}</td>";
                echo "<td>" . implode("<br>", $info['consult_case']) . "</td>";
                echo "<td>" . implode("<br>", $info['symptoms']) . "</td>";
                for ($i = 1; $i <= 12; $i++) { // แสดงข้อมูลรายเดือน
                    echo "<td>{$info['monthly'][$i]}</td>";
                }
                $total = array_sum($info['monthly']);
                echo "<td>{$total}</td>";
                echo "</tr>";
                $index++;
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$connect->close();
?>
