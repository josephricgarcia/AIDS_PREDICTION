<?php
include 'session.php';
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: LogIn.php");
    exit();
}

$userId = $_SESSION['user_id'];
$existingData = [];
$assessmentId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessmentId = isset($_POST['assessment_id']) ? (int)$_POST['assessment_id'] : 0;
} else {
    $assessmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

if ($assessmentId <= 0) {
    echo "<script>alert('Invalid assessment ID.'); window.location.href='History.php';</script>";
    exit();
}

$stmt = mysqli_prepare($dbhandle, "SELECT * FROM assessment WHERE userId = ? AND id = ?");
mysqli_stmt_bind_param($stmt, "ii", $userId, $assessmentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existingData = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$existingData) {
    echo "<script>alert('Assessment not found or no permission.'); window.location.href='History.php';</script>";
    exit();
}

if (isset($_POST['submit'])) {
    // Validation identical to Assess.php
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    $homo = filter_input(INPUT_POST, 'homo', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $drugs = filter_input(INPUT_POST, 'drugs', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $oprior = filter_input(INPUT_POST, 'oprior', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $z30 = filter_input(INPUT_POST, 'z30', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $str2 = filter_input(INPUT_POST, 'str2', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $symptom = filter_input(INPUT_POST, 'symptom', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $treat = filter_input(INPUT_POST, 'treat', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    $offtrt = filter_input(INPUT_POST, 'offtrt', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);

    $error = '';
    if ($age === false || $age === null) $error = "Invalid age.";
    elseif ($weight === false || $weight === null) $error = "Invalid weight.";
    elseif ($homo === null) $error = "Invalid homosexual activity selection.";
    elseif ($drugs === null) $error = "Invalid drugs history selection.";
    elseif ($oprior === null) $error = "Invalid prior infection selection.";
    elseif ($z30 === null) $error = "Invalid AZT use selection.";
    elseif ($gender === null) $error = "Invalid gender selection.";
    elseif ($str2 === null) $error = "Invalid antiretroviral history.";
    elseif ($symptom === null) $error = "Invalid symptom selection.";
    elseif ($treat === null) $error = "Invalid treatment selection.";
    elseif ($offtrt === null) $error = "Invalid off-treatment selection.";

    if (empty($error)) {
        // New prediction API call
        $url = "http://localhost:5000/predict";
        $data = [
            "age" => $age,
            "weight" => $weight,
            "homo" => $homo,
            "drugs" => $drugs,
            "oprior" => $oprior,
            "z30" => $z30,
            "gender" => $gender,
            "str2" => $str2,
            "symptom" => $symptom,
            "treat" => $treat,
            "offtrt" => $offtrt
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($output === false || trim($output) === '') {
            echo "<script>alert('API Error: $curl_error');</script>";
            exit;
        }

        $result = json_decode($output, true);
        if (!$result || !isset($result['infected'])) {
            echo "<script>alert('Invalid API response.');</script>";
            exit;
        }

        $infected = $result['infected'];

        // Update query with infected status
        $stmt = mysqli_prepare(
            $dbhandle,
            "UPDATE assessment 
             SET age=?, weight=?, homo=?, drugs=?, oprior=?, z30=?, 
                 gender=?, str2=?, symptom=?, treat=?, offtrt=?, infected=?
             WHERE id=? AND userId=?"
        );
        mysqli_stmt_bind_param(
            $stmt, 
            "ddiiiiiiiiiiii",
            $age, $weight, $homo, $drugs, $oprior, $z30,
            $gender, $str2, $symptom, $treat, $offtrt, $infected,
            $assessmentId, $userId
        );

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Assessment updated with new prediction!'); window.location.href='History.php';</script>";
        } else {
            echo "<script>alert('Database Error: " . mysqli_error($dbhandle) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('$error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assessment - HIV/AIDS Treatment Data</title>
    <link rel="stylesheet" href="CSS/assessment.css">
</head>
<body>
    <aside class="sidebar">
        <img src="IMAGE/blood.png" alt="Logo" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="Assess.php">Assessment</a></li>
            <li><a href="History.php">History</a></li>
            <li><a href="Aboutus.php">About</a></li>
            <li><a href="Settings.php">Settings</a></li>
        </ul>
    </aside>

    <div class="assessment-form">
        <form action="EditAssessment.php" method="post">
            <h2>Edit Assessment</h2>
            <input type="hidden" name="assessment_id" value="<?php echo $assessmentId; ?>">
            <table>
                <tr>
                    <td>
                        <label for="age">Age (years)</label>
                        <input type="number" id="age" name="age" min="0" required value="<?php echo htmlspecialchars($existingData['age']); ?>">
                    </td>
                    <td>
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.1" min="0" required value="<?php echo htmlspecialchars($existingData['weight']); ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="homo">Homosexual Activity</label>
                        <select id="homo" name="homo" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['homo'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['homo'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                    <td>
                        <label for="drugs">History of IV Drug Use</label>
                        <select id="drugs" name="drugs" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['drugs'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['drugs'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="oprior">Prior Opportunistic Infection</label>
                        <select id="oprior" name="oprior" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['oprior'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['oprior'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                    <td>
                        <label for="z30">AZT Use in Last 30 Days</label>
                        <select id="z30" name="z30" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['z30'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['z30'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['gender'] == 1 ? 'selected' : ''; ?>>Male</option>
                            <option value="0" <?php echo $existingData['gender'] == 0 ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </td>
                    <td>
                        <label for="str2">Antiretroviral History</label>
                        <select id="str2" name="str2" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['str2'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['str2'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="symptom">Symptomatic Indicator</label>
                        <select id="symptom" name="symptom" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['symptom'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['symptom'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                    <td>
                        <label for="treat">Treatment Indicator</label>
                        <select id="treat" name="treat" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['treat'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['treat'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="offtrt">Off-Treatment Indicator</label>
                        <select id="offtrt" name="offtrt" required>
                            <option value="" disabled>Select</option>
                            <option value="1" <?php echo $existingData['offtrt'] == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $existingData['offtrt'] == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
            </table>
            <button type="submit" name="submit" id="submit-assessment">UPDATE</button>
        </form>
    </div>
</body>
</html>