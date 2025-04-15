<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "face att";
$conn = new mysqli($host, $user, $password, $database);

$subject = $_GET['subject'];
$stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present FROM attendance WHERE subjectname = ?");
$stmt->bind_param("s", $subject);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'total' => $result['total'],
    'present' => $result['present']
]);
