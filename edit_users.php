
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Users</title>
    <link rel="stylesheet" href="CSS/style.css">

</head>
<body>
    <div class="table-container">
        <table>
            <tr>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Gender</th>
                <th>Birthday</th>
                <th>Contact No</th>
                <th>Username</th>
                <th>Operation</th>
            </tr>

            <?php
                include 'connection.php';

                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
                    $update_id = $_POST['update_id'];
                    header("Location: update_form.php?id=" . $update_id);
                    exit();
                }

                $sql = "SELECT * FROM users";
                $result = $dbhandle->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['mname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['birthday']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>
                                <form method='POST' action='edit_users.php'>
                                    <input type='hidden' name='update_id' value='" . $row['id'] . "'>
                                    <button type='submit'>Update</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No users found.</td></tr>";
                }
                $dbhandle->close();
            ?>
        </table>
    </div>
</body>
</html>