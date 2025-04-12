<?php
session_start();
include 'connection.php';

if (!$dbhandle) {
    die("<script>alert('Database connection failed: " . mysqli_connect_error() . "');</script>");
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

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
                        $_SESSION['role'] = $row['role'];
                        $_SESSION['logged_in'] = true;

                        if ($row['role'] === 'user') {
                            header("Location: Home.php");
                        } elseif ($row['role'] === 'admin') {
                            header("Location: AdminDashboard.php");
                        }
                        exit();
                    } else {
                        echo "<script>alert('Invalid password');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid username');</script>";
                }
            } else {
                echo "<script>alert('Data retrieval failed: " . mysqli_error($dbhandle) . "');</script>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<script>alert('SQL statement preparation failed: " . mysqli_error($dbhandle) . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid database connection object.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StressSense: Log In</title>
    <link rel="stylesheet" href="CSS/login.css">
    <link rel="shortcut icon" href="images/stresssense_logo.png">
</head>

<body>
    <div class="login-form">
        <h1>Sign In Your Account</h1>
        <form action="Login.php" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit" name="submit">Log In</button>
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </form>
    </div>
</body>
</html>