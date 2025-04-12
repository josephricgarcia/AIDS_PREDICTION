<?php
    include 'connection.php';

    $sql = "SELECT lname, fname, mname, contact_number, gender, birthday FROM users";
    $result = $dbhandle->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<table class="view-table">
    <tr>
        <th>Name</th>
        <th>Contact No</th>
        <th>Gender</th>
        <th>Birthday</th>
    </tr>

    <?php 
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['lname'] . " " . $row['fname'] . " " . $row['mname'] . "</td>";
            echo "<td>" . $row['contact_number'] . "</td>";
            echo "<td>" . $row['gender'] . "</td>";
            echo "<td>" . $row['birthday'] . "</td>";
            echo "</tr>";
            
            
        }
    } else {
        echo "<tr><td colspan='4'>No data found</td></tr>";
    }
    
    ?>
</table>
    
</body>
</html>