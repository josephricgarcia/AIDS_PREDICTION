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
$sql = "SELECT * FROM users";
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
    <title>StresSense: Manage Users - AIDS Prediction</title>
    <link rel="stylesheet" href="CSS/accounts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="IMAGE/aidshiv.png">
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="AdminDashboard.php">Home</a></li>
            <li><a href="insights.php">Insights</a></li>
            <li><a href="accounts.php" class="active">Users</a></li>
             <li><a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <div class="main-content">
        <div class="container">
            <h2>Manage Users</h2>
           
            <?php if ($error_message): ?>
                <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <table class="user-table">
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
                            <td colspan="10" style="text-align: center;">No users found.</td>
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
                                <td class="action-buttons">
                                    <a href="edit_user_form.php?id=<?php echo $user['id']; ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                    <form method="POST" action="process_user.php" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="create_user_form.php" class="add-user-btn"><i class="fas fa-user-plus"></i> Add New User</a>
        </div>
    </div>
</body>
</html>
<?php
if ($dbhandle) {
    mysqli_close($dbhandle);
}
?>