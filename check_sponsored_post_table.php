<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub_test', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if sponsored_post_image table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'sponsored_post_image'");
    if ($stmt->rowCount() > 0) {
        echo "sponsored_post_image table exists in visionhub_test database\n";
    } else {
        echo "sponsored_post_image table does not exist in visionhub_test database\n";
    }
    
    // List all tables with 'sponsored' in the name
    $stmt = $pdo->query("SHOW TABLES LIKE '%sponsored%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables with 'sponsored' in name:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
} catch (PDOException $e) {
    echo "Error connecting to test database: " . $e->getMessage() . "\n";
}