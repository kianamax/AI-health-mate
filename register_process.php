<?php
session_start();
require_once 'config.php'; // Make sure this file exists and contains your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $usertype = $_POST['usertype'];
    $town = $_POST['town'];

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username or email already exists.";
        header("Location: login.php");
        exit();
    }

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, usertype, town) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$username, $email, $password, $usertype, $town])) {
        $_SESSION['success'] = "Registration successful. You can now log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: login.php");
        exit();
    }
}
?>