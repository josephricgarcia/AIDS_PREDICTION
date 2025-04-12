<?php
include 'session.php';
include 'connection.php';

$userId = $_SESSION['user_id'];
$assessments = [];
$error = '';

// Fetch assessments using userId
$query = "SELECT * FROM assessment WHERE userId = ?";
$stmt = mysqli_prepare($dbhandle, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $assessments[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
} else {
    $error = 'Error fetching data. Please try again later.';
}
mysqli_close($dbhandle);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HIV/AIDS Treatment - Assessment History</title>
    <link rel="stylesheet" href="CSS/history.css">
    <link rel="shortcut icon" href="IMAGE/blood.png">
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="Assess.php">Assessment</a></li>
            <li><a href="History.php" class="active">History</a></li>
            <li><a href="Aboutus.php">About</a></li>
            <li><a href="Settings.php">Settings</a></li>
        </ul>
    </aside>

    <div class="history-content">
        <h2>Assessment History</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif (!empty($assessments)): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Homosexual Activity</th>
                        <th>Drugs History</th>
                        <th>Race</th>
                        <th>Weight (kg)</th>
                        <th>Predictions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assessments as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['homosexual_activity']); ?></td>
                        <td><?php echo htmlspecialchars($row['drugs_history']); ?></td>
                        <td><?php echo htmlspecialchars($row['race']); ?></td>
                        <td><?php echo htmlspecialchars($row['weight']); ?></td>
                        <td><?php echo htmlspecialchars($row['prediction'] ?? 'N/A'); ?></td>
                        <td class="actions">
                            <a href="EditAssessment.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                            <form method="POST" action="DeleteAssessment.php" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="assessment_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No assessments found. <a href="Assess.php">Create one now</a></p>
        <?php endif; ?>
    </div>
</body>
</html>