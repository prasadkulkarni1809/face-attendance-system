<?php
file_put_contents("log.txt", "mark_absent.php called at " . date("H:i:s") . "\n", FILE_APPEND);

$host = "localhost";
$username = "root";
$password = "";
$database = "face att";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) exit();

date_default_timezone_set("Asia/Kolkata");
$current_date = date("Y-m-d");

// Get latest session and subject
$sql = "SELECT session_id, subject_name FROM active_sub ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) exit();

$row = $result->fetch_assoc();
$session_id = $row['session_id'];
$subject = $row['subject_name'];

// Get all students
$students = $conn->query("SELECT registrationNumber FROM tblstudents");
if (!$students) exit();

while ($student = $students->fetch_assoc()) {
    $regNo = $student['registrationNumber'];

    $check = $conn->query("SELECT * FROM attendance 
        WHERE registrationNumber = '$regNo' 
        AND session_id = '$session_id'");

    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO attendance 
            (registrationNumber, date, session_id, subject, status) 
            VALUES ('$regNo', '$current_date', '$session_id', '$subject', 'Absent')");
    }
}

$conn->close();
exit();
?>
