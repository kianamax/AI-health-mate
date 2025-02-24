<?php
session_start();
// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
        <p class="lead mb-5">Choose a health service to get started:</p>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card service-card">
                    <div class="card-body">
                        <h5 class="card-title">Symptom Checker</h5>
                        <p class="card-text">Analyze your symptoms and get AI-powered insights.</p>
                        <a href="symptom_checker.php" class="btn btn-primary">Start Check</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card service-card">
                    <div class="card-body">
                        <h5 class="card-title">Health Metrics Tracker</h5>
                        <p class="card-text">Log and monitor your vital health metrics.</p>
                        <a href="health_metrics.php" class="btn btn-primary">Track Metrics</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card service-card">
                    <div class="card-body">
                        <h5 class="card-title">Diet Planner</h5>
                        <p class="card-text">Get personalized diet recommendations.</p>
                        <a href="diet_planner.php" class="btn btn-primary">Plan Diet</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card service-card">
                    <div class="card-body">
                        <h5 class="card-title">Exercise Routine</h5>
                        <p class="card-text">Create a customized exercise plan.</p>
                        <a href="exercise_planner.php" class="btn btn-primary">Plan Workout</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card service-card">
                    <div class="card-body">
                        <h5 class="card-title">Mental Health Assessment</h5>
                        <p class="card-text">Take a quick mental health screening.</p>
                        <a href="mental_health.php" class="btn btn-primary">Start Assessment</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
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

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>