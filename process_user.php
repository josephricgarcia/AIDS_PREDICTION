<?php
session_start(); // Start the session to store messages

include 'connection.php';

if (!$dbhandle) {
    error_log("Database connection failed in process_user.php: " . mysqli_connect_error());
    $_SESSION['error_message'] = "Database connection error. Please try again later.";
    header("Location: AdminDashboard.php");
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;


if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact_no = trim($_POST['cno']);
    $username = trim($_POST['username']);
    $password_plain = $_POST['password']; 

    
    if (empty($lname) || empty($fname) || empty($gender) || empty($birthday) || empty($contact_no) || empty($username) || empty($password_plain)) {
        $_SESSION['error_message'] = "All required fields must be filled for creating a user.";
        header("Location: create_user_form.php"); 
        exit();
    }

   
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (lname, fname, mname, gender, birthday, cno, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbhandle, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $password_hashed);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "User created successfully!";
        } else {

            error_log("Create user failed: " . mysqli_stmt_error($stmt));

            if (mysqli_errno($dbhandle) == 1062) {
                 $_SESSION['error_message'] = "Username '$username' already exists. Please choose a different one.";
                 header("Location: create_user_form.php"); 
                 exit();
            } else {
                $_SESSION['error_message'] = "Failed to create user. Database error.";
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare failed (INSERT): " . mysqli_error($dbhandle));
        $_SESSION['error_message'] = "Failed to prepare user creation statement.";
    }

    header("Location: AdminDashboard.php"); 
    exit();

}

elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);


    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact_no = trim($_POST['cno']);
    $username = trim($_POST['username']);
    $password_plain = $_POST['password']; 


     if ($id === false || empty($lname) || empty($fname) || empty($gender) || empty($birthday) || empty($contact_no) || empty($username)) {
        $_SESSION['error_message'] = "Invalid ID or missing required fields for updating user.";

        header("Location: " . (isset($_POST['id']) ? "edit_user_form.php?id=".$_POST['id'] : "AdminDashboard.php"));
        exit();
    }

    if (!empty($password_plain)) {

        $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET lname=?, fname=?, mname=?, gender=?, birthday=?, cno=?, username=?, password=? WHERE id=?";
        $stmt = mysqli_prepare($dbhandle, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssssi", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $password_hashed, $id);
        }
    } else {

        $sql = "UPDATE users SET lname=?, fname=?, mname=?, gender=?, birthday=?, cno=?, username=? WHERE id=?";
        $stmt = mysqli_prepare($dbhandle, $sql);
         if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssi", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $id);
         }
    }


     if (isset($stmt) && $stmt) {
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "User updated successfully!";
        } else {
             error_log("Update user failed: " . mysqli_stmt_error($stmt));

            if (mysqli_errno($dbhandle) == 1062) {
                 $_SESSION['error_message'] = "Username '$username' already exists. Please choose a different one.";
                 header("Location: edit_user_form.php?id=".$id); 
                 exit();
            } else {
                $_SESSION['error_message'] = "Failed to update user. Database error.";
            }
        }
        mysqli_stmt_close($stmt);
    } else {
         error_log("Prepare failed (UPDATE): " . mysqli_error($dbhandle));
        $_SESSION['error_message'] = "Failed to prepare user update statement.";
    }

    header("Location: AdminDashboard.php"); 
    exit();

}

elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false) {
        $_SESSION['error_message'] = "Invalid user ID for deletion.";
        header("Location: AdminDashboard.php");
        exit();
    }

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($dbhandle, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
           
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $_SESSION['success_message'] = "User deleted successfully!";
            } else {
                 $_SESSION['error_message'] = "User with ID " . htmlspecialchars($id) . " not found or already deleted.";
            }
        } else {
            error_log("Delete user failed: " . mysqli_stmt_error($stmt));
            $_SESSION['error_message'] = "Failed to delete user. Database error.";
        }
        mysqli_stmt_close($stmt);
    } else {
         error_log("Prepare failed (DELETE): " . mysqli_error($dbhandle));
        $_SESSION['error_message'] = "Failed to prepare user deletion statement.";
    }

    header("Location: AdminDashboard.php"); 
    exit();

}

else {
    $_SESSION['error_message'] = "Invalid action or request method.";
    header("Location: AdminDashboard.php");
    exit();
}

if ($dbhandle) {
    mysqli_close($dbhandle);
}
?>