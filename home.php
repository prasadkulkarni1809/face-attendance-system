<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>
<style>
        .blue-button {
            background-color: #4C75F2; /* Dark blue */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px; /* Rounded corners */
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    text-align: center;
    display: inline-block;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    margin-left:100px
}

.blue-button:hover {
    background-color: #0f1a58; /* Slightly darker blue */
}
.select-menu {
    width: 100%; /* Full width */
    max-width: 300px; /* Restrict max width */
    padding: 10px;
    font-size: 16px;
    font-weight: bold;
    color: #333;
    background-color: #f9f9f9;
    border: 2px solid #16226c; /* Blue border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.select-menu:disabled {
    background-color: #ddd; /* Grey background when disabled */
    cursor: not-allowed;
    border-color: #aaa; /* Lighter border */
}

.select-menu:focus {
    border-color: #0f1a58; /* Darker blue focus */
    outline: none;
    box-shadow: 0 0 5px rgba(22, 34, 108, 0.5);
}

    </style>
<body>

     

    <?php 
    //   header("Location: home.php");
    include 'includes/topbar.php'; ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <div class="overview">
                <div class="title">
                    <h2 class="section--title">Overview</h2>
                    <select name="date" id="date" class="dropdown">
                        <option value="today">Today</option>
                        <option value="lastweek">Last Week</option>
                        <option value="lastmonth">Last Month</option>
                        <option value="lastyear">Last Year</option>
                        <option value="alltime">All Time</option>
                    </select>
                </div>
                <div class="cards">
                    <div class="card card-1">

                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Registered Students</h5>
                                <h1><?php total_rows('tblstudents') ?></h1>
                            </div>
                            <i class="ri-user-2-line card--icon--lg"></i>
                        </div>

                    </div>
                    <div class="card card-1">

                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Units</h5>
                              
                            </div>
                            <i class="ri-file-text-line card--icon--lg"></i>
                        </div>

                    </div>

                    <div class="card card-1">

                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title">Registered Lectures</h5>
                                <h1><?php total_rows('tbllecture') ?></h1>
                            </div>
                            <i class="ri-user-line card--icon--lg"></i>
                        </div>

                    </div>
                </div>
            </div>
<br><br><br>

<?php
// Set timezone
date_default_timezone_set("Asia/Kolkata");

// Database connection
$host = "localhost";
$database = "face att"; // Fixed space issue
$user = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['selected_subject'])) {
    $selected_subject = $_POST['selected_subject'];

    // First check if there's any active subject
    $checkActiveSql = "SELECT * FROM active_sub WHERE end_time > NOW() LIMIT 1";
    $activeSubject = $pdo->query($checkActiveSql)->fetch(PDO::FETCH_ASSOC);

    if (!$activeSubject) {
        // No active subject found - proceed with creating new session
        
        // Set attendance duration to 10 minutes
        $start_time = date("Y-m-d H:i:s");
        $end_time = date("Y-m-d H:i:s", strtotime($start_time . " +10 minutes"));

        // Generate unique session ID
        $session_id = uniqid('sess_');

        // Insert new active subject
        $sql = "INSERT INTO active_sub (subject_name, start_time, end_time, session_id) 
                VALUES (:subject, :start_time, :end_time, :session_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'subject' => $selected_subject,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'session_id' => $session_id
        ]);

        echo "✅ Attendance started for '$selected_subject' (Active Until: $end_time, Session ID: $session_id)";
    } else {
        // Active subject already exists
        $current_subject = htmlspecialchars($activeSubject['subject_name']);
        $current_end = htmlspecialchars($activeSubject['end_time']);
        echo "❌ Cannot start new session - '$current_subject' is already active until $current_end";
    }
}


// Fetch the currently active subject for students

// Get currently active subject (where current time is between start and end time)
$sql = "SELECT subject_name, end_time FROM active_sub 
        WHERE NOW() BETWEEN start_time AND end_time 
        ORDER BY start_time DESC LIMIT 1";
$stmt = $pdo->query($sql);
$active_subject = $stmt->fetch(PDO::FETCH_ASSOC);

// Format the end time for display
$end_time_display = '';
if ($active_subject) {
    $end_time = new DateTime($active_subject['end_time']);
    $end_time_display = $end_time->format('h:i A'); // Formats to something like "02:30 PM"
}

// Display active subject message (with proper HTML escaping)
$subject_message = $active_subject 
    ? "📢 Active Subject: " . htmlspecialchars($active_subject['subject_name']) . 
      " (Until " . htmlspecialchars($end_time_display) . ")" 
    : "❌ No active subject currently.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
