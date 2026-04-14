<?php
// config/db.php

$host = 'localhost';
$db_name = 'smart_city';
$username = 'root';
$password = 'password'; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Determine if the error is "Unknown database" to help with first-time setup
    if ($e->getCode() == 1049) {
        die("Database 'smart_city' not found. Please run the setup script or import schema.sql.");
    }
    die("Connection failed: " . $e->getMessage());
}
?>
