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

$users = [];
$sql = "SELECT * FROM users";
$result = mysqli_query($dbhandle, $sql);
if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC); 
    mysqli_free_result($result); 
} else {
    error_log("Query failed for fetching all users: (" . mysqli_errno($dbhandle) . ") " . mysqli_error($dbhandle));
}

$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['error_message'], $_SESSION['success_message']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StresSense: Admin Dashboard - AIDS Prediction</title>
    <link rel="stylesheet" href="CSS/admindashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="IMAGE/aidshiv.png">
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="AdminDashboard.php" class="active">Home</a></li>
            <li><a href="insights.php">Insights</a></li>
            <li><a href="accounts.php">Users</a></li>
            <li><a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <div class="main-content">
        <div class="container">
            <h2>Admin Dashboard</h2>
            <p>Welcome to the Admin Dashboard for AIDS Prediction.</p>
            <h3>Admin Functions</h3>
            <ul>
                <li>Manage user accounts, including creating, editing, and deleting users.</li>
                <li>View insights through visual data representations like pie charts.</li>
                <li>Ensure the security and integrity of user data.</li>
            </ul>
            <p>Use the sidebar to navigate between managing users and viewing insights.</p>
        </div>
    </div>
</body>
</html>
<?php
if ($dbhandle) {
    mysqli_close($dbhandle);
}
?>