</head>
<body>

<!-- Display current active subject -->
<h2><?php echo $subject_message; ?></h2>

<!-- Form for selecting a subject -->
<form method="post">
    <select name="selected_subject" required style="padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 16px; margin-right: 10px;">
        <option value="">Select a course</option>
        <?php
        try {
            $query = $pdo->query("SELECT name FROM tblcourse");
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
        } catch (PDOException $e) {
            echo '<option disabled>Error fetching courses</option>';
        }
        ?>
    </select>

    <button type="submit" style="
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s ease;
    "
    onmouseover="this.style.background='linear-gradient(135deg, #66BB6A, #388E3C)'"
    onmouseout="this.style.background='linear-gradient(135deg, #4CAF50, #2E7D32)'"
    >
        Start Attendance
    </button>
</form>

</body>
</html>


</body>
</html>






            <div class="table-container">
                <a href="manage-lecture" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Lectures</h2>
                        <button class="add"><i class="ri-add-line"></i>Add lecture</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Phone No</th>
                            
                                <th>Date Registered</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                           $sql = "SELECT * FROM tbllecture"; 

                                $stmt = $pdo->query($sql);
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                if ($result) {
                                    foreach ($result as $row) {
                                        echo "<tr id='rowlecture{$row["Id"]}'>";
                                        echo "<td>" . $row["firstName"] . "</td>";
                                        echo "<td>" . $row["emailAddress"] . "</td>";
                                        echo "<td>" . $row["phoneNo"] . "</td>";
                                       
                                        echo "<td>" . $row["dateCreated"] . "</td>";
                                        echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='lecture'></i></span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No records found</td></tr>";
                                }
                                ?>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="table-container">
                <a href="manage-students" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Students</h2>
                        <button class="add"><i class="ri-add-line"></i>Add Student</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <th>Name</th>
                                <th>Faculty</th>
                                <th>Course</th>
                                <th>Email</th>
                             
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tblstudents";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowstudents{$row["Id"]}'>";
                                    echo "<td>" . $row["registrationNumber"] . "</td>";
                                    echo "<td>" . $row["firstName"] . "</td>";
                                   
                                 
                                    echo "<td>" . $row["email"] . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='students'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No records found</td></tr>";
                            }

                            ?>

                        </tbody>
                    </table>
                </div>

            </div> <!--
            <div class="table-container">
                <a href="create-venue" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Lecture Rooms</h2>
                        <button class="add"><i class="ri-add-line"></i>Add room</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Faculty</th>
                                <th>Current Status</th>
                                <th>Capacity</th>
                                <th>Classification</th>
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tblvenue";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowvenue{$row["Id"]}'>";
                                    echo "<td>" . $row["className"] . "</td>";
                                   
                                    echo "<td>" . $row["currentStatus"] . "</td>";
                                  
                                    echo "<td>" . $row["classification"] . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='venue'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {

                                echo "<tr><td colspan='6'>No records found</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>

            </div> -->
            <div class="table-container">
                <a href="manage-course" style="text-decoration:none;">
                    <div class="title">
                        <h2 class="section--title">Courses</h2>
                        <button class="add"><i class="ri-add-line"></i>Add Course</button>
                    </div>
                </a>
                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Faculty</th>
                                <th>Total Units</th>
                                <th>Total Students</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT 
                        c.name AS course_name,c.Id AS Id,
                        c.facultyID AS faculty,
                        f.facultyName AS faculty_name,
                        COUNT(u.ID) AS total_units,
                        COUNT(DISTINCT s.Id) AS total_students,
                        c.dateCreated AS date_created
                        FROM tblcourse c
                        LEFT JOIN tblunit u ON c.ID = u.courseID
                        LEFT JOIN tblstudents s ON c.courseCode = s.courseCode
                        LEFT JOIN tblfaculty f on c.facultyID=f.Id
                        GROUP BY c.ID";
                            $stmt = $pdo->query($sql);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($result) {
                                foreach ($result as $row) {
                                    echo "<tr id='rowcourse{$row["Id"]}'>";
                                    echo "<td>" . $row["course_name"] . "</td>";
                                
                             
                                    echo "<td>" . $row["total_students"] . "</td>";
                                    echo "<td>" . $row["date_created"] . "</td>";
                                    echo "<td><span><i class='ri-delete-bin-line delete' data-id='{$row["Id"]}' data-name='course'></i></span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No records found</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>

    <?php js_asset(["active_link", "delete_request"]) ?>


</body>

</html>