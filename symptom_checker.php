<?php
session_start();
require_once 'config.php'; // Loads PDO connection and OPENAI_API_KEY

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission for symptoms
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $symptoms = trim($_POST['symptoms']);
    if (!empty($symptoms)) {
        $stmt = $pdo->prepare("INSERT INTO user_symptoms (user_id, symptoms) VALUES (?, ?)");
        $stmt->execute([$user_id, $symptoms]);

        // Fetch recent health data for context
        $health_stmt = $pdo->prepare("SELECT weight, blood_pressure, heart_rate, blood_sugar, steps, sleep_hours FROM health_data WHERE user_id = ? ORDER BY date DESC LIMIT 1");
        $health_stmt->execute([$user_id]);
        $health_data = $health_stmt->fetch();

        // Prepare prompt based on health data availability
        if ($health_data) {
            $prompt = "Based on these symptoms: '$symptoms', and the following health data (weight: {$health_data['weight']}kg, blood pressure: {$health_data['blood_pressure']}, heart rate: {$health_data['heart_rate']}bpm, blood sugar: {$health_data['blood_sugar']}mg/dL, steps: {$health_data['steps']}, sleep hours: {$health_data['sleep_hours']}), suggest possible general lifestyle tips or recommendations. Do not provide medical diagnoses or prescriptions—only general advice, and encourage consulting a doctor if symptoms persist or worsen.";
        } else {
            $prompt = "Based on these symptoms: '$symptoms', suggest possible general lifestyle tips or recommendations. Do not provide medical diagnoses or prescriptions—only general advice, and encourage consulting a doctor if symptoms persist or worsen.";
        }

        // Call OpenAI API for analysis
        $apiKey = OPENAI_API_KEY;
        $url = "https://api.openai.com/v1/chat/completions";
        $data = [
            "model" => "gpt-3.5-turbo",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 150
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            $analysis = "Error: " . curl_error($ch) . " (HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ")";
        } else {
            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $analysis = "JSON Error: " . json_last_error_msg();
            } else {
                $analysis = $result['choices'][0]['message']['content'] ?? "No insights available. Raw response: " . print_r($result, true);
            }
        }
        curl_close($ch);

        // Store analysis in symptom_analyses
        $stmt = $pdo->prepare("INSERT INTO symptom_analyses (user_id, analysis) VALUES (?, ?)");
        $stmt->execute([$user_id, $analysis]);

        $success_message = "Symptoms analyzed successfully! Check your insights below.";
    } else {
        $error_message = "Please enter your symptoms before submitting.";
    }
}

// Fetch existing symptoms for display
$stmt = $pdo->prepare("SELECT symptoms FROM user_symptoms WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$existing_symptoms = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symptom Checker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="patient_dashboard.php">Smart Health Tracker</a>
            <div class="ms-auto">
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Symptom Checker</h1>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Enter Your Symptoms</h2>
                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label for="symptoms" class="form-label">Describe Your Symptoms</label>
                        <textarea class="form-control" id="symptoms" name="symptoms" rows="4" required><?php echo htmlspecialchars($existing_symptoms ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Analyze Symptoms</button>
                </form>
            </div>
        </div>

        <?php if (isset($analysis)): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">AI Insights</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($analysis)); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>