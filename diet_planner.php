<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $preferences = trim($_POST['preferences']);
    $goals = trim($_POST['goals']);

    // Fetch recent health data
    $stmt = $pdo->prepare("SELECT weight, height, blood_sugar FROM health_data WHERE user_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $health_data = $stmt->fetch();
    $health_context = $health_data ? "Weight: {$health_data['weight']}kg, Height: {$health_data['height']}cm, Blood Sugar: {$health_data['blood_sugar']}mg/dL" : "No recent health data";

    // OpenAI prompt
    $prompt = "Based on dietary preferences: '$preferences', goals: '$goals', and health data ($health_context), suggest a general daily diet plan. Focus on nutrition tips and encourage consulting a dietitian for specific needs.";
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
    $plan_details = $result['choices'][0]['message']['content'] ?? "Error generating plan.";
    curl_close($ch);

    // Insert into diet_plans
    $stmt = $pdo->prepare("INSERT INTO diet_plans (user_id, plan_details) VALUES (?, ?)");
    $stmt->execute([$user_id, $plan_details]);

    $success_message = "Diet plan generated successfully!";
}

// Fetch latest diet plan
$stmt = $pdo->prepare("SELECT plan_details, created_at FROM diet_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$diet_plan = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diet Planner</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>/* Reuse styles from symptom_checker.php */</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="patient_dashboard.php">Smart Health Tracker</a>
            <div class="ms-auto"><a class="nav-link" href="logout.php">Logout</a></div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1>Diet Planner</h1>
        <?php if (isset($success_message)) echo "<div class='alert alert-success'>$success_message</div>"; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Create Your Diet Plan</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="preferences" class="form-label">Dietary Preferences</label>
                        <textarea class="form-control" id="preferences" name="preferences" rows="2" required placeholder="e.g., vegetarian, low-carb"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="goals" class="form-label">Goals</label>
                        <textarea class="form-control" id="goals" name="goals" rows="2" required placeholder="e.g., weight loss, muscle gain"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Plan</button>
                </form>
            </div>
        </div>
        <?php if ($diet_plan): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Your Diet Plan (<?php echo $diet_plan['created_at']; ?>)</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($diet_plan['plan_details'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>