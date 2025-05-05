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
            <div class="table-container"> <!-- Scrollable table wrapper -->
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Age</th>
                            <th>Weight</th>
                            <th>Gender</th>
                            <th>Homosexual Activity</th>
                            <th>Drug Use</th>
                            <th>Prior Infection</th>
                            <th>AZT</th>
                            <th>Antiretroviral</th>
                            <th>Symptomatic</th>
                            <th>Treatment</th>
                            <th>Off-Treatment</th>
                            <th>Infected</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assessments as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['age']); ?></td>
                            <td><?php echo htmlspecialchars($row['weight']); ?></td>
                            <td><?php echo ($row['gender'] == 1) ? 'Male' : 'Female'; ?></td>
                            <td><?php echo ($row['homo'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['drugs'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['oprior'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['z30'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['str2'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['symptom'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['treat'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['offtrt'] == 1) ? 'Yes' : 'No'; ?></td>
                            <td><?php echo ($row['infected'] == 1) ? 'Yes' : 'No'; ?></td>
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
            </div>
        <?php else: ?>
            <p class="no-assessments">No assessments found. <a href="Assess.php">Create one now</a></p>
        <?php endif; ?>
    </div>
</body>
</html>