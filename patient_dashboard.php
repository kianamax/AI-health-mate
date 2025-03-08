<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT u.id AS doctor_id, u.username AS doctor_name 
    FROM doctor_patient_relationships dpr 
    JOIN users u ON dpr.doctor_id = u.id 
    WHERE dpr.patient_id = ?
");
$stmt->execute([$user_id]);
$assigned_doctor = $stmt->fetch();

$stmt = $pdo->query("SELECT id, username AS doctor_name FROM users WHERE usertype = 'doctor'");
$all_doctors = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['date'])) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_date'])) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $notes = $_POST['appointment_notes'] ?? '';
    $status = ($doctor_id == ($assigned_doctor['doctor_id'] ?? null)) ? 'scheduled' : 'pending';

    // Insert the appointment
    $stmt = $pdo->prepare("
        INSERT INTO appointments (doctor_id, patient_id, appointment_date, status, notes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$doctor_id, $user_id, $appointment_date, $status, $notes]);

    // Fetch the doctor's username for better logging
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch();
    $doctor_name = $doctor['username'] ?? "Doctor ID: $doctor_id";

    // Log the appointment booking
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, action_date, details) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([
            $user_id,
            'Booked Appointment',
            "with $doctor_name on " . date('Y-m-d H:i', strtotime($appointment_date))
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log appointment: " . $e->getMessage());
        // Optionally, notify the user or continue without logging
    }

    $success_message = $status === 'scheduled' ? "Appointment scheduled successfully!" : "Appointment request submitted! Awaiting doctor approval.";
}

$stmt = $pdo->prepare("SELECT * FROM health_data WHERE user_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_data = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT a.*, u.username AS doctor_name 
    FROM appointments a 
    JOIN users u ON a.doctor_id = u.id 
    WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() 
    ORDER BY a.appointment_date ASC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$upcoming_appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Smart Health Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; position: relative; min-height: 100vh; color: #333; padding-top: 70px; padding-bottom: 80px; }
        .navbar { background: linear-gradient(90deg, #007bff, #00c6ff); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 15px 0; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; }
        .navbar-brand, .nav-link { color: white !important; font-weight: 600; }
        .nav-link:hover { color: #e0e0e0 !important; }
        .section { min-height: 100vh; display: flex; align-items: center; padding: 6rem 0; position: relative; scroll-margin-top: 70px; } /* Adjust for fixed navbar */
        .section-content { position: relative; z-index: 1; width: 100%; max-width: 1200px; margin: 0 auto; padding: 30px; background: rgba(255, 255, 255, 0.95); border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .section-title { color: #007bff; font-weight: 700; margin-bottom: 30px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); background: white; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12); }
        .btn-custom { background: #00bcd4; color: white; border-radius: 25px; padding: 10px 20px; font-weight: 600; text-transform: uppercase; border: none; transition: all 0.3s ease; }
        .btn-custom:hover { background: #0097a7; transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table thead { background: #007bff; color: white; }
        .list-group-item { background: transparent; border-color: rgba(0, 0, 0, 0.1); color: #333; }
        footer { position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; color: #666; font-size: 14px; background: rgba(255, 255, 255, 0.9); padding: 10px 0; }

        /* Creative Backgrounds */
        #recent-health-data { background: linear-gradient(135deg, #e0f7fa, #b3e5fc); }
        #health-services { background: linear-gradient(135deg, #b3e5fc, #81d4fa); position: relative; }
        #health-services::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1) 10px, transparent 10px, transparent 20px); opacity: 0.5; }
        #appointments { background: linear-gradient(135deg, #81d4fa, #4fc3f7); }
        #record-health-data { background: linear-gradient(135deg, #4fc3f7, #29b6f6); position: relative; }
        #record-health-data::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%); opacity: 0.6; }

        /* Smooth Scrolling */
        html { scroll-behavior: smooth; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Smart Health Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#recent-health-data">Recent Health Data</a></li>
                    <li class="nav-item"><a class="nav-link" href="#health-services">Health Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#appointments">Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="#record-health-data">Record Health Data</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section: Recent Health Data -->
    <section id="recent-health-data" class="section">
        <div class="section-content">
            <h1 class="section-title text-center">Hey <?php echo htmlspecialchars($_SESSION['username']); ?>, Here Is Your Recent Health Data</h1>
            <div class="card">
                <div class="card-body">
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
    </section>

    <!-- Section: Health Services -->
    <section id="health-services" class="section">
        <div class="section-content">
            <h2 class="section-title">Health Services</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Symptom Checker</h5>
                            <p class="card-text">Analyze your symptoms and get AI-powered insights.</p>
                            <a href="symptom_checker.php" class="btn btn-custom">Start Check</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Health Metrics Tracker</h5>
                            <p class="card-text">Log and monitor your vital health metrics.</p>
                            <a href="health_metrics.php" class="btn btn-custom">Track Metrics</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Diet Planner</h5>
                            <p class="card-text">Get personalized diet recommendations.</p>
                            <a href="diet_planner.php" class="btn btn-custom">Plan Diet</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Exercise Routine</h5>
                            <p class="card-text">Create a customized exercise plan.</p>
                            <a href="exercise_planner.php" class="btn btn-custom">Plan Workout</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Mental Health Assessment</h5>
                            <p class="card-text">Take a quick mental health screening.</p>
                            <a href="mental_health.php" class="btn btn-custom">Start Assessment</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Health History</h5>
                            <p class="card-text">View your health records and past analyses.</p>
                            <a href="health_history.php" class="btn btn-custom">View History</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Appointments -->
    <section id="appointments" class="section">
        <div class="section-content">
            <h2 class="section-title">Appointments</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Appointments</h5>
                    <?php if (empty($upcoming_appointments)): ?>
                        <p>No upcoming appointments.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <li class="list-group-item">
                                    Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?> - 
                                    <?php echo date('M d, Y H:i', strtotime($appointment['appointment_date'])); ?> 
                                    (<?php echo htmlspecialchars($appointment['status']); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Book an Appointment</h5>
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">Select Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-control" required>
                                <option value="">-- Choose a Doctor --</option>
                                <?php foreach ($all_doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>" <?php echo ($assigned_doctor && $doctor['id'] == $assigned_doctor['doctor_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Date and Time</label>
                            <input type="datetime-local" name="appointment_date" id="appointment_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_notes" class="form-label">Notes (Optional)</label>
                            <textarea name="appointment_notes" id="appointment_notes" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom">Book Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Record Health Data -->
    <section id="record-health-data" class="section">
        <div class="section-content">
            <h2 class="section-title">Record Health Data</h2>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
                    <?php endif; ?>
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
                        <button type="submit" class="btn btn-custom">Record Data</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2025 Smart Health Tracker. All rights reserved.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>