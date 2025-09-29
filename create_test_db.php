<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create test database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS visionhub_test");
    echo "Test database 'visionhub_test' created successfully\n";
    
} catch (PDOException $e) {
    echo "Error creating test database: " . $e->getMessage() . "\n";
}