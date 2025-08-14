<?php
/**
 * Installation Script for Multi-Server Control Panel
 * Run this script to set up the database and initial configuration
 */

// Load CodeIgniter
require_once 'vendor/autoload.php';

use CodeIgniter\Config\Services;
use CodeIgniter\Database\BaseConnection;

echo "=== Multi-Server Control Panel Installation ===\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    
    $db = Services::database();
    $db->connect();
    
    if ($db->isConnected()) {
        echo "   ✓ Database connection successful\n";
    } else {
        throw new Exception("Database connection failed");
    }
    
    // Run migrations
    echo "\n2. Running database migrations...\n";
    
    $migrate = Services::migrations();
    $migrate->setNamespace('App');
    
    try {
        $migrate->latest();
        echo "   ✓ Database migrations completed successfully\n";
    } catch (Exception $e) {
        echo "   ⚠ Migration warning: " . $e->getMessage() . "\n";
        echo "   Continuing with installation...\n";
    }
    
    // Run seeders
    echo "\n3. Running database seeders...\n";
    
    $seeder = Services::seeder();
    $seeder->setNamespace('App');
    
    try {
        $seeder->call('UserSeeder');
        echo "   ✓ Default users created successfully\n";
    } catch (Exception $e) {
        echo "   ⚠ Seeder warning: " . $e->getMessage() . "\n";
    }
    
    // Create necessary directories
    echo "\n4. Creating necessary directories...\n";
    
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
    echo "\n5. Generating RSA keys for control panel...\n";
    
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
    echo "\n6. Setting file permissions...\n";
    
    chmod($privateKeyPath, 0600);
    chmod($publicKeyPath, 0644);
    chmod('keys', 0755);
    
    echo "   ✓ File permissions set correctly\n";
    
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
    echo "1. Database connection settings in .env file\n";
    echo "2. Database server is running\n";
    echo "3. User has sufficient privileges\n";
    echo "4. PHP extensions: openssl, pdo_mysql\n";
    exit(1);
} 