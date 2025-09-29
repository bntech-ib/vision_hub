<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "Connected to MySQL successfully\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS visionhub");
    echo "Database 'visionhub' created or already exists\n";
    
    // Select the database
    $pdo->exec("USE visionhub");
    
    // Check if migrations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
    if ($stmt->rowCount() == 0) {
        echo "Migrations table does not exist\n";
    } else {
        echo "Migrations table exists\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}