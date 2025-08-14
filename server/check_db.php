<?php
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

// Load configuration
$env = loadEnv('.env');

// Database connection parameters
$host = $env['database.default.hostname'] ?? 'localhost';
$port = (int)($env['database.default.port'] ?? 3306);
$username = $env['database.default.username'] ?? 'root';
$password = $env['database.default.password'] ?? '';
$database = $env['database.default.database'] ?? 'servermanager';

try {
    $mysqli = new mysqli($host, $username, $password, $database, $port);
    
    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "Database connection successful!\n\n";
    
    // Check existing tables
    $result = $mysqli->query("SHOW TABLES");
    $tables = [];
    
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    if (empty($tables)) {
        echo "No tables found in database.\n";
    } else {
        echo "Existing tables:\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 