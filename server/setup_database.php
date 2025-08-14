<?php
/**
 * Database Setup Script
 * Creates the servermanager database if it doesn't exist
 */

// Load environment variables
$envFile = __DIR__ . '/.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            $envVars[$key] = $value;
        }
    }
}

// Database connection details
$host = $envVars['database.default.hostname'] ?? 'localhost';
$port = $envVars['database.default.port'] ?? 3306;
$username = $envVars['database.default.username'] ?? 'root';
$password = $envVars['database.default.password'] ?? '';
$database = $envVars['database.default.database'] ?? 'servermanager';

echo "=== Database Setup ===\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $username\n";
echo "Database: $database\n\n";

// Connect to MySQL server (without specifying database)
$mysqli = new mysqli($host, $username, $password, '', $port);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to MySQL server successfully\n";

// Check if database exists
$result = $mysqli->query("SHOW DATABASES LIKE '$database'");

if ($result->num_rows > 0) {
    echo "✅ Database '$database' already exists\n";
} else {
    // Create database
    $sql = "CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    
    if ($mysqli->query($sql)) {
        echo "✅ Database '$database' created successfully\n";
    } else {
        die("❌ Error creating database: " . $mysqli->error . "\n");
    }
}

$mysqli->close();

echo "\n✅ Database setup completed successfully!\n";
echo "You can now run the installation script.\n"; 