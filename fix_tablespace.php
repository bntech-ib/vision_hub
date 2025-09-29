<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Discard the tablespace
    $pdo->exec("ALTER TABLE migrations DISCARD TABLESPACE");
    echo "Tablespace discarded successfully\n";
    
} catch (PDOException $e) {
    echo "Error discarding tablespace: " . $e->getMessage() . "\n";
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop the migrations table
    $pdo->exec("DROP TABLE IF EXISTS migrations");
    echo "Migrations table dropped successfully\n";
    
} catch (PDOException $e) {
    echo "Error dropping table: " . $e->getMessage() . "\n";
}