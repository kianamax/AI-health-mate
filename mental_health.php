<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $responses = [
        "mood" => (int)$_POST['mood'],
        "sleep" => (int)$_POST['sleep']
    ];

    // OpenAI prompt
    $prompt = "Based on mental health responses (1-5 scale, 1=poor, 5=excellent): Mood: {$responses['mood']}, Sleep: {$responses['sleep']}, provide general wellness tips. Avoid diagnoses and encourage consulting a professional.";
    $apiKey = OPENAI_API_KEY;
    $url = "https://api.openai.com/v1/chat/completions";
    $data = ["model" => "gpt-3.5-turbo", "messages" => [["role" => "user", "content" => $prompt]], "max_tokens" => 150];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer $apiKey"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    $analysis = $result['choices'][0]['message']['content'] ?? "Error generating analysis.";
    curl_close($ch);

    // Combine responses and analysis
    $assessment_details = json_encode(["responses" => $responses, "analysis" => $analysis]);

    // Insert into mental_health_assessments
    $stmt = $pdo->prepare("INSERT INTO mental_health_assessments (user_id, assessment_details) VALUES (?, ?)");
    $stmt->execute([$user_id, $assessment_details]);

    $success_message = "Assessment completed successfully!";
}

// Fetch latest assessment
$stmt = $pdo->prepare("SELECT assessment_details, created_at FROM mental_health_assessments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$assessment = $stmt->fetch();
$assessment_data = $assessment ? json_decode($assessment['assessment_details'], true) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mental Health Assessment</title>
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
        <h1>Mental Health Assessment</h1>
        <?php if (isset($success_message)) echo "<div class='alert alert-success'>$success_message</div>"; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Take the Assessment</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label>How’s your mood today? (1-5)</label>
                        <input type="number" class="form-control" name="mood" min="1" max="5" required>
                    </div>
                    <div class="mb-3">
                        <label>How well did you sleep? (1-5)</label>
                        <input type="number" class="form-control" name="sleep" min="1" max="5" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
        <?php if ($assessment_data): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Your Insights (<?php echo $assessment['created_at']; ?>)</h5>
                    <p><strong>Mood:</strong> <?php echo $assessment_data['responses']['mood']; ?>/5</p>
                    <p><strong>Sleep:</strong> <?php echo $assessment_data['responses']['sleep']; ?>/5</p>
                    <p><strong>Tips:</strong> <?php echo nl2br(htmlspecialchars($assessment_data['analysis'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>