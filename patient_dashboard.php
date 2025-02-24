<?php
session_start();
require_once 'config.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $blood_pressure = $_POST['blood_pressure'];
    $heart_rate = $_POST['heart_rate'];
    $blood_sugar = $_POST['blood_sugar'];
    $steps = $_POST['steps'];
    $sleep_hours = $_POST['sleep_hours'];
    $notes = $_POST['notes'];

    $stmt = $pdo->prepare("INSERT INTO health_data (user_id, date, weight, height, blood_pressure, heart_rate, blood_sugar, steps, sleep_hours, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $date, $weight, $height, $blood_pressure, $heart_rate, $blood_sugar, $steps, $sleep_hours, $notes]);

    $success_message = "Health data recorded successfully!";
}

// Fetch recent health data
$stmt = $pdo->prepare("SELECT * FROM health_data WHERE user_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_data = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Health Tracker Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .service-card {
            transition: transform 0.3s ease;
        }
        .service-card:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">Smart Health Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column: Health Data Form and Recent Data -->
            <div class="col-lg-6 mb-4">
                <!-- Health Data Input Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">Record Health Data</h2>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="height" class="form-label">Height (cm)</label>
                                    <input type="number" class="form-control" id="height" name="height" step="0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                    <input type="text" class="form-control" id="blood_pressure" name="blood_pressure">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="heart_rate" class="form-label">Heart Rate (bpm)</label>
                                    <input type="number" class="form-control" id="heart_rate" name="heart_rate">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="blood_sugar" class="form-label">Blood Sugar (mg/dL)</label>
                                    <input type="number" class="form-control" id="blood_sugar" name="blood_sugar" step="0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="steps" class="form-label">Steps</label>
                                    <input type="number" class="form-control" id="steps" name="steps">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sleep_hours" class="form-label">Sleep Hours</label>
                                    <input type="number" class="form-control" id="sleep_hours" name="sleep_hours" step="0.1">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Record Data</button>
                        </form>
                    </div>
                </div>

                <!-- Recent Health Data Table -->
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Recent Health Data</h2>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Weight</th>
                                        <th>Blood Pressure</th>
                                        <th>Heart Rate</th>
                                        <th>Blood Sugar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_data as $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($data['date']); ?></td>
                                            <td><?php echo htmlspecialchars($data['weight']); ?> kg</td>
                                            <td><?php echo htmlspecialchars($data['blood_pressure']); ?></td>
                                            <td><?php echo htmlspecialchars($data['heart_rate']); ?> bpm</td>
                                            <td><?php echo htmlspecialchars($data['blood_sugar']); ?> mg/dL</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Services -->
            <div class="col-lg-6">
                <h2 class="mb-4">Health Services</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card service-card">
                            <div class="card-body">
                                <h5 class="card-title">Symptom Checker</h5>
                                <p class="card-text">Analyze your symptoms and get AI-powered insights.</p>
                                <a href="symptom_checker.php" class="btn btn-primary">Start Check</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card service-card">
                            <div class="card-body">
                                <h5 class="card-title">Health Metrics Tracker</h5>
                                <p class="card-text">Log and monitor your vital health metrics.</p>
                                <a href="health_metrics.php" class="btn btn-primary">Track Metrics</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card service-card">
                            <div class="card-body">
                                <h5 class="card-title">Diet Planner</h5>
                                <p class="card-text">Get personalized diet recommendations.</p>
                                <a href="diet_planner.php" class="btn btn-primary">Plan Diet</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card service-card">
                            <div class="card-body">
                                <h5 class="card-title">Exercise Routine</h5>
                                <p class="card-text">Create a customized exercise plan.</p>
                                <a href="exercise_planner.php" class="btn btn-primary">Plan Workout</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card service-card">
                            <div class="card-body">
                                <h5 class="card-title">Mental Health Assessment</h5>
                                <p class="card-text">Take a quick mental health screening.</p>
                                <a href="mental_health.php" class="btn btn-primary">Start Assessment</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card service-card">
                            <div class="card-body">
                                <h5 class="card-title">Health History</h5>
                                <p class="card-text">View your health records and past analyses.</p>
                                <a href="health_history.php" class="btn btn-primary">View History</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // You can add any custom