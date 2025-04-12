<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: LogIn.php");
    exit();
}

// Include the centralized database connection
require_once 'connection.php';

if (isset($_POST['submit'])) {
    // Input validation
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $homosexual_activity = filter_input(INPUT_POST, 'homosexual_activity', FILTER_SANITIZE_STRING);
    $drugs_history = filter_input(INPUT_POST, 'drugs_history', FILTER_SANITIZE_STRING);
    $race = filter_input(INPUT_POST, 'race', FILTER_SANITIZE_STRING);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);

    if ($age !== false && $gender && $homosexual_activity && $drugs_history && $race && $weight !== false) {
        // Use the connection from connection.php
        $userId = $_SESSION['user_id'];
        $stmt = mysqli_prepare($dbhandle, 
            "INSERT INTO assessment (age, gender, homosexual_activity, drugs_history, race, weight, id) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "issssdi", $age, $gender, $homosexual_activity, $drugs_history, $race, $weight, $userId);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Assessment submitted successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($dbhandle) . "');</script>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Invalid input. Please check your entries.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aids  - HIV/AIDS Treatment Data</title>
    <link rel="stylesheet" href="CSS/assessment.css">
</head>
<body>

        <aside class="sidebar">
        <img src="IMAGE/aidshiv.png" alt="Logo" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="Assess.php">Assessment</a></li>
            <li><a href="Aboutus.php">About</a></li>
            <li><a href="Settings.php"> Settings </a></li>
        </ul>
    </aside>
        
        </div>
        <div class="main-content">
        <div class="container">
        <div class="assessment-form">
        <form action="Assessment.php" method="post">
            <h2>Assessment Form</h2>
            

            <div class="assessment">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="0" required>
            </div>

            <div class="assessment">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="assessment">
                <label for="homosexual_activity">Homosexual Activity</label>
                <select id="homosexual_activity" name="homosexual_activity" required>
                    <option value="">Select</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>

            <div class="assessment">
                <label for="drugs_history">Drugs History</label>
                <select id="drugs_history" name="drugs_history" required>
                    <option value="">Select</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>

            <div class="assessment">
                <label for="race">Race</label>
                <input type="text" id="race" name="race" required>
            </div>

            <div class="assessment">
                <label for="weight">Weight (kg)</label>
                <input type="number" id="weight" name="weight" step="0.1" min="0" required>
            </div>

            <button type="submit" name="submit" id="submit-assessment">SUBMIT</button>
        </form>
    </div>
        </div>
    
</body>
</html>
