<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=visionhub', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the migrations table manually
    $sql = "CREATE TABLE `migrations` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `batch` int NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Migrations table created successfully\n";
    
} catch (PDOException $e) {
    echo "Error creating migrations table: " . $e->getMessage() . "\n";
}