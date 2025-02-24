<?php
// Database configuration
$host = 'localhost';
$dbname = 'smart_health_tracker_with_ai';
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password (empty)

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Disable emulation of prepared statements
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    // If there is an error with the connection, stop the script and display the error.
    die("Connection failed: " . $e->getMessage());
}