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

// Fetch patient details and medical history
$stmt = $pdo->prepare("SELECT u.username, u.email, hd.* FROM users u LEFT JOIN health_data hd ON hd.user_id = u.id WHERE u.id = ?");
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
    <title>View Patient - Smart Health Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; padding: 20px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); background: white; }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table thead { background: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Patient Details: <?php echo htmlspecialchars($patient['username']); ?></h1>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Contact Information</h5>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                <h5 class="card-title">Medical History</h5>
                <?php if ($patient['id']): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Weight (kg)</th>
                                    <th>Height (cm)</th>
                                    <th>Blood Pressure</th>
                                    <th>Heart Rate (bpm)</th>
                                    <th>Blood Sugar (mmol/L)</th>
                                    <th>Steps</th>
                                    <th>Sleep Hours</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM health_data WHERE user_id = ? ORDER BY created_at DESC");
                                $stmt->execute([$patient_id]);
                                $health_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($health_data)) {
                                    echo '<tr><td colspan="9" class="text-muted">No medical history available.</td></tr>';
                                } else {
                                    foreach ($health_data as $record): ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d', strtotime($record['date'])); ?></td>
                                            <td><?php echo $record['weight'] ?? 'N/A'; ?></td>
                                            <td><?php echo $record['height'] ?? 'N/A'; ?></td>
                                            <td><?php echo $record['blood_pressure'] ?? 'N/A'; ?></td>
                                            <td><?php echo $record['heart_rate'] ?? 'N/A'; ?></td>
                                            <td><?php echo $record['blood_sugar'] ?? 'N/A'; ?></td>
                                            <td><?php echo $record['steps'] ?? 'N/A'; ?></td>
                                            <td><?php echo $record['sleep_hours'] ?? 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($record['notes'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach;
                                } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No medical history available.</p>
                <?php endif; ?>
                <a href="doctor_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>