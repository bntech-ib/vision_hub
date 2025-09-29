<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read the SQL file
    $sql = file_get_contents('discard_tablespace.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    echo "SQL executed successfully\n";
    
} catch (PDOException $e) {
    echo "Error executing SQL: " . $e->getMessage() . "\n";
}