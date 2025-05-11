<?php
include 'session.php';
include 'connection.php';

$showResult = false;
$probability = 0;
$infected = 0;
$errorMessage = '';

if (isset($_POST['submit'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $errorMessage = "Error: User not logged in.";
        header("Location: login.php");
        exit;
    }

    // Validate inputs
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

    if ($age === false || $age === null) {
        $errorMessage = "Invalid age.";
    } elseif ($weight === false || $weight === null) {
        $errorMessage = "Invalid weight.";
    } elseif ($homo === null) {
        $errorMessage = "Invalid homosexual activity selection.";
    } elseif ($drugs === null) {
        $errorMessage = "Invalid drugs history selection.";
    } elseif ($oprior === null) {
        $errorMessage = "Invalid prior opportunistic infection selection.";
    } elseif ($z30 === null) {
        $errorMessage = "Invalid AZT use selection.";
    } elseif ($gender === null) {
        $errorMessage = "Invalid gender selection.";
    } elseif ($str2 === null) {
        $errorMessage = "Invalid antiretroviral history selection.";
    } elseif ($symptom === null) {
        $errorMessage = "Invalid symptomatic indicator selection.";
    } elseif ($treat === null) {
        $errorMessage = "Invalid treatment indicator selection.";
    } elseif ($offtrt === null) {
        $errorMessage = "Invalid off-treatment indicator selection.";
    }

    if (empty($errorMessage)) {
        $userId = $_SESSION['user_id'];

        // Verify userId exists in users table
        $checkUser = mysqli_prepare($dbhandle, "SELECT id FROM users WHERE id = ?");
        mysqli_stmt_bind_param($checkUser, "i", $userId);
        mysqli_stmt_execute($checkUser);
        mysqli_stmt_store_result($checkUser);

        if (mysqli_stmt_num_rows($checkUser) === 0) {
            $errorMessage = "Error: Invalid user ID.";
            mysqli_stmt_close($checkUser);
        } else {
            mysqli_stmt_close($checkUser);

            // Call Flask API for prediction
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
                $errorMessage = "Error: Prediction failed. Unable to connect to prediction API. $curl_error";
            } else {
                $result = json_decode($output, true);
                if (!$result || !isset($result['probability']) || !isset($result['infected'])) {
                    $errorMessage = "Error: Invalid prediction output from API. Output: " . addslashes($output);
                } else {
                    $probability = $result['probability'];
                    $infected = $result['infected'];

                    // Insert into assessment
                    $stmt = mysqli_prepare(
                        $dbhandle,
                        "INSERT INTO assessment (userId, age, weight, homo, drugs, oprior, z30, gender, str2, symptom, treat, offtrt, probability, infected) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($stmt, "idiidiiiiiiiid", $userId, $age, $weight, $homo, $drugs, $oprior, $z30, $gender, $str2, $symptom, $treat, $offtrt, $probability, $infected);

                    if (mysqli_stmt_execute($stmt)) {
                        $showResult = true;
                    } else {
                        $errorMessage = "Error: " . mysqli_error($dbhandle);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aids - HIV/AIDS Treatment Data</title>
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

    <?php if ($showResult): ?>
        <div class="result-card">
            <h1>Assessment Result</h1>
            <h3>Probability of Infection: <?php echo number_format($probability * 100, 2); ?>%</h3>
            <h4>Status: <?php echo $infected ? "INFECTED" : "NOT INFECTED"; ?></h4>
            <div class="button-group">
                <a href="Assess.php" class="btn btn-primary">Take Another Assessment</a>
                <a href="History.php" class="btn btn-secondary">View History</a>
            </div>
        </div>
    <?php elseif (!empty($errorMessage)): ?>
        <div class="error-card">
            <h2>Error</h2>
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
            <a href="Assess.php" class="btn btn-primary">Try Again</a>
        </div>
    <?php else: ?>
        <div class="assessment-form">
            <form action="Assess.php" method="post">
                <h2>Assessment Form</h2>
                <table>
                    <tr>
                        <td class="assessment">
                            <label for="age">Age (years)</label>
                            <input type="number" id="age" name="age" min="0" required>
                        </td>
                        <td class="assessment">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" step="0.1" min="0" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="assessment">
                            <label for="homo">Homosexual Activity</label>
                            <select id="homo" name="homo" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                        <td class="assessment">
                            <label for="drugs">History of IV Drug Use</label>
                            <select id="drugs" name="drugs" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="assessment">
                            <label for="oprior">Prior Opportunistic Infection</label>
                            <select id="oprior" name="oprior" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                        <td class="assessment">
                            <label for="z30">AZT Use in Last 30 Days</label>
                            <select id="z30" name="z30" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="assessment">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select</option>
                                <option value="0">Male</option>
                                <option value="1">Female</option>
                            </select>
                        </td>
                        <td class="assessment">
                            <label for="str2">Antiretroviral History</label>
                            <select id="str2" name="str2" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="assessment">
                            <label for="symptom">Symptomatic Indicator</label>
                            <select id="symptom" name="symptom" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                        <td class="assessment">
                            <label for="treat">Treatment Indicator</label>
                            <select id="treat" name="treat" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="assessment" colspan="2">
                            <label for="offtrt">Off-Treatment Indicator</label>
                            <select id="offtrt" name="offtrt" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <button type="submit" name="submit" id="submit-assessment">SUBMIT</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>