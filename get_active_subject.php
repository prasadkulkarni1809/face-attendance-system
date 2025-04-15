<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "face att";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) exit();

date_default_timezone_set("Asia/Kolkata");

$sql = "SELECT session_id, subject_name, start_time FROM active_sub ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(null);
}
$conn->close();
?>
