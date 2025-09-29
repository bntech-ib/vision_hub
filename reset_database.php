<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop the database
    $pdo->exec("DROP DATABASE IF EXISTS visionhub");
    echo "Database dropped successfully\n";
    
    // Create the database
    $pdo->exec("CREATE DATABASE visionhub");
    echo "Database created successfully\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}