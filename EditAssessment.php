<?php
include 'session.php';
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: LogIn.php");
    exit();
}

$userId = $_SESSION['user_id'];
$existingData = [];
$assessmentId = 0;

// Determine assessment ID based on request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessmentId = isset($_POST['assessment_id']) ? (int)$_POST['assessment_id'] : 0;
} else {
    $assessmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

// Validate assessment ID
if ($assessmentId <= 0) {
    echo "<script>alert('Invalid assessment ID.'); window.location.href='History.php';</script>";
    exit();
}

// Fetch the assessment for the user with the provided ID
$stmt = mysqli_prepare($dbhandle, "SELECT * FROM assessment WHERE userId = ? AND id = ?");
mysqli_stmt_bind_param($stmt, "ii", $userId, $assessmentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existingData = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$existingData) {
    echo "<script>alert('Assessment not found or you do not have permission to edit it.'); window.location.href='History.php';</script>";
    exit();
}

if (isset($_POST['submit'])) {
    // Validate inputs
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $gender = $_POST['gender'] ?? '';
    $homosexual_activity = $_POST['homosexual_activity'] ?? '';
    $drugs_history = $_POST['drugs_history'] ?? '';
    $race = trim($_POST['race'] ?? '');
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);

    $error = '';
    
    // Validation checks
    if ($age === false) $error .= 'Invalid age. ';
    if (!in_array($gender, ['male', 'female', 'other'])) $error .= 'Invalid gender. ';
    if (!in_array($homosexual_activity, ['yes', 'no'])) $error .= 'Invalid homosexual activity selection. ';
    if (!in_array($drugs_history, ['yes', 'no'])) $error .= 'Invalid drugs history selection. ';
    if (empty($race)) $error .= 'Race cannot be empty. ';
    if ($weight === false) $error .= 'Invalid weight. ';

    if (empty($error)) {
        // Use the validated assessment ID
        $stmt = mysqli_prepare($dbhandle, 
            "UPDATE assessment 
             SET age = ?, gender = ?, homosexual_activity = ?, drugs_history = ?, race = ?, weight = ?
             WHERE id = ? AND userId = ?"
        );
        mysqli_stmt_bind_param($stmt, "isssssii", $age, $gender, $homosexual_activity, $drugs_history, $race, $weight, $assessmentId, $userId);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Assessment updated successfully!'); window.location.href='History.php';</script>";
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
    <title>Edit Assessment - HIV/AIDS Treatment Data</title>
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
        <form action="EditAssessment.php" method="post">
            <h2>Edit Assessment</h2>
            <input type="hidden" name="assessment_id" value="<?php echo $assessmentId; ?>">

            <div class="assessment">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="0" required value="<?php echo htmlspecialchars($existingData['age']); ?>">
            </div>

            <div class="assessment">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="male" <?php echo ($existingData['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo ($existingData['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?php echo ($existingData['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="assessment">
                <label for="homosexual_activity">Homosexual Activity</label>
                <select id="homosexual_activity" name="homosexual_activity" required>
                    <option value="yes" <?php echo ($existingData['homosexual_activity'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?php echo ($existingData['homosexual_activity'] == 'no') ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="assessment">
                <label for="drugs_history">Drugs History</label>
                <select id="drugs_history" name="drugs_history" required>
                    <option value="yes" <?php echo ($existingData['drugs_history'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?php echo ($existingData['drugs_history'] == 'no') ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="assessment">
                <label for="race">Race</label>
                <input type="text" id="race" name="race" required value="<?php echo htmlspecialchars($existingData['race']); ?>">
            </div>

            <div class="assessment">
                <label for="weight">Weight (kg)</label>
                <input type="number" id="weight" name="weight" step="0.1" min="0" required value="<?php echo htmlspecialchars($existingData['weight']); ?>">
            </div>

            <button type="submit" name="submit" id="submit-assessment">UPDATE</button>
        </form>
    </div>
</body>
</html>