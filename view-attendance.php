<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>Student Attendance</title>

    <link rel="stylesheet" href="resources/assets/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
    

    <style>
        .table-container {
            max-height: 80vh;
            overflow-y: auto;
            margin-top: 1rem;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #fff;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .delete {
            cursor: pointer;
            color: crimson;
        }

        .edit {
            cursor: pointer;
            color: #1976d2;
            margin-right: 10px;
        }

        .formDiv-- {
            margin-top: 2rem;
        }

        .formDiv-- form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .formDiv-- input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .formDiv-- input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .form-title p {
            font-size: 20px;
            font-weight: bold;
        }

        .close, .close-edit {
            font-size: 24px;
            cursor: pointer;
        }

        #editStatusModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            display: none;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        #overlayEdit {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .filter-container {
            margin-bottom: 10px;
            display: flex;
            justify-content: flex-end;
        }

        .filter-container select {
            padding: 8px 12px;
            font-size: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .analytics-container {
    background: #f7f7f7;
    padding: 25px;
    margin-top: 30px;
    border-radius: 15px;
    width: 60%;
    margin-left: auto;
    margin-right: auto;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    font-family: 'Segoe UI', sans-serif;
}

.analytics-container h3 {
    font-size: 20px;
    margin-bottom: 15px;
}

.analytics-container p {
    font-size: 16px;
    margin: 8px 0;
}

.analytics-container select {
    padding: 6px 12px;
    font-size: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}   
    </style>
</head>

<body>

<?php
$host = "localhost";
$database = "face att";
$user = "root";
$password = "";
$conn = new mysqli($host, $user, $password, $database);




if (isset($_SESSION['user']['email'])) {
    $email = $_SESSION['user']['email'];

    // Get the registration number using the email
    $result = $conn->query("SELECT registrationNumber FROM tblstudents WHERE email = '$email'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $registration = $row['registrationNumber'];
    } else {
        die("No registration number found for the email.");
    }
} else {
    die("User not logged in.");
}




?>

<?php include "Includes/topbar.php"; ?>

<section class="main">
    <?php include "Includes/sidebar.php"; ?>
    <div class="main--content">
        <div id="overlay"></div>

        <div class="table-container">
            <div class="title" id="showButton" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="section--title">Attendance</h2>
                <button class="add"><i class="ri-add-line"></i></button>
            </div>
            <!-- Subject Filter Dropdown -->
            <div class="filter-container">
                <form method="GET" action="">
                    <select name="subject" onchange="this.form.submit()">
                        <option value="">-- Filter by Subject --</option>
                        <?php
                        $subQuery = $conn->query("SELECT DISTINCT name FROM tblcourse");
                        while ($sub = $subQuery->fetch_assoc()) {
                            $selected = (isset($_GET['subject']) && $_GET['subject'] == $sub['name']) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($sub['name']) . "' $selected>" . htmlspecialchars($sub['name']) . "</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <!-- Attendance Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Registration Number</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Course Code</th>
                        <th>Subject Name</th>
                        
                    </tr>
                </thead>
                <tbody>
                <?php
$whereClauses = [];

if (!empty($_GET['subject'])) {
    $subject = $conn->real_escape_string($_GET['subject']);
    $whereClauses[] = "subjectname = '$subject'";
}


$whereClauses[] = "registrationNumber = '$registration'"; // Always filter by registration

$where = "WHERE " . implode(" AND ", $whereClauses);

$sql = "SELECT * FROM attendance $where ORDER BY date DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr id='rowlecture{$row["id"]}'>";
        echo "<td>{$row["registrationNumber"]}</td>";
        echo "<td>{$row["date"]}</td>";
        echo "<td>{$row["time"]}</td>";
        echo "<td>{$row["status"]}</td>";
        echo "<td>{$row["courseCode"]}</td>";
        echo "<td>{$row["subjectname"]}</td>";
        
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No records found</td></tr>";
}
?>
           </table>
            <br><br>
            <br><br>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">📊 Attendance Analytics</h3>  
            <br>
            

<?php

$whereClauses[] = "registrationNumber = '$registration'";

// Overall Attendance % for this student
$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM attendance WHERE registrationNumber = '$registration'");
$presentQuery = $conn->query("SELECT COUNT(*) AS present FROM attendance WHERE registrationNumber = '$registration' AND status = 'Present'");

$totalRows = $totalQuery->fetch_assoc()['total'];
$presentRows = $presentQuery->fetch_assoc()['present'];

$overallPercentage = ($totalRows > 0) ? round(($presentRows / $totalRows) * 100, 2) : 0;
?>

<table class="w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg overflow-hidden mb-6">
    <thead class="text-xs text-gray-100 uppercase bg-indigo-600">
        <tr>
            <th scope="col" class="px-6 py-3">Type</th>
            <th scope="col" class="px-6 py-3">Total Lectures</th>
            <th scope="col" class="px-6 py-3">Present</th>
            <th scope="col" class="px-6 py-3">Attendance %</th>
        </tr>
    </thead>
    <tbody>
        <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-medium">Overall</td>
            <td class="px-6 py-4"><?= $totalRows ?></td>
            <td class="px-6 py-4"><?= $presentRows ?></td>
            <td class="px-6 py-4"><?= $overallPercentage ?>%</td>
        </tr>
    </tbody>
</table>

    <br><br> 
    <br><br> 

    <!-- Subject Dropdown -->
    <form method="GET" action="" class="mb-6">
        
      <b>  <label for="subject_select" class="block mb-2 text-sm font-medium text-gray-700">🎯 Select Subject for Analytics:</label></b>
        <select name="subject_analytics" id="subject_select"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-gray-800 px-3 py-2"
            onchange="this.form.submit()">
            <option value="">-- Select Subject --</option>
            <?php
            $subQuery = $conn->query("SELECT DISTINCT subjectname FROM attendance");
            while ($row = $subQuery->fetch_assoc()) {
                $subject = $row['subjectname'];
                $selected = (isset($_GET['subject_analytics']) && $_GET['subject_analytics'] == $subject) ? "selected" : "";
                echo "<option value='" . htmlspecialchars($subject) . "' $selected>" . htmlspecialchars($subject) . "</option>";
                echo "Selected subject: $subject <br>";
echo "Registration: " . ($_GET['registration'] ?? 'Not set') . "<br>";

            }
            
            ?> <input type="hidden" name="registration" value="<?= htmlspecialchars($registration) ?>">
        </select>
    </form>

    <?php  
if (isset($_GET['subject_analytics']) && $_GET['subject_analytics'] !== "" && isset($_GET['registration'])) {
    $subject = $conn->real_escape_string($_GET['subject_analytics']);
   
$whereClauses[] = "registrationNumber = '$registration'";

    $subjectTotal = $conn->query("
        SELECT COUNT(*) AS total 
        FROM attendance 
        WHERE subjectname = '$subject' AND registrationNumber = '$registration'
    ")->fetch_assoc()['total'];

    $subjectPresent = $conn->query("
        SELECT COUNT(*) AS present 
        FROM attendance 
        WHERE subjectname = '$subject' AND registrationNumber = '$registration' AND status = 'Present'
    ")->fetch_assoc()['present'];

    $subjectPercent = ($subjectTotal > 0) ? round(($subjectPresent / $subjectTotal) * 100, 2) : 0;
?>
    <table class="w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg overflow-hidden">
        <thead class="text-xs text-white uppercase bg-indigo-500">
            <tr>
                <th class="px-6 py-3">Subject</th>
                <th class="px-6 py-3">Total Lectures</th>
                <th class="px-6 py-3">Present</th>
                <th class="px-6 py-3">Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white border-b hover:bg-gray-50">
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($subject) ?></td>
                <td class="px-6 py-4"><?= $subjectTotal ?></td>
                <td class="px-6 py-4"><?= $subjectPresent ?></td>
                <td class="px-6 py-4"><?= $subjectPercent ?>%</td>
            </tr>
        </tbody>
    </table>
<?php } ?>

</div>

        

</section>

<script>
    // Open Edit Modal
  

    // Close Modal
    document.querySelector('.close-edit').addEventListener('click', function () {
        document.getElementById('editStatusModal').style.display = 'none';
        document.getElementById('overlayEdit').style.display = 'none';
    });


    // Handle delete click

    
</script>

</body>
</html>
