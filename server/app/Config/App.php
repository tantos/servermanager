<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Application Configuration
 */
class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Default Timezone
     * --------------------------------------------------------------------------
     *
     * This is the default timezone that will be used when displaying or
     * converting dates and times. This should match the timezone set in
     * your server configuration.
     */
    public $appTimezone = 'UTC';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     *
     * This determines the default locale that will be used by the
     * Request class to ensure that the language, numbers, dates, etc.
     * are correctly formatted.
     */
    public $defaultLocale = 'en';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     *
     * If true (default), the current Request object will automatically
     * determine the locale to use based on the value of the Accept-Language
     * header.
     *
     * If false, no automatic detection will be performed.
     */
    public $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * Contains all languages your application supports. This can be used
     * by the `negotiateLocale()` method to pick the best locale.
     *
     * @var string[]
     */
    public $supportedLocales = ['en'];

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     *
     * This determines the default character set used in various methods
     * that require a character set to be provided.
     */
    public $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     *
     * If true, this will force every request made to this application to be
     * made via a secure connection (HTTPS). If the incoming request is not
     * secure, the user will be redirected to a secure version of the page
     * and the HTTP Strict Transport Security header will be set.
     */
    public $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     *
     * Enables the Response's Content Secure Policy to restrict the sources that
     * can be used for images, scripts, CSS files, audio, and more. If enabled,
     * the Response object will populate default values for the policy from the
     * `ContentSecurityPolicy.php` file. Passing a non-empty value to `enableCSP()`
     * will override the default values.
     */
    public $CSPEnabled = false;

    /**
     * --------------------------------------------------------------------------
     * RSA Keys Configuration
     * --------------------------------------------------------------------------
     */
    public $rsaPrivateKeyPath;
    public $rsaPublicKeyPath;

    /**
     * --------------------------------------------------------------------------
     * Server Agent Configuration
     * --------------------------------------------------------------------------
     */
    public $agentDefaultPort = 6969;
    public $agentTimeout = 30;
    public $agentMaxRetries = 3;

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
                    
                    // Set RSA key paths
                    if ($key === 'RSA_PRIVATE_KEY_PATH') {
                        $this->rsaPrivateKeyPath = $value;
                    } elseif ($key === 'RSA_PUBLIC_KEY_PATH') {
                        $this->rsaPublicKeyPath = $value;
                    }
                    
                    // Set agent configuration
                    if ($key === 'AGENT_DEFAULT_PORT') {
                        $this->agentDefaultPort = (int)$value;
                    } elseif ($key === 'AGENT_TIMEOUT') {
                        $this->agentTimeout = (int)$value;
                    } elseif ($key === 'AGENT_MAX_RETRIES') {
                        $this->agentMaxRetries = (int)$value;
                    }
                }
            }
        }
    }

    /**
     * Get RSA private key path
     */
    public function getRsaPrivateKeyPath()
    {
        return $this->rsaPrivateKeyPath ?? 'keys/private_key.pem';
    }

    /**
     * Get RSA public key path
     */
    public function getRsaPublicKeyPath()
    {
        return $this->rsaPublicKeyPath ?? 'keys/public_key.pem';
    }

    /**
     * Get agent default port
     */
    public function getAgentDefaultPort()
    {
        return $this->agentDefaultPort;
    }

    /**
     * Get agent timeout
     */
    public function getAgentTimeout()
    {
        return $this->agentTimeout;
    }

    /**
     * Get agent max retries
     */
    public function getAgentMaxRetries()
    {
        return $this->agentMaxRetries;
    }
}
