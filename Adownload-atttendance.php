<?php


// ✅ Handle attendance download before any HTML output
if (isset($_POST['download_attendance'])) {
    // ✅ Connect to DB
    $conn = new mysqli("localhost", "root", "", "face att");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ✅ Get all attendance records
    $stmt = $conn->prepare("SELECT registrationNumber, date, time, status, courseCode, subjectname, session_id FROM attendance ORDER BY registrationNumber, date DESC, time DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    // ✅ Output headers for Excel download
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=all_students_attendance_" . date("Y-m-d") . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // ✅ Column headers
    echo "Registration Number\tDate\tTime\tStatus\tCourse Code\tSubject Name\tSession ID\n";

    // ✅ Rows
    while ($row = $result->fetch_assoc()) {
        echo "{$row['registrationNumber']}\t{$row['date']}\t{$row['time']}\t{$row['status']}\t{$row['courseCode']}\t{$row['subjectname']}\t{$row['session_id']}\n";
    }

    exit; // ✅ Prevent HTML output
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Admin Dashboard - Download Attendance</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php'; ?>

    <section class="main">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main--content">
            <div style="padding: 40px;">
                <h1 style="margin-bottom: 20px; color: #333;">📥 Download All Students' Attendance</h1>
                <form method="post">
                    <button type="submit" name="download_attendance" style="
                        background-color: #4CAF50;
                        color: white;
                        border: none;
                        padding: 12px 25px;
                        font-size: 16px;
                        border-radius: 8px;
                        cursor: pointer;
                        transition: background-color 0.3s ease, transform 0.2s ease;
                    " onmouseover="this.style.backgroundColor='#45a049'" onmouseout="this.style.backgroundColor='#4CAF50'">
                        Download All Attendance
                    </button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
