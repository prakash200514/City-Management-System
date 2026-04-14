<?php
// setup_db.php

$host = 'localhost';
$username = 'root';
$password = 'password';

echo "Connecting to MySQL server...<br>";

try {
    // Connect without database selected
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read schema file
    echo "Reading schema.sql...<br>";
    $sql = file_get_contents('database/schema.sql');

    if ($sql) {
        // Execute schema
        echo "Executing schema...<br>";
        $pdo->exec($sql);
        echo "Database 'smart_city' and tables created successfully.<br>";
        
        // Connect to the specific database to insert admin user separately if needed, 
        // but schema.sql handles structure. 
        // Let's Insert the Super Admin with a secure hash.
        $pdo->exec("USE smart_city");
        
        // Admin user details
        $admin_email = 'admin@smartcity.com';
        $admin_pass = 'admin123';
        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$admin_email]);
        
        if ($stmt->rowCount() == 0) {
            // Get Super Admin Role ID
            $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'Super Admin'");
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            $role_id = $role['id'];

            // Insert Admin
            $insert = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)");
            $insert->execute(['Super Admin', $admin_email, $hashed_password, $role_id]);
            echo "Super Admin user created (Email: $admin_email, Password: $admin_pass)<br>";
        } else {
            echo "Super Admin user already exists.<br>";
        }

    } else {
        echo "Error: Could not read database/schema.sql";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
