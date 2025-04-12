<?php
include 'connection.php';

if (isset($_POST['submit'])) {
    // Retrieve form data
    $lname = $_POST['lname'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact_no = $_POST['contact_number'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("<script>alert('Passwords do not match.'); window.history.back();</script>");
    }

    // Check if username already exists
    $check_sql = "SELECT username FROM users WHERE username = ?";
    $stmt_check = mysqli_prepare($dbhandle, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "s", $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        die("<script>alert('Username already taken.'); window.history.back();</script>");
    }
    mysqli_stmt_close($stmt_check);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user with role set to 'user'
    $sql = "INSERT INTO users (lname, fname, mname, gender, birthday, contact_number, username, password, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($dbhandle instanceof mysqli) {
        $stmt = mysqli_prepare($dbhandle, $sql);
        if ($stmt) {
            $role = 'user'; // Set role to 'user'
            mysqli_stmt_bind_param($stmt, "sssssssss", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt)) {
                echo "<script>
                    alert('Registration successful! Redirecting to login page...');
                    window.location.href = 'login.php';
                </script>";
            } else {
                die("<script>alert('Data insertion failed: " . mysqli_error($dbhandle) . "'); window.history.back();</script>");
            }
            mysqli_stmt_close($stmt);
        } else {
            die("<script>alert('SQL statement preparation failed: " . mysqli_error($dbhandle) . "'); window.history.back();</script>");
        }
    } else {
        die("<script>alert('Invalid database connection.'); window.history.back();</script>");
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="CSS/register.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>Create Account</h1>
                <p>Join our community today</p>
            </div>
            
            <form action="register.php" method="post" class="registration-form">
                <div class="form-group">
                    <div class="name-fields">
                        <div class="input-field">
                            <input type="text" id="fname" name="fname" required>
                            <label for="fname">First Name</label>
                        </div>
                        <div class="input-field">
                            <input type="text" id="lname" name="lname" required>
                            <label for="lname">Last Name</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field">
                        <input type="text" id="mname" name="mname" required>
                        <label for="mname">Middle Name</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field select-field">
                        <select id="gender" name="gender" required>
                            <option value=""></option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <label for="gender">Gender</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field">
                        <input type="date" id="birthday" name="birthday" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field">
                        <input type="text" id="contact_no" name="contact_number" required>
                        <label for="contact_no">Contact Number</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field">
                        <input type="text" id="username" name="username" required>
                        <label for="username">Username</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field">
                        <input type="password" id="password" name="password" required>
                        <label for="password">Password</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-field">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>
                </div>

                <button type="submit" name="submit" class="submit-btn">Create Account</button>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>