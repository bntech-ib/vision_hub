<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub_test', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "Users table exists in visionhub_test database\n";
    } else {
        echo "Users table does not exist in visionhub_test database\n";
    }
    
    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in visionhub_test database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
} catch (PDOException $e) {
    echo "Error connecting to test database: " . $e->getMessage() . "\n";
}