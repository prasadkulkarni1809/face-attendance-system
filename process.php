<?php
session_start(); // Start session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['course'])) {
        $_SESSION['selected_course'] = $_POST['course']; // Store selected course in session
       // header("Location: display.php"); // Redirect to the display page
        exit();
    } else {
        echo "Please select a course.";
    }
}
?>
