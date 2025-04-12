<?php
include 'session.php';
include 'connection.php';

if (isset($_POST['submit'])) {
    // Validate inputs
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $gender = $_POST['gender'] ?? '';
    $homosexual_activity = $_POST['homosexual_activity'] ?? '';
    $drugs_history = $_POST['drugs_history'] ?? '';
    $race = trim($_POST['race'] ?? '');
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);

    $error = '';
    if ($age === false || $age === null) {
        $error = "Invalid age.";
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $error = "Invalid gender selection.";
    } elseif (!in_array($homosexual_activity, ['yes', 'no'])) {
        $error = "Invalid homosexual activity selection.";
    } elseif (!in_array($drugs_history, ['yes', 'no'])) {
        $error = "Invalid drugs history selection.";
    } elseif (empty($race)) {
        $error = "Race cannot be empty.";
    } elseif ($weight === false || $weight === null) {
        $error = "Invalid weight.";
    }

    if (empty($error)) {
        $userId = $_SESSION['user_id'];
        // Ensure the 'id' column is auto-increment in the database
        $stmt = mysqli_prepare($dbhandle, 
            "INSERT INTO assessment (userId, age, gender, homosexual_activity, drugs_history, race, weight) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "isssssd", $userId, $age, $gender, $homosexual_activity, $drugs_history, $race, $weight);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Assessment submitted successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($dbhandle) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('$error');</script>";
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aids - HIV/AIDS Treatment Data</title>
    <link rel="stylesheet" href="CSS/assessment.css">
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="Assess.php">Assessment</a></li>
            <li><a href="History.php">History</a></li>
            <li><a href="Aboutus.php">About</a></li>
            <li><a href="Settings.php">Settings</a></li>
        </ul>
    </aside>

    <div class="assessment-form">
        <form action="Assess.php" method="post">
            <h2>Assessment Form</h2>

            <div class="assessment">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="0" required>
            </div>

            <div class="assessment">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
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
</body>
</html>