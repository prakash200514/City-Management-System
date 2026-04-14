<?php
// seed_users.php
require 'config/db.php';

echo "<h2>Seeding Initial Users...</h2>";

// 1. Get Super Admin Role ID
$stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'Super Admin'");
$stmt->execute();
$role_id = $stmt->fetchColumn();

if (!$role_id) {
    die("Error: 'Super Admin' role not found in database. Run schema.sql first.");
}

// 2. Check if Admin Exists
$email = 'admin@smartcity.com';
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    echo "Super Admin already exists.<br>";
} else {
    // 3. Create Super Admin
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $name = 'System Administrator';
    
    $sql = "INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$name, $email, $password, $role_id]);
    
    echo "Success: Created Super Admin user.<br>";
    echo "<strong>Email:</strong> admin@smartcity.com<br>";
    echo "<strong>Password:</strong> admin123<br>";
}

echo "<br><a href='pages/auth/login.php'>Go to Login</a>";
?>
