<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/admin_styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php' ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <?php
session_start(); // Start the session

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=face att", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['course']) && !empty($_POST['course'])) {
        $courseId = $_POST['course'];

        // Save the selected course in a session variable
        $_SESSION['selected_course_id'] = $courseId;

        // Fetch the course name from the database
        $query = $pdo->prepare("SELECT name FROM tblcourse WHERE Id = ?");
        $query->execute([$courseId]);
        $course = $query->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $_SESSION['selected_course_name'] = $course['name'];

            // Redirect to the student attendance page
            header("Location: student_attendance.php");
            exit();
        } else {
            echo "Course not found. Please try again.";
        }
    } else {
        echo "Please select a course.";
    }
} else {
    echo "Invalid Request.";
}
?>
    <?php js_asset(["active_link", "delete_request"]) ?>