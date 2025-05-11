<?php
include 'session.php';
include 'connection.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!$dbhandle) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch gender distribution
$gender_data = ['Male' => 0, 'Female' => 0, 'Prefer not to say' => 0];
$sql_gender = "SELECT gender, COUNT(*) as count FROM users GROUP BY gender";
$result_gender = mysqli_query($dbhandle, $sql_gender);
if ($result_gender) {
    while ($row = mysqli_fetch_assoc($result_gender)) {
        switch ($row['gender']) {
            case 'm':
                $gender_data['Male'] = $row['count'];
                break;
            case 'f':
                $gender_data['Female'] = $row['count'];
                break;
            case 'x':
                $gender_data['Prefer not to say'] = $row['count'];
                break;
        }
    }
    mysqli_free_result($result_gender);
} else {
    error_log("Query failed for gender distribution: (" . mysqli_errno($dbhandle) . ") " . mysqli_error($dbhandle));
}

// Fetch AIDS prediction distribution
$prediction_data = ['Infected' => 0, 'Not Infected' => 0];
$sql_prediction = "SELECT infected, COUNT(*) as count FROM assessment GROUP BY infected";
$result_prediction = mysqli_query($dbhandle, $sql_prediction);
if ($result_prediction) {
    while ($row = mysqli_fetch_assoc($result_prediction)) {
        if ($row['infected'] == 1) {
            $prediction_data['Infected'] = $row['count'];
        } else {
            $prediction_data['Not Infected'] = $row['count'];
        }
    }
    mysqli_free_result($result_prediction);
} else {
    error_log("Query failed for prediction distribution: (" . mysqli_errno($dbhandle) . ") " . mysqli_error($dbhandle));
}

mysqli_close($dbhandle);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StresSense: Insights - AIDS Prediction</title>
    <link rel="stylesheet" href="CSS/insights.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="IMAGE/aidshiv.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="AdminDashboard.php">Home</a></li>
            <li><a href="insights.php" class="active">Insights</a></li>
            <li><a href="accounts.php">Users</a></li>
             <li><a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <div class="main-content">
        <div class="container">
            <h2>Insights</h2>
            <p>Explore data visualizations for user demographics and AIDS predictions.</p>
            <div class="chart-container">
                <div class="chart-wrapper">
                    <h3>Gender Distribution</h3>
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="chart-wrapper">
                    <h3>AIDS Prediction Distribution</h3>
                    <canvas id="predictionChart"></canvas>
                </div>
            </div>
            </div>
    </div>
    <script>
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female', 'Prefer not to say'],
                datasets: [{
                    data: [<?php echo $gender_data['Male']; ?>, <?php echo $gender_data['Female']; ?>, <?php echo $gender_data['Prefer not to say']; ?>],
                    backgroundColor: ['#4a90e2', '#ff6b6b', '#feca57'],
                    borderColor: ['#fff'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Prediction Chart
        const predictionCtx = document.getElementById('predictionChart').getContext('2d');
        new Chart(predictionCtx, {
            type: 'pie',
            data: {
                labels: ['Infected', 'Not Infected'],
                datasets: [{
                    data: [<?php echo $prediction_data['Infected']; ?>, <?php echo $prediction_data['Not Infected']; ?>],
                    backgroundColor: ['#e53e3e', '#2f855a'],
                    borderColor: ['#fff'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>