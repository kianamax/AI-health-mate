<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: admin_dashboard.php");
    exit();
}

// Prepare and execute the query
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(); // Fetch the result from the PDOStatement object

if (!$user) {
    // Optionally handle the case where no user is found
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>/* Reuse styles */</style>
</head>
<body>
    <div class="container mt-5">
        <h1>User Details: <?php echo htmlspecialchars($user['username']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($user['usertype']); ?></p>
        <p><strong>Town:</strong> <?php echo htmlspecialchars($user['town']); ?></p>
        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</body>
</html>