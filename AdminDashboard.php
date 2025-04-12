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
$sql = "SELECT * FROM users ORDER BY id DESC";
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
    <title>StresSense: Admin Dashboard</title>
    <link rel="stylesheet" href="CSS/admindashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


</head>
<body>
    

    <div class="admin-container">
        <h1> Manage Users</h1>
        <div class="admin-header">
            <a href="logout.php" class="admin-logout-btn">Logout</a>
        </div>
        <?php if ($error_message): ?>
            <div class="admin-message admin-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
             <div class="admin-message admin-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <table class="admin-user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Gender</th>
                    <th>Birthday</th>
                    <th>Contact No</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['lname']); ?></td>
                            <td><?php echo htmlspecialchars($user['fname']); ?></td>
                            <td><?php echo htmlspecialchars($user['mname']); ?></td>
                            <td><?php
                                switch($user['gender']) {
                                    case 'm': echo 'Male'; break;
                                    case 'f': echo 'Female'; break;
                                    case 'x': echo 'Prefer not to say'; break;
                                    default: echo htmlspecialchars($user['gender']);
                                }
                            ?></td>
                            <td><?php echo htmlspecialchars($user['birthday']); ?></td>
                            <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="admin-action-buttons">
                                <a href="edit_user_form.php?id=<?php echo $user['id']; ?>" class="admin-edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="process_user.php?action=delete&id=<?php echo $user['id']; ?>" class="admin-delete-btn"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="create_user_form.php" class="admin-add-user-btn">
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>

</body>
</html>
<?php
// Close the database connection at the end of the script
if ($dbhandle) {
    mysqli_close($dbhandle);
}
?>
