<?php
    include 'connection.php';
    include 'session.php';

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    $id = isset($_POST['id']) ? trim($_POST['id']) : null;
    $lname = isset($_POST['lname']) ? trim($_POST['lname']) : null;
    $fname = isset($_POST['fname']) ? trim($_POST['fname']) : null;
    $mname = isset($_POST['mname']) ? trim($_POST['mname']) : null;
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : null;
    $birthday = isset($_POST['birthday']) ? trim($_POST['birthday']) : null;
    $cno = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : null;
    $username = isset($_POST['username']) ? trim($_POST['username']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $role = isset($_POST['role']) ? trim($_POST['role']) : null; // Added role

    if (!$dbhandle) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Update user data
    $sql = "UPDATE users SET lname = ?, fname = ?, mname = ?, gender = ?, birthday = ?, contact_number = ?, username = ?, role = ?" . // Added role
           (!empty($password) ? ", password = ?" : "") . " WHERE id = ?";
    $stmt = mysqli_prepare($dbhandle, $sql);

    if ($stmt) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sssssssssi", $lname, $fname, $mname, $gender, $birthday, $cno, $username, $role, $hashed_password, $id); // Added $role
        } else {
            mysqli_stmt_bind_param($stmt, "ssssssssi", $lname, $fname, $mname, $gender, $birthday, $cno, $username, $role, $id); // Added $role
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "User information updated successfully.";
            header("Location: AdminDashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to update user information.";
            // Re-fetch user data
            $sql_select = "SELECT * FROM users WHERE id = ?";
            $stmt_select = mysqli_prepare($dbhandle, $sql_select);
            mysqli_stmt_bind_param($stmt_select, "i", $id);
            mysqli_stmt_execute($stmt_select);
            $result = mysqli_stmt_get_result($stmt_select);
            $edit_user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt_select);
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare failed updating user: " . mysqli_error($dbhandle));
        $_SESSION['error_message'] = "Database error updating user data.";
    }

    mysqli_close($dbhandle);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    include 'connection.php';
    $id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($dbhandle, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $edit_user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    } else {
        die("Error fetching user data: " . mysqli_error($dbhandle));
    }
    mysqli_close($dbhandle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Information</title>
    <link rel="shortcut icon" href="images/stresssense_logo.png">
    <link rel="stylesheet" href="CSS/edit_user_form.css"> 
</head>
<body>
    <div class="user-form"> 
        <h1>Edit User Information</h1> 
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>
        <form method="POST" action="edit_user_form.php">
            <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
            
            <input type="text" name="lname" placeholder="Last Name" value="<?php echo htmlspecialchars($edit_user['lname']); ?>" required>
            <input type="text" name="fname" placeholder="First Name" value="<?php echo htmlspecialchars($edit_user['fname']); ?>" required>
            <input type="text" name="mname" placeholder="Middle Name" value="<?php echo htmlspecialchars($edit_user['mname']); ?>">

            <div class="form-row">
                <select name="gender" required>
                    <option value="m" <?= ($edit_user['gender'] === 'm') ? 'selected' : '' ?>>Male</option>
                    <option value="f" <?= ($edit_user['gender'] === 'f') ? 'selected' : '' ?>>Female</option>
                    <option value="x" <?= ($edit_user['gender'] === 'x') ? 'selected' : '' ?>>Prefer not to say</option>
                </select>
                <input type="date" name="birthday" value="<?php echo htmlspecialchars($edit_user['birthday']); ?>" required>
            </div>

            <input type="text" name="contact_number" placeholder="Contact Number" value="<?php echo htmlspecialchars($edit_user['contact_number']); ?>" required>
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
            
            <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
            <input type="password" name="confirm_password" placeholder="Confirm New Password (leave blank to keep current)">

            <div class="form-row">
                <select name="role" required>
                    <option value="user" <?= ($edit_user['role'] === 'user') ? 'selected' : '' ?>>user</option> 
                    <option value="admin" <?= ($edit_user['role'] === 'admin') ? 'selected' : '' ?>>admin</option>
                </select>
            </div>

            <div class="button-row">
                <button type="submit" name="submit">Update User</button>
                <button type="button" name="cancel" onclick="window.location.href='AdminDashboard.php';">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>