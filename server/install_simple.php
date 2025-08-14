<?php
/**
 * Simple Installation Script for Multi-Server Control Panel
 * Creates all necessary database tables and initial data
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

echo "=== Multi-Server Control Panel Installation ===\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $username\n";
echo "Database: $database\n\n";

// Connect to database
$mysqli = new mysqli($host, $username, $password, $database, $port);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to database successfully\n";

// Drop existing tables for clean install
echo "\n🗑️  Dropping existing tables...\n";
$tables = [
    'command_history',
    'server_sites', 
    'server_keys',
    'servers',
    'users'
];

foreach ($tables as $table) {
    $mysqli->query("DROP TABLE IF EXISTS `$table`");
    echo "  - Dropped table: $table\n";
}

// Create users table
echo "\n📋 Creating users table...\n";
$sql = "CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `role` enum('admin','user') NOT NULL DEFAULT 'user',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `last_login` datetime NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($mysqli->query($sql)) {
    echo "✅ Users table created successfully\n";
} else {
    die("❌ Error creating users table: " . $mysqli->error . "\n");
}

// Create servers table
echo "\n📋 Creating servers table...\n";
$sql = "CREATE TABLE `servers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `hostname` varchar(100) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `port` int(11) NOT NULL DEFAULT 6969,
    `description` text NULL,
    `os_info` varchar(200) NULL,
    `status` enum('online','offline','error') NOT NULL DEFAULT 'offline',
    `last_seen` datetime NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($mysqli->query($sql)) {
    echo "✅ Servers table created successfully\n";
} else {
    die("❌ Error creating servers table: " . $mysqli->error . "\n");
}

// Create server_keys table
echo "\n📋 Creating server_keys table...\n";
$sql = "CREATE TABLE `server_keys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `server_id` int(11) NOT NULL,
    `key_name` varchar(100) NOT NULL,
    `public_key` text NOT NULL,
    `key_type` varchar(20) NOT NULL DEFAULT 'RSA',
    `key_size` int(11) NOT NULL DEFAULT 2048,
    `fingerprint` varchar(64) NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_server_id` (`server_id`),
    KEY `idx_key_name` (`key_name`),
    CONSTRAINT `fk_server_keys_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($mysqli->query($sql)) {
    echo "✅ Server keys table created successfully\n";
} else {
    die("❌ Error creating server keys table: " . $mysqli->error . "\n");
}

// Create server_sites table
echo "\n📋 Creating server_sites table...\n";
$sql = "CREATE TABLE `server_sites` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `server_id` int(11) NOT NULL,
    `site_name` varchar(100) NOT NULL,
    `document_root` varchar(255) NOT NULL,
    `server_name` varchar(200) NOT NULL,
    `server_alias` varchar(200) NULL,
    `php_version` varchar(10) NULL,
    `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `ssl_enabled` tinyint(1) NOT NULL DEFAULT 0,
    `ssl_cert_path` varchar(255) NULL,
    `ssl_key_path` varchar(255) NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_server_id` (`server_id`),
    KEY `idx_site_name` (`site_name`),
    CONSTRAINT `fk_server_sites_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($mysqli->query($sql)) {
    echo "✅ Server sites table created successfully\n";
} else {
    die("❌ Error creating server sites table: " . $mysqli->error . "\n");
}

// Create command_history table
echo "\n📋 Creating command_history table...\n";
$sql = "CREATE TABLE `command_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `server_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `command` text NOT NULL,
    `output` longtext NULL,
    `error` text NULL,
    `exit_code` int(11) NULL,
    `execution_time` decimal(10,3) NULL,
    `status` enum('success','error','timeout') NOT NULL DEFAULT 'success',
    `ip_address` varchar(45) NULL,
    `user_agent` text NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_server_id` (`server_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_command_history_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_command_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($mysqli->query($sql)) {
    echo "✅ Command history table created successfully\n";
} else {
    die("❌ Error creating command history table: " . $mysqli->error . "\n");
}

// Insert default users
echo "\n👥 Inserting default users...\n";
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$userPassword = password_hash('user123', PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");

// Admin user
$adminUsername = 'admin';
$adminEmail = 'admin@example.com';
$adminFullName = 'System Administrator';
$adminRole = 'admin';

$stmt->bind_param("sssss", $adminUsername, $adminEmail, $adminPassword, $adminFullName, $adminRole);
if ($stmt->execute()) {
    echo "✅ Admin user created (username: admin, password: admin123)\n";
} else {
    echo "❌ Error creating admin user: " . $stmt->error . "\n";
}

// Regular user
$userUsername = 'user';
$userEmail = 'user@example.com';
$userFullName = 'Regular User';
$userRole = 'user';

$stmt->bind_param("sssss", $userUsername, $userEmail, $userPassword, $userFullName, $userRole);
if ($stmt->execute()) {
    echo "✅ Regular user created (username: user, password: user123)\n";
} else {
    echo "❌ Error creating regular user: " . $stmt->error . "\n";
}

$stmt->close();

// Create keys directory
echo "\n🔑 Creating keys directory...\n";
$keysDir = __DIR__ . '/keys';
if (!is_dir($keysDir)) {
    if (mkdir($keysDir, 0755, true)) {
        echo "✅ Keys directory created successfully\n";
    } else {
        echo "⚠️  Warning: Could not create keys directory\n";
    }
} else {
    echo "✅ Keys directory already exists\n";
}

$mysqli->close();

echo "\n🎉 Installation completed successfully!\n";
echo "\nDefault login credentials:\n";
echo "  Admin: admin / admin123\n";
echo "  User:  user  / user123\n";
echo "\nNext steps:\n";
echo "1. Generate RSA keys for server communication\n";
echo "2. Start your web server pointing to the 'public' directory\n";
echo "3. Visit your application in the browser\n";
