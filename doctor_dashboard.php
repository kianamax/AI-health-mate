<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

// Handle appointment approval
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $appointment_id = (int)$_GET['approve'];
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'scheduled' WHERE id = ? AND doctor_id = ? AND status = 'pending'");
    $stmt->execute([$appointment_id, $doctor_id]);
    header("Location: doctor_dashboard.php");
    exit();
}

// Fetch patients with upcoming appointments
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username AS name, u.email, a.appointment_date, a.status, a.id AS appointment_id
    FROM users u
    JOIN appointments a ON a.patient_id = u.id
    WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date DESC
");
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending appointments
$stmt = $pdo->prepare("
    SELECT a.id AS appointment_id, u.username AS name, u.email, a.appointment_date, a.status, a.notes
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ? AND a.status = 'pending'
    ORDER BY a.appointment_date ASC
");
$stmt->execute([$doctor_id]);
$pending_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Smart Health Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; position: relative; min-height: 100vh; color: #333; padding-top: 70px; padding-bottom: 80px; }
        .navbar { background: linear-gradient(90deg, #007bff, #00c6ff); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 15px 0; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; }
        .navbar-brand, .nav-link { color: white !important; font-weight: 600; }
        .nav-link:hover { color: #e0e0e0 !important; }
        .section { min-height: 100vh; display: flex; align-items: center; padding: 6rem 0; position: relative; scroll-margin-top: 70px; }
        .section-content { position: relative; z-index: 1; width: 100%; max-width: 1200px; margin: 0 auto; padding: 30px; background: rgba(255, 255, 255, 0.95); border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .section-title { color: #007bff; font-weight: 700; margin-bottom: 30px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); background: white; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12); }
        .btn-custom { background: #00bcd4; color: white; border-radius: 25px; padding: 10px 20px; font-weight: 600; text-transform: uppercase; border: none; transition: all 0.3s ease; }
        .btn-custom:hover { background: #0097a7; transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table thead { background: #007bff; color: white; }
        footer { position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; color: #666; font-size: 14px; background: rgba(255, 255, 255, 0.9); padding: 10px 0; }

        /* Creative Backgrounds */
        #patient-list { background: linear-gradient(135deg, #e0f7fa, #b3e5fc); }
        #pending-appointments { background: linear-gradient(135deg, #b3e5fc, #81d4fa); position: relative; }
        #pending-appointments::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1) 10px, transparent 10px, transparent 20px); opacity: 0.5; }

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
                    <li class="nav-item"><a class="nav-link" href="#patient-list">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pending-appointments">Pending Appointments</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section: Patient List -->
    <section id="patient-list" class="section">
        <div class="section-content">
            <h2 class="section-title">Your Patients</h2>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Latest Appointment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($patients)) {
                                    echo '<tr><td colspan="5" class="text-muted">No patients with upcoming appointments.</td></tr>';
                                } else {
                                    foreach ($patients as $patient): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                            <td><?php echo $patient['appointment_date'] ? date('M d, Y H:i', strtotime($patient['appointment_date'])) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($patient['status'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="view_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-custom btn-sm">View</a>
                                                <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-custom btn-sm">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Pending Appointments -->
    <section id="pending-appointments" class="section">
        <div class="section-content">
            <h2 class="section-title">Pending Appointments</h2>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Email</th>
                                    <th>Appointment Date</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($pending_appointments)) {
                                    echo '<tr><td colspan="5" class="text-muted">No pending appointments.</td></tr>';
                                } else {
                                    foreach ($pending_appointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($appointment['name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['notes'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="?approve=<?php echo $appointment['appointment_id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this appointment?');">Approve</a>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                } ?>
                            </tbody>
                        </table>
                    </div>
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