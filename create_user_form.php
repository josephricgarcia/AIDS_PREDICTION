<?php
include 'connection.php';
session_start();

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$confirm_password = "";
$lname = $fname = $mname = $gender = $birthday = $contact_no = $username = $role = "";

// Check database connection
if (!$dbhandle) {
    echo "<script>alert('Database connection failed: " . mysqli_connect_error() . "');</script>";
    exit();
}

if (isset($_POST['submit'])) {
    // Use null coalescing to prevent undefined index warnings
    $lname = $_POST['lname'] ?? '';
    $fname = $_POST['fname'] ?? '';
    $mname = $_POST['mname'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $contact_no = $_POST['contact_number'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validate role
    if (!in_array($role, ['user', 'admin'])) {
        echo "<script>alert('Invalid role selected!');</script>";
        $role = 'user'; // Default to 'user'
    }

    // Server-side validation for required fields
    if (empty($lname) || empty($fname) || empty($mname) || empty($gender) || empty($birthday) || empty($contact_no) || empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
        echo "<script>alert('All fields are required!');</script>";
    } else if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if username already exists
        $check_sql = "SELECT username FROM users WHERE username = ?";
        if ($stmt_check = mysqli_prepare($dbhandle, $check_sql)) {
            mysqli_stmt_bind_param($stmt_check, "s", $username);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                echo "<script>alert('Username already exists! Please choose a different username.');</script>";
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (lname, fname, mname, gender, birthday, contact_number, username, password, role) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($dbhandle, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sssssssss", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $hashed_password, $role);

                    if (mysqli_stmt_execute($stmt)) {
                        echo "<script>alert('Account created successfully!'); window.location.href='AdminDashboard.php';</script>";
                        exit();
                    } else {
                        echo "<script>alert('Account registration failed: " . mysqli_error($dbhandle) . "');</script>";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo "<script>alert('SQL statement preparation failed: " . mysqli_error($dbhandle) . "');</script>";
                }
            }
            mysqli_stmt_close($stmt_check);
        } else {
            echo "<script>alert('SQL statement preparation failed: " . mysqli_error($dbhandle) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - StresSense Admin</title>
    <link rel="stylesheet" href="CSS/create_user_form.css">
    <link rel="shortcut icon" href="images/stresssense_logo.png">
</head>
<body>
    <div class="user-form">
        <h1>Create User Account Here</h1>
        <form action="create_user_form.php" method="post">
            <input type="text" id="lname" name="lname" placeholder="Last Name" value="<?php echo htmlspecialchars($lname); ?>" required> 
            <input type="text" id="fname" name="fname" placeholder="First Name" value="<?php echo htmlspecialchars($fname); ?>" required>
            <input type="text" id="mname" name="mname" placeholder="Middle Name" value="<?php echo htmlspecialchars($mname); ?>" required>
            <div class="form-row">
                <select id="gender" name="gender" required>
                    <option value="" disabled <?php echo empty($gender) ? 'selected' : ''; ?>>Gender</option>
                    <option value="m" <?php echo $gender === 'm' ? 'selected' : ''; ?>>Male</option>
                    <option value="f" <?php echo $gender === 'f' ? 'selected' : ''; ?>>Female</option>
                    <option value="x" <?php echo $gender === 'x' ? 'selected' : ''; ?>>Prefer not to say</option>
                </select>
                <select id="role" name="role" required>
                    <option value="" disabled <?php echo empty($role) ? 'selected' : ''; ?>>Role</option>
                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" required>
            </div>
            <input type="text" id="contact_no" name="contact_number" placeholder="Contact Number" value="<?php echo htmlspecialchars($contact_no); ?>" required>
            <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="submit">Create Account</button>
            <a href="AdminDashboard.php" style="color: red; text-decoration: none; margin-left: 10px;">Cancel</a>
        </form>
    </div>
</body>
</html>