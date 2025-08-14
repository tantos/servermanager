<?php
/**
 * Database Setup Script
 * Creates the servermanager database if it doesn't exist
 * Configuration is read from .env file
 */

// Load environment variables from .env file
function loadEnv($file) {
    if (!file_exists($file)) {
        echo "Error: .env file not found. Please create it first.\n";
        exit(1);
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

echo "=== Database Setup ===\n\n";

// Load configuration
$env = loadEnv('.env');

// Database connection parameters
$host = $env['database.default.hostname'] ?? 'localhost';
$port = (int)($env['database.default.port'] ?? 3306);
$username = $env['database.default.username'] ?? 'root';
$password = $env['database.default.password'] ?? '';
$database = $env['database.default.database'] ?? 'servermanager';

echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $username\n";
echo "Database: $database\n\n";

try {
    // Connect to MySQL without selecting a database
    $mysqli = new mysqli($host, $username, $password, '', $port);
    
    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "✓ Connected to MySQL server successfully\n";
    
    // Check if database exists
    $result = $mysqli->query("SHOW DATABASES LIKE '$database'");
    $dbExists = $result->num_rows > 0;
    
    if ($dbExists) {
        echo "✓ Database '$database' already exists\n";
    } else {
        // Create database
        $mysqli->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "✓ Database '$database' created successfully\n";
    }
    
    // Test connection to the specific database
    $mysqli->close();
    $mysqli = new mysqli($host, $username, $password, $database, $port);
    
    if ($mysqli->connect_error) {
        throw new Exception("Failed to connect to database '$database': " . $mysqli->connect_error);
    }
    
    echo "✓ Successfully connected to database '$database'\n";
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now run: php install_simple.php\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. .env file exists and is properly configured\n";
    echo "2. MySQL server is running\n";
    echo "3. User '$username' has sufficient privileges\n";
    echo "4. Port $port is correct\n";
    echo "5. Password is correct\n";
    exit(1);
} 