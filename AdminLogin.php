<?php
session_start();
include 'connection.php';

// Redirect to dashboard if admin is already logged in
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: AdminDashboard.php");
    exit();
}

if (!$dbhandle) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error = "";
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username !== 'admin') {
        $error = "You are not authorized to log in as an admin.";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        if ($dbhandle instanceof mysqli) {
            $stmt = mysqli_prepare($dbhandle, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);

                    if ($row) {
                        if (password_verify($password, $row['password'])) {
                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['is_admin'] = true;
                            
                            header("Location: AdminDashboard.php");
                            exit();
                        } else {
                            $error = "Invalid password";
                        }
                    } else {
                        $error = "Admin account not found in the database.";
                    }
                } else {
                    $error = "Data retrieval failed: " . mysqli_error($dbhandle);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "SQL statement preparation failed: " . mysqli_error($dbhandle);
            }
        } else {
            $error = "Invalid database connection object.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StressSense: Admin Log In</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="shortcut icon" href="images/stresssense_logo.png">
</head>

<body>


    <div class="login-form">
        <h1>Admin Sign In</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="AdminLogin.php" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit" name="submit">Log In</button>

            <p>Not an admin? <a href="LogIn.php" class="user-login-link">Login as User</a></p>
        </form>
    </div>

</body>
</html>