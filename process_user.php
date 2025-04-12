<?php
session_start(); // Session kept only if needed for other functionality
include 'connection.php';

function jsAlertRedirect($message, $type = 'error', $redirectUrl = 'AdminDashboard.php') {
    $cleanMessage = addslashes(htmlspecialchars($message));
    $alertType = ($type === 'success') ? 'success' : 'error';
    echo "<script>
        alert('$alertType: $cleanMessage');
        window.location.href = '$redirectUrl';
    </script>";
    exit();
}

if (!$dbhandle) {
    jsAlertRedirect("Database connection failed. Please try again later.", 'error', 'AdminDashboard.php');
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $requiredFields = ['lname', 'fname', 'gender', 'birthday', 'cno', 'username', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        jsAlertRedirect("Missing required fields: " . implode(', ', $missingFields), 'error', 'create_user_form.php');
    }

    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact_no = trim($_POST['cno']);
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (lname, fname, mname, gender, birthday, contact_number, username, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbhandle, $sql);

    if (!$stmt) {
        jsAlertRedirect("Database preparation failed.", 'error', 'create_user_form.php');
    }

    mysqli_stmt_bind_param($stmt, "ssssssss", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $password_hashed);

    if (mysqli_stmt_execute($stmt)) {
        jsAlertRedirect("User created successfully!", 'success');
    } else {
        if (mysqli_errno($dbhandle) == 1062) {
            jsAlertRedirect("Username '$username' already exists.", 'error', 'create_user_form.php');
        } else {
            jsAlertRedirect("User creation failed: " . mysqli_error($dbhandle), 'error');
        }
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if (!$id || empty($_POST['lname']) || empty($_POST['fname']) || empty($_POST['gender']) || 
        empty($_POST['birthday']) || empty($_POST['cno']) || empty($_POST['username'])) {
        $redirect = isset($_POST['id']) ? "edit_user_form.php?id={$_POST['id']}" : 'AdminDashboard.php';
        jsAlertRedirect("Invalid or missing required fields.", 'error', $redirect);
    }

    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact_no = trim($_POST['cno']);
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];

    if (!empty($password_plain)) {
        $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET lname=?, fname=?, mname=?, gender=?, birthday=?, contact_number=?, username=?, password=? WHERE id=?";
        $stmt = mysqli_prepare($dbhandle, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $password_hashed, $id);
    } else {
        $sql = "UPDATE users SET lname=?, fname=?, mname=?, gender=?, birthday=?, contact_number=?, username=? WHERE id=?";
        $stmt = mysqli_prepare($dbhandle, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssi", $lname, $fname, $mname, $gender, $birthday, $contact_no, $username, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        jsAlertRedirect("User updated successfully!", 'success');
    } else {
        if (mysqli_errno($dbhandle) == 1062) {
            jsAlertRedirect("Username '$username' already exists.", 'error', "edit_user_form.php?id=$id");
        } else {
            jsAlertRedirect("Update failed: " . mysqli_error($dbhandle), 'error');
        }
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if (!$id) {
        jsAlertRedirect("Invalid user ID.", 'error');
    }

    mysqli_begin_transaction($dbhandle);

    try {
        // Delete related assessments
        $stmt = mysqli_prepare($dbhandle, "DELETE FROM assessment WHERE userId = ?");
        if (!$stmt) throw new Exception("Preparation failed: " . mysqli_error($dbhandle));
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (!mysqli_stmt_execute($stmt)) throw new Exception("Execution failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);

        // Delete user
        $stmt = mysqli_prepare($dbhandle, "DELETE FROM users WHERE id = ?");
        if (!$stmt) throw new Exception("Preparation failed: " . mysqli_error($dbhandle));
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (!mysqli_stmt_execute($stmt)) throw new Exception("Execution failed: " . mysqli_stmt_error($stmt));
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($dbhandle);

        if ($affected > 0) {
            jsAlertRedirect("User deleted successfully!", 'success');
        } else {
            jsAlertRedirect("User not found.", 'error');
        }
    } catch (Exception $e) {
        mysqli_rollback($dbhandle);
        jsAlertRedirect("Deletion failed: " . $e->getMessage(), 'error');
    }
}

else {
    jsAlertRedirect("Invalid request.", 'error');
}

if ($dbhandle) {
    mysqli_close($dbhandle);
}
?>