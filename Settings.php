<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT lname, fname, mname, gender, birthday, contact_number, username FROM users WHERE id = ?";
$stmt = mysqli_prepare($dbhandle, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Error retrieving user data");
}

$full_name = trim($user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aids Prediction : Settings</title>
    <link rel="stylesheet" href="CSS/settings.css">
    <link rel="shortcut icon" href="IMAGE/aidshiv.png">
    <script>
        function confirmLogout(event) {
            if (!confirm("Are you sure you want to logout?")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="Assess.php">Assessment</a></li>
            <li><a href="History.php">History</a></li>
            <li><a href="Aboutus.php">About</a></li>
            <li><a href="Settings.php"> Settings </a></li>
        </ul>
    </aside>

    <div class="settings-content">
        <form action="update_form.php" method="POST">
            <div class="user-details">
                <img src="IMAGE/person.png" alt="User Profile">
                <div class="user-info">
                    <div class="user-row">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                    <div class="user-row">
                        <label>Gender:</label>
                        <span><?php echo htmlspecialchars($user['gender']); ?></span>
                    </div>
                    <div class="user-row">
                        <label>Birthday:</label>
                        <span><?php echo htmlspecialchars($user['birthday']); ?></span>
                    </div>
                    <div class="user-row">
                        <label>Contact:</label>
                        <span><?php echo htmlspecialchars($user['contact_number']); ?></span>
                    </div>
                    <div class="user-row">
                        <label>Username:</label>
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                </div>
             </div>
             <div class="operation">
                <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <button type="submit" class="edit-btn">Edit Account</button>
                <a href="logout.php" class="cancel-btn" onclick="confirmLogout(event)">Logout</a>
            </div>
        </form>
    </div>
</body>
</html>