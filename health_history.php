<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$health_data = $pdo->query("SELECT date, weight, blood_pressure, heart_rate FROM health_data WHERE user_id = $user_id ORDER BY date DESC")->fetchAll();
$symptoms = $pdo->query("SELECT symptoms, created_at FROM user_symptoms WHERE user_id = $user_id ORDER BY created_at DESC")->fetchAll();
$analyses = $pdo->query("SELECT analysis, created_at FROM symptom_analyses WHERE user_id = $user_id ORDER BY created_at DESC")->fetchAll();
$diet_plans = $pdo->query("SELECT plan_details, created_at FROM diet_plans WHERE user_id = $user_id ORDER BY created_at DESC")->fetchAll();
$exercise_routines = $pdo->query("SELECT routine_details, created_at FROM exercise_routines WHERE user_id = $user_id ORDER BY created_at DESC")->fetchAll();
$mental_assessments = $pdo->query("SELECT assessment_details, created_at FROM mental_health_assessments WHERE user_id = $user_id ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Health History</title>
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
        <h1>Health History</h1>
        <div class="accordion" id="historyAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#healthData">Health Data</button></h2>
                <div id="healthData" class="accordion-collapse collapse show" data-bs-parent="#historyAccordion">
                    <div class="accordion-body">
                        <table class="table table-striped">
                            <thead><tr><th>Date</th><th>Weight</th><th>Blood Pressure</th><th>Heart Rate</th></tr></thead>
                            <tbody><?php foreach ($health_data as $data): ?>
                                <tr><td><?php echo $data['date']; ?></td><td><?php echo $data['weight']; ?></td><td><?php echo $data['blood_pressure']; ?></td><td><?php echo $data['heart_rate']; ?></td></tr>
                            <?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#symptoms">Symptoms</button></h2>
                <div id="symptoms" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                    <div class="accordion-body">
                        <?php foreach ($symptoms as $symptom): ?>
                            <p><strong><?php echo $symptom['created_at']; ?>:</strong> <?php echo htmlspecialchars($symptom['symptoms']); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#analyses">Symptom Analyses</button></h2>
                <div id="analyses" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                    <div class="accordion-body">
                        <?php foreach ($analyses as $analysis): ?>
                            <p><strong><?php echo $analysis['created_at']; ?>:</strong> <?php echo nl2br(htmlspecialchars($analysis['analysis'])); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dietPlans">Diet Plans</button></h2>
                <div id="dietPlans" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                    <div class="accordion-body">
                        <?php foreach ($diet_plans as $plan): ?>
                            <p><strong><?php echo $plan['created_at']; ?>:</strong> <?php echo nl2br(htmlspecialchars($plan['plan_details'])); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#exerciseRoutines">Exercise Routines</button></h2>
                <div id="exerciseRoutines" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                    <div class="accordion-body">
                        <?php foreach ($exercise_routines as $routine): ?>
                            <p><strong><?php echo $routine['created_at']; ?>:</strong> <?php echo nl2br(htmlspecialchars($routine['routine_details'])); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mentalAssessments">Mental Health Assessments</button></h2>
                <div id="mentalAssessments" class="accordion-collapse collapse" data-bs-parent="#historyAccordion">
                    <div class="accordion-body">
                        <?php foreach ($mental_assessments as $assessment): 
                            $data = json_decode($assessment['assessment_details'], true); ?>
                            <p><strong><?php echo $assessment['created_at']; ?>:</strong></p>
                            <p>Mood: <?php echo $data['responses']['mood']; ?>/5, Sleep: <?php echo $data['responses']['sleep']; ?>/5</p>
                            <p><?php echo nl2br(htmlspecialchars($data['analysis'])); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>