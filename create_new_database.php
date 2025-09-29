<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create a new database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS visionhub_new");
    echo "New database 'visionhub_new' created successfully\n";
    
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
}