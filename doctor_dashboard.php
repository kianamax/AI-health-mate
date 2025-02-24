<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Fetch doctor's information
$doctor_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

// Fetch doctor's patients
$stmt = $pdo->prepare("SELECT * FROM patients WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll();

// Fetch upcoming appointments
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date >= CURDATE() ORDER BY appointment_date ASC LIMIT 5");
$stmt->execute([$doctor_id]);
$upcoming_appointments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Smart Health Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            color: #fff;
            min-height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            padding: 30px;
            margin-top: 50px;
            margin-bottom: 50px;
        }
        .btn-custom {
            background-color: #00bcd4;
            color: #fff;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px;
            border: none;
        }
        .btn-custom:hover {
            background-color: #0097a7;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .table {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        .table th, .table td {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.2);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .navbar-brand, .nav-link {
            color: #fff !important;
        }
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
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <header class="mb-4">
            <h1 class="display-4 fw-bold">Welcome, Dr. <?php echo htmlspecialchars($doctor['username']); ?></h1>
        </header>

        <main>
            <section id="quick-stats" class="mb-4">
                <h2 class="h3 mb-3">Quick Stats</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users"></i> Total Patients</h5>
                                <p class="card-text display-6"><?php echo count($patients); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-calendar-check"></i> Upcoming Appointments</h5>
                                <p class="card-text display-6"><?php echo count($upcoming_appointments); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="upcoming-appointments" class="mb-4">
                <h2 class="h3 mb-3">Upcoming Appointments</h2>
                <div class="card">
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <li class="list-group-item bg-transparent">
                                    <i class="fas fa-user-clock"></i>
                                    <?php echo htmlspecialchars($appointment['patient_name']); ?> - 
                                    <?php echo htmlspecialchars($appointment['appointment_date']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="patient-list" class="mb-4">
                <h2 class="h3 mb-3">Your Patients</h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                    <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                    <td>
                                        <a href="view_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-custom btn-sm"><i class="fas fa-eye"></i> View</a>
                                        <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-custom btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="quick-actions">
                <h2 class="h3 mb-3">Quick Actions</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <a href="add_patient.php" class="btn btn-custom w-100"><i class="fas fa-user-plus"></i> Add New Patient</a>
                    </div>
                    <div class="col-md-4">
                        <a href="schedule_appointment.php" class="btn btn-custom w-100"><i class="fas fa-calendar-plus"></i> Schedule Appointment</a>
                    </div>
                    <div class="col-md-4">
                        <a href="view_medical_records.php" class="btn btn-custom w-100"><i class="fas fa-file-medical"></i> View Medical Records</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <footer class="text-center py-4">
        <p>&copy; 2025 Smart Health Tracker. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>