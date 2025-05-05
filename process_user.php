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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id']) && $_POST['action'] === 'delete') {
    $userId = intval($_POST['id']);

    // Prevent deleting the currently logged-in admin
    if ($_SESSION['user_id'] == $userId) {
        echo "<script>alert('You cannot delete your own account.'); window.location.href='AdminDashboard.php';</script>";
        exit();
    }

    $query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($dbhandle, $query);
    
    if (!$stmt) {
        echo "<script>alert('Database error: " . mysqli_error($dbhandle) . "'); window.location.href='AdminDashboard.php';</script>";
        exit();
    }

    mysqli_stmt_bind_param($stmt, 'i', $userId);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('User deleted successfully.'); window.location.href='AdminDashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting user: " . mysqli_error($dbhandle) . "'); window.location.href='AdminDashboard.php';</script>";
    }

    mysqli_stmt_close($stmt);
    exit();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='AdminDashboard.php';</script>";
    exit();
}

if ($dbhandle) {
    mysqli_close($dbhandle);
}
?>
