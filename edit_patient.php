<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$patient_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$patient_id) {
    die("Invalid patient ID.");
}

// Fetch patient details
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Smart Health Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; padding: 20px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); background: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Patient: <?php echo htmlspecialchars($patient['username']); ?></h1>
        <div class="card mt-4">
            <div class="card-body">
                <p class="text-muted">Editing functionality is not yet implemented. Contact admin to update patient details.</p>
                <a href="doctor_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>