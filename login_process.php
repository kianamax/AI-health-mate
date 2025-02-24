<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['usertype'] = $user['usertype'];

        switch ($user['usertype']) {
            case 'admin':
                header("Location: admin_dashboard.php");
                break;
            case 'doctor':
                header("Location: doctor_dashboard.php");
                break;
            case 'patient':
                header("Location: patient_dashboard.php");
                break;
            default:
                header("Location: login.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header("Location: login.php");
        exit();
    }
}
?>