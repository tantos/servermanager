<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

/**
 * Logging Configuration
 */
class Logger extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Logging Threshold
     * --------------------------------------------------------------------------
     *
     * You can enable error logging by setting a threshold over zero. The
     * threshold determines what gets logged. Threshold options are:
     *
     * 0 = Disables logging, Error logging TURNED OFF
     * 1 = Emergency Messages - System is unusable
     * 2 = Alert Messages - Action must be taken immediately
     * 3 = Critical Messages - Application component unavailable, unexpected exception.
     * 4 = Runtime Errors - Don't need immediate action, but should be monitored.
     * 5 = Warnings - Exceptional occurrences that are not errors.
     * 6 = Notices - Normal but significant events.
     * 7 = Info - Interesting events.
     * 8 = Debug - Detailed debug information.
     * 9 = All Messages
     *
     * You can also pass an array with threshold levels to show individual error types
     *
     *     array(1, 2, 3, 8) = Emergency, Alert, Critical, and Debug messages
     *
     * For a live site you'll usually only enable Errors (1) to be logged otherwise
     * your log files will fill up very fast.
     *
     * @var int|array
     */
    public $threshold = 4;

    /**
     * --------------------------------------------------------------------------
     * Error Logging Directory Path
     * --------------------------------------------------------------------------
     *
     * By default, error logs are stored in WRITEPATH . 'logs/'
     * Specify a different destination here, if desired.
     *
     * @var string
     */
    public string $logPath = WRITEPATH . 'logs/';

    /**
     * --------------------------------------------------------------------------
     * Log File Extension
     * --------------------------------------------------------------------------
     *
     * The default filename extension for log files.
     * An extension of '.log' allows for proper viewing of the log file.
     *
     * @var string
     */
    public string $fileExtension = 'log';

    /**
     * --------------------------------------------------------------------------
     * Log File Permissions
     * --------------------------------------------------------------------------
     *
     * The file system permissions to be applied on newly created log files.
     *
     * IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
     * integer notation (i.e. 0700, 0644, etc.)
     *
     * @var int
     */
    public int $filePermissions = 0644;

    /**
     * --------------------------------------------------------------------------
     * Log Format
     * --------------------------------------------------------------------------
     *
     * Each item that is logged has an associated date. You can use PHP date
     * codes to set your own date formatting
     *
     * @var string
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * --------------------------------------------------------------------------
     * Log Handlers
     * --------------------------------------------------------------------------
     *
     * The logging system supports multiple actions to be taken when something
     * is logged. This is done by allowing for multiple Handlers, special classes
     * designed to write the log to their chosen destinations, whether that is
     * a file on the getServer, a cloud-based service, or even taking actions such
     * as emailing the admins to notify them of that something went wrong.
     *
     * Each handler must have the same method signature as the others.
     *
     * @var array
     */
    public array $handlers = [];

    /**
     * --------------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------------
     */
    public function __construct()
    {
        parent::__construct();
        
        // Load environment variables
        $this->loadEnvironmentVariables();
        
        // Set default handlers
        $this->setDefaultHandlers();
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
                    
                    // Set logger configuration
                    if ($key === 'logger.threshold') {
                        $this->threshold = (int)$value;
                    } elseif ($key === 'logger.logPath') {
                        $this->logPath = $value;
                    } elseif ($key === 'logger.fileExtension') {
                        $this->fileExtension = $value;
                    }
                }
            }
        }
    }

    /**
     * Set default handlers
     */
    private function setDefaultHandlers()
    {
        $this->handlers = [
            // File Handler
            FileHandler::class => [
                'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
                'path' => $this->logPath,
                'level' => $this->threshold,
                'fileExtension' => $this->fileExtension,
                'filePermissions' => $this->filePermissions,
                'dateFormat' => $this->dateFormat,
            ],
        ];
    }

    /**
     * Get logger threshold
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Get log path
     */
    public function getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * Get file extension
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }
} 