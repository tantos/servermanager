<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     */
    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'ci4',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => (ENVIRONMENT !== 'production'),
        'charset'  => 'utf8',
        'DBCollate' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'      => '',
        'hostname' => '127.0.0.1',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 'db_',  // Needed to ensure we can work in Postgres
        'pConnect' => false,
        'DBDebug'  => (ENVIRONMENT !== 'production'),
        'charset'  => 'utf8',
        'DBCollate' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Load environment variables
        $this->loadEnvironmentVariables();
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnvironmentVariables()
    {
        $envFile = ROOTPATH . '.env';
        
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
                    
                    // Set database configuration
                    if ($key === 'database.default.hostname') {
                        $this->default['hostname'] = $value;
                    } elseif ($key === 'database.default.database') {
                        $this->default['database'] = $value;
                    } elseif ($key === 'database.default.username') {
                        $this->default['username'] = $value;
                    } elseif ($key === 'database.default.password') {
                        $this->default['password'] = $value;
                    } elseif ($key === 'database.default.DBDriver') {
                        $this->default['DBDriver'] = $value;
                    } elseif ($key === 'database.default.DBPrefix') {
                        $this->default['DBPrefix'] = $value;
                    } elseif ($key === 'database.default.port') {
                        $this->default['port'] = (int)$value;
                    } elseif ($key === 'database.default.charset') {
                        $this->default['charset'] = $value;
                    } elseif ($key === 'database.default.DBCollat') {
                        $this->default['DBCollate'] = $value;
                    }
                    
                    // Set test database configuration
                    if ($key === 'database.tests.hostname') {
                        $this->tests['hostname'] = $value;
                    } elseif ($key === 'database.tests.database') {
                        $this->tests['database'] = $value;
                    } elseif ($key === 'database.tests.username') {
                        $this->tests['username'] = $value;
                    } elseif ($key === 'database.tests.password') {
                        $this->tests['password'] = $value;
                    } elseif ($key === 'database.tests.DBDriver') {
                        $this->tests['DBDriver'] = $value;
                    } elseif ($key === 'database.tests.DBPrefix') {
                        $this->tests['DBPrefix'] = $value;
                    } elseif ($key === 'database.tests.port') {
                        $this->tests['port'] = (int)$value;
                    } elseif ($key === 'database.tests.charset') {
                        $this->tests['charset'] = $value;
                    } elseif ($key === 'database.tests.DBCollat') {
                        $this->tests['DBCollate'] = $value;
                    }
                }
            }
        }
    }

    /**
     * Get database configuration
     */
    public function getDefaultConfig(): array
    {
        return $this->default;
    }

    /**
     * Get test database configuration
     */
    public function getTestConfig(): array
    {
        return $this->tests;
    }
} 