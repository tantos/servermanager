<?php
/**
 * Simple Installation Script for Multi-Server Control Panel
 * Sets up database and tables manually using MySQLi
 */

echo "=== Multi-Server Control Panel Installation ===\n\n";

// Database connection parameters
$host = 'localhost';
$port = 33066;
$username = 'simrs';
$password = 'bismilah';
$database = 'server_manager';

try {
    // Connect to database using MySQLi
    echo "1. Testing database connection...\n";
    
    $mysqli = new mysqli($host, $username, $password, $database, $port);
    
    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    // Set charset
    $mysqli->set_charset("utf8mb4");
    
    echo "   ✓ Database connection successful\n";
    
    // Drop existing tables if they exist (clean install)
    echo "\n2. Preparing database...\n";
    
    $tables = ['command_history', 'server_sites', 'server_keys', 'servers', 'users'];
    
    foreach ($tables as $table) {
        $mysqli->query("DROP TABLE IF EXISTS `$table`");
    }
    
    echo "   ✓ Database prepared for clean installation\n";
    
    // Create tables
    echo "\n3. Creating database tables...\n";
    
    // Users table
    $sql = "CREATE TABLE `users` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `username` varchar(100) NOT NULL,
        `email` varchar(255) NOT NULL,
        `password_hash` varchar(255) NOT NULL,
        `full_name` varchar(255) NOT NULL,
        `role` enum('admin','user','viewer') NOT NULL DEFAULT 'user',
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `last_login` datetime NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$mysqli->query($sql)) {
        throw new Exception("Failed to create users table: " . $mysqli->error);
    }
    echo "   ✓ Users table created\n";
    
    // Add unique constraints after table creation
    $mysqli->query("ALTER TABLE `users` ADD UNIQUE KEY `username` (`username`)");
    $mysqli->query("ALTER TABLE `users` ADD UNIQUE KEY `email` (`email`)");
    
    // Servers table
    $sql = "CREATE TABLE `servers` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `hostname` varchar(255) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `port` int(5) NOT NULL DEFAULT 6969,
        `description` text NULL,
        `os_info` varchar(255) NULL,
        `status` enum('online','offline','maintenance') NOT NULL DEFAULT 'offline',
        `last_seen` datetime NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$mysqli->query($sql)) {
        throw new Exception("Failed to create servers table: " . $mysqli->error);
    }
    echo "   ✓ Servers table created\n";
    
    // Add indexes after table creation
    $mysqli->query("ALTER TABLE `servers` ADD KEY `hostname` (`hostname`)");
    $mysqli->query("ALTER TABLE `servers` ADD KEY `ip_address` (`ip_address`)");
    $mysqli->query("ALTER TABLE `servers` ADD KEY `status` (`status`)");
    
    // Server keys table
    $sql = "CREATE TABLE `server_keys` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `server_id` int(11) unsigned NOT NULL,
        `key_name` varchar(255) NOT NULL,
        `public_key` text NOT NULL,
        `key_type` enum('rsa','ed25519') NOT NULL DEFAULT 'rsa',
        `key_size` int(5) NOT NULL DEFAULT 2048,
        `fingerprint` varchar(64) NOT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `server_id` (`server_id`),
        KEY `fingerprint` (`fingerprint`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$mysqli->query($sql)) {
        throw new Exception("Failed to create server_keys table: " . $mysqli->error);
    }
    echo "   ✓ Server keys table created\n";
    
    // Server sites table
    $sql = "CREATE TABLE `server_sites` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `server_id` int(11) unsigned NOT NULL,
        `site_name` varchar(255) NOT NULL,
        `document_root` varchar(500) NOT NULL,
        `server_name` varchar(255) NOT NULL,
        `server_alias` text NULL,
        `php_version` varchar(10) NULL,
        `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
        `ssl_enabled` tinyint(1) NOT NULL DEFAULT 0,
        `ssl_cert_path` varchar(500) NULL,
        `ssl_key_path` varchar(500) NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `server_id` (`server_id`),
        KEY `site_name` (`site_name`),
        KEY `is_enabled` (`is_enabled`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$mysqli->query($sql)) {
        throw new Exception("Failed to create server_sites table: " . $mysqli->error);
    }
    echo "   ✓ Server sites table created\n";
    
    // Command history table
    $sql = "CREATE TABLE `command_history` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `server_id` int(11) unsigned NOT NULL,
        `user_id` int(11) unsigned NOT NULL,
        `command` text NOT NULL,
        `output` longtext NULL,
        `error` text NULL,
        `exit_code` int(11) NOT NULL DEFAULT 0,
        `execution_time` float NULL,
        `status` enum('success','failed','timeout') NOT NULL DEFAULT 'success',
        `ip_address` varchar(45) NULL,
        `user_agent` text NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `server_id` (`server_id`),
        KEY `user_id` (`user_id`),
        KEY `status` (`status`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$mysqli->query($sql)) {
        throw new Exception("Failed to create command_history table: " . $mysqli->error);
    }
    echo "   ✓ Command history table created\n";
    
    // Insert default users
    echo "\n4. Creating default users...\n";
    
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, full_name, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $mysqli->error);
    }
    
    $stmt->bind_param("sssssi", $username, $email, $password_hash, $full_name, $role, $is_active);
    
    // Admin user
    $username = 'admin';
    $email = 'admin@server-manager.local';
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $full_name = 'System Administrator';
    $role = 'admin';
    $is_active = 1;
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create admin user: " . $stmt->error);
    }
    echo "   ✓ Admin user created\n";
    
    // Regular user
    $username = 'user';
    $email = 'user@server-manager.local';
    $password_hash = password_hash('user123', PASSWORD_DEFAULT);
    $full_name = 'Regular User';
    $role = 'user';
    $is_active = 1;
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create regular user: " . $stmt->error);
    }
    echo "   ✓ Regular user created\n";
    
    $stmt->close();
    
    // Create necessary directories
    echo "\n5. Creating necessary directories...\n";
    
    $dirs = [
        'writable/logs',
        'writable/cache',
        'writable/sessions',
        'writable/uploads',
        'keys'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "   ✓ Created directory: {$dir}\n";
        } else {
            echo "   ✓ Directory already exists: {$dir}\n";
        }
    }
    
    // Generate RSA keys for control panel
    echo "\n6. Generating RSA keys for control panel...\n";
    
    $privateKeyPath = 'keys/private_key.pem';
    $publicKeyPath = 'keys/public_key.pem';
    
    if (!file_exists($privateKeyPath)) {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res)['key'];
        
        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $publicKey);
        
        echo "   ✓ RSA keys generated successfully\n";
        echo "   Private key: {$privateKeyPath}\n";
        echo "   Public key: {$publicKeyPath}\n";
    } else {
        echo "   ✓ RSA keys already exist\n";
    }
    
    // Set proper permissions
    echo "\n7. Setting file permissions...\n";
    
    chmod($privateKeyPath, 0600);
    chmod($publicKeyPath, 0644);
    chmod('keys', 0755);
    
    echo "   ✓ File permissions set correctly\n";
    
    // Close database connection
    $mysqli->close();
    
    // Installation complete
    echo "\n=== Installation Complete! ===\n\n";
    
    echo "Default login credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    
    echo "Username: user\n";
    echo "Password: user123\n\n";
    
    echo "Next steps:\n";
    echo "1. Start your web server\n";
    echo "2. Navigate to the control panel\n";
    echo "3. Log in with admin credentials\n";
    echo "4. Add your first server in Settings\n";
    echo "5. Install the server agent on your Ubuntu servers\n\n";
    
    echo "Important security notes:\n";
    echo "- Change default passwords after first login\n";
    echo "- Keep RSA keys secure and private\n";
    echo "- Use VPN for server-agent communication\n";
    echo "- Regularly backup your database\n\n";
    
} catch (Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. Database connection settings\n";
    echo "2. Database server is running\n";
    echo "3. User has sufficient privileges\n";
    echo "4. PHP extensions: openssl, mysqli\n";
    
    // Close connection if it exists
    if (isset($mysqli)) {
        $mysqli->close();
    }
    
    exit(1);
} 