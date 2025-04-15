<?php


// ✅ Handle attendance download before any HTML output
if (isset($_POST['download_attendance'])) {
    if (!isset($_SESSION['user']['email'])) {
        echo "Unauthorized access.";
        exit;
    }

    $email = $_SESSION['user']['email'];
    $conn = new mysqli("localhost", "root", "", "face att");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt1 = $conn->prepare("SELECT registrationNumber FROM tblstudents WHERE email = ?");
    $stmt1->bind_param("s", $email);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    if ($result1->num_rows === 0) {
        echo "No student found.";
        exit;
    }

    $row1 = $result1->fetch_assoc();
    $regNo = $row1['registrationNumber'];

    $stmt2 = $conn->prepare("SELECT registrationNumber, date, time, status, courseCode, subjectname, session_id FROM attendance WHERE registrationNumber = ?");
    $stmt2->bind_param("s", $regNo);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    // ✅ Output headers before any HTML or output
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=attendance_{$regNo}_" . date("Y-m-d") . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "Registration Number\tDate\tTime\tStatus\tCourse Code\tSubject Name\tSession ID\n";
    while ($row = $result2->fetch_assoc()) {
        echo "{$row['registrationNumber']}\t{$row['date']}\t{$row['time']}\t{$row['status']}\t{$row['courseCode']}\t{$row['subjectname']}\t{$row['session_id']}\n";
    }

    exit; // ✅ Stop executing further HTML
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Lecture Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php'; ?>

    <section class="main">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main--content">
            <div style="padding: 40px;">
                <h1 style="margin-bottom: 20px; color: #333;">📥 Download Your Attendance Record</h1>
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
                        Download Attendance
                    </button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
