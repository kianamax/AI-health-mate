<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $goals = trim($_POST['goals']);

    // Fetch health data
    $stmt = $pdo->prepare("SELECT weight, height, steps FROM health_data WHERE user_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $health_data = $stmt->fetch();
    $health_context = $health_data ? "Weight: {$health_data['weight']}kg, Height: {$health_data['height']}cm, Steps: {$health_data['steps']}" : "No recent health data";

    // OpenAI prompt
    $prompt = "Based on exercise goals: '$goals', and health data ($health_context), suggest a general weekly exercise routine. Focus on fitness tips and recommend consulting a trainer.";
    $apiKey = OPENAI_API_KEY;
    $url = "https://api.openai.com/v1/chat/completions";
    $data = ["model" => "gpt-3.5-turbo", "messages" => [["role" => "user", "content" => $prompt]], "max_tokens" => 200];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer $apiKey"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $routine_details = $result['choices'][0]['message']['content'] ?? "Error generating routine.";
    curl_close($ch);

    // Insert into exercise_routines
    $stmt = $pdo->prepare("INSERT INTO exercise_routines (user_id, routine_details) VALUES (?, ?)");
    $stmt->execute([$user_id, $routine_details]);

    $success_message = "Exercise routine generated successfully!";
}

// Fetch latest routine
$stmt = $pdo->prepare("SELECT routine_details, created_at FROM exercise_routines WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$routine = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exercise Routine</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>/* Reuse styles */</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="patient_dashboard.php">Smart Health Tracker</a>
            <div class="ms-auto"><a class="nav-link" href="logout.php">Logout</a></div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1>Exercise Routine</h1>
        <?php if (isset($success_message)) echo "<div class='alert alert-success'>$success_message</div>"; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Create Your Routine</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="goals" class="form-label">Exercise Goals</label>
                        <textarea class="form-control" id="goals" name="goals" rows="2" required placeholder="e.g., endurance, strength"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Routine</button>
                </form>
            </div>
        </div>
        <?php if ($routine): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Your Routine (<?php echo $routine['created_at']; ?>)</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($routine['routine_details'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>