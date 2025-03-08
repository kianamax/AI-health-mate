<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM logs WHERE action_date >= CURDATE() - INTERVAL 7 DAY ORDER BY action_date DESC LIMIT 5");
$stmt->execute();
$recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Health Tracker</title>
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
        #recent-activity { background: linear-gradient(135deg, #e0f7fa, #b3e5fc); }
        #users { background: linear-gradient(135deg, #b3e5fc, #81d4fa); position: relative; }
        #users::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1) 10px, transparent 10px, transparent 20px); opacity: 0.5; }

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
                    <li class="nav-item"><a class="nav-link" href="#recent-activity">Recent Activity</a></li>
                    <li class="nav-item"><a class="nav-link" href="#users">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section: Recent Activity -->
    <!-- Section: Recent Activity -->
<section id="recent-activity" class="section">
    <div class="section-content">
        <h1 class="section-title text-center">Hey <?php echo htmlspecialchars($_SESSION['username']); ?>, Here Is Your Recent Activity</h1>
        <div class="card">
            <div class="card-body">
                <?php if (empty($recent_activity)): ?>
                    <p class="text-muted">No recent activity found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Date</th><th>Patient</th><th>Action</th><th>Details</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                // Join logs with users to get patient username
                                $stmt = $pdo->prepare("
                                    SELECT l.*, u.username 
                                    FROM logs l
                                    JOIN users u ON l.user_id = u.id
                                    WHERE l.action_date >= CURDATE() - INTERVAL 7 DAY 
                                    ORDER BY l.action_date DESC 
                                    LIMIT 5
                                ");
                                $stmt->execute();
                                $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($recent_activity as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['action_date']); ?></td>
                                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section: Users -->
    <section id="users" class="section">
        <div class="section-content">
            <h2 class="section-title">Manage Users</h2>
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Users: <?php echo $total_users; ?></h5>
                    <a href="manage_users.php" class="btn btn-custom">View All Users</a>
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