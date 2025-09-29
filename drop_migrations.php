<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop the migrations table
    $pdo->exec("DROP TABLE IF EXISTS migrations");
    echo "Migrations table dropped successfully\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}