<?php
/**
 * Database Setup Script
 * Creates the database if it doesn't exist
 */

echo "=== Database Setup ===\n\n";

// Database connection parameters
$host = 'localhost';
$port = 33066;
$username = 'simrs';
$password = 'bismilah';
$database = 'server_manager';

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server successfully\n";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        echo "✓ Database '$database' already exists\n";
    } else {
        // Create database
        $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "✓ Database '$database' created successfully\n";
    }
    
    // Test connection to the specific database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Successfully connected to database '$database'\n";
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now run: php install.php\n";
    
} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL server is running\n";
    echo "2. User '$username' has sufficient privileges\n";
    echo "3. Port $port is correct\n";
    echo "4. Password is correct\n";
    exit(1);
} 