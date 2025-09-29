<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Create a new Capsule instance
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'visionhub',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

try {
    // Test the connection
    $pdo = $capsule->getConnection()->getPdo();
    echo "Connected successfully\n";
    
    // Check if migrations table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'migrations'")->fetchAll();
    if (count($tables) > 0) {
        echo "Migrations table exists\n";
        
        // Check contents of migrations table
        $stmt = $pdo->query("SELECT * FROM migrations");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Migrations table has " . count($rows) . " rows\n";
        
        foreach ($rows as $row) {
            echo "- " . $row['migration'] . " (batch: " . $row['batch'] . ")\n";
        }
    } else {
        echo "Migrations table does not exist\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}