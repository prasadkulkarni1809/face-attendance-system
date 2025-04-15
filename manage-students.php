<?php
if (isset($_POST['addStudent'])) {

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $registrationNumber = $_POST['registrationNumber'];
    
    $dateRegistered = date("Y-m-d");

    $imageFileNames = []; // Array to hold image file paths

    // Process and save images
    $folderPath = "resources/labels/{$registrationNumber}/";
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    for ($i = 1; $i <= 5; $i++) {
        if (isset($_POST["capturedImage$i"])) {
            $base64Data = explode(',', $_POST["capturedImage$i"])[1];
            $imageData = base64_decode($base64Data);
            $labelName = "{$i}.png";
            $filePath = "{$folderPath}{$labelName}";
            file_put_contents($filePath, $imageData);
            
            // Store the relative path to the image
            $imageFileNames[] = $filePath; 
        }
    }

    // Convert image file paths to JSON
    $imagesJson = json_encode($imageFileNames);

    // Check for duplicate registration number
    $checkQuery = $pdo->prepare("SELECT COUNT(*) FROM tblstudents WHERE registrationNumber = :registrationNumber");
    $checkQuery->execute([':registrationNumber' => $registrationNumber]);
    $count = $checkQuery->fetchColumn();

    if ($count > 0) {
        $_SESSION['message'] = "Student with the given Registration No: $registrationNumber already exists!";
    } else {
        // Insert new student with images stored as JSON
        $insertQuery = $pdo->prepare("
            INSERT INTO tblstudents 
            (firstName, lastName, email, registrationNumber, studentImage, dateRegistered) 
            VALUES 
            (:firstName, :lastName, :email, :registrationNumber, :studentImage, :dateRegistered)
        ");

        $insertQuery->execute([
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':registrationNumber' => $registrationNumber,
            ':studentImage' => $imagesJson, // Store JSON array of image file paths
            ':dateRegistered' => $dateRegistered
        ]);

        $_SESSION['message'] = "Student: $registrationNumber added successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>AMS - Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php'; ?>

    <section class=main>
        <?php include "Includes/sidebar.php"; ?>

        <div class="main--content">
            <div id="overlay"></div>
            <?php showMessage(); ?>
            <div class="table-container">
                <div class="title" id="showButton">
                    <h2 class="section--title">Students</h2>
                    <button class="add"><i class="ri-add-line"></i>Add Student</button>
                </div>

                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Images</th>
                                <th>Settings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM tblstudents";
                            $result = fetch($sql);
                            if ($result) {
                                foreach ($result as $row) {
                                    $images = json_decode($row['studentImage'], true);
                                    echo "<tr id='rowstudents{$row["Id"]}'>";
                                    echo "<td>" . $row["registrationNumber"] . "</td>";
                                    echo "<td>" . $row["firstName"] . " " . $row["lastName"] . "</td>";
                                    echo "<td>" . $row["email"] . "</td>";
                                    echo "<td>";

                                    if ($images) {
                                        foreach ($images as $imagePath) {
                                          //  echo "<img src='$imagePath' alt='Student Image' style='width:50px; height:50px; margin-right:5px;'>";
                                        }
                                    }

                                    echo "</td>";
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
            </div>
            <div class="formDiv--" id="form" style="display:none;">
                <form method="post">
                    <div style="display:flex; justify-content:space-around;">
                        <div class="form-title">
                            <p>Add Student</p>
                        </div>
                        <div>
                            <span class="close">&times;</span>
                        </div>
                    </div>
                    <div>
                        <div>
                            <input type="text" name="firstName" placeholder="First Name">
                            <input type="text" name="lastName" placeholder="Last Name">
                            <input type="email" name="email" placeholder="Email Address">
                            <input type="text" required id="registrationNumber" name="registrationNumber" placeholder="Registration Number"> <br>
                            <p id="error" style="color: red; display: none;">Invalid characters in registration number.</p>
                        </div>
                        <div>
                            <div class="form-title-image">
                                <p>Take Student Pictures</p>
                            </div>
                            <div id="open_camera" class="image-box" onclick="takeMultipleImages()">
                                <img src="resources/images/default.png" alt="Default Image">
                            </div>
                            <div id="multiple-images"></div>
                        </div>
                    </div>

                    <input type="submit" class="btn-submit" value="Save Student" name="addStudent" />
                </form>
            </div>
    </section>

    <?php js_asset(["admin_functions", "delete_request", "script", "active_link"]) ?>

    <script>
        const registrationNumberInput = document.getElementById('registrationNumber');
        const errorMessage = document.getElementById('error');
        const invalidCharacters = /[\\/:*?"<>|]/g;

        registrationNumberInput.addEventListener('input', () => {
            const originalValue = registrationNumberInput.value;
            const sanitizedValue = originalValue.replace(invalidCharacters, '');

            if (originalValue !== sanitizedValue) {
                errorMessage.style.display = 'inline';
                errorMessage.textContent = 'Invalid characters removed.';
            } else {
                errorMessage.style.display = 'none';
            }

            registrationNumberInput.value = sanitizedValue; 
        });
    </script>
</body>

</html>
