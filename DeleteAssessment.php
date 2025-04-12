<?php
include 'session.php';
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessmentId = $_POST['assessment_id'] ?? null;

    if ($assessmentId) {
        // Prepare the delete query
        $query = "DELETE FROM assessment WHERE id = ? AND userId = ?";
        $stmt = mysqli_prepare($dbhandle, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $assessmentId, $_SESSION['user_id']);
            if (mysqli_stmt_execute($stmt)) {
               
                echo "<script>
                    alert('Assessment deleted successfully');
                    window.location.href = 'History.php';
                </script>";
                exit;
            } else {
                echo "<script>
                    alert('Failed to delete assessment');
                    window.location.href = 'History.php';
                </script>";
                exit;
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<script>
                alert('Error preparing the delete statement');
                window.location.href = 'History.php';
            </script>";
            exit;
        }
    } else {
        echo "<script>
            alert('Invalid assessment ID');
            window.location.href = 'History.php';
        </script>";
        exit;
    }
} else {
    echo "<script>
        alert('Invalid request method');
        window.location.href = 'History.php';
    </script>";
    exit;
}

mysqli_close($dbhandle);
?>
