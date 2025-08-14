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
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL to your CodeIgniter root. Typically, this will be your base URL,
     * WITH a trailing slash:
     *
     * E.g., http://example.com/
     */
    public string $baseURL = '';

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Typically, this will be your `index.php` file, unless you've renamed it to
     * something else. If you have configured your web server to remove this file
     * from your site URIs, set this variable to an empty string.
     */
    public string $indexPage = 'index.php';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     *
     * This item determines which server global should be used to retrieve the
     * URI string. The default setting of 'REQUEST_URI' works for most servers.
     * If your links do not seem to work, try one of the other delicious flavors:
     *
     *  'REQUEST_URI': Uses $_SERVER['REQUEST_URI']
     * 'QUERY_STRING': Uses $_SERVER['QUERY_STRING']
     *    'PATH_INFO': Uses $_SERVER['PATH_INFO']
     *
     * WARNING: If you set this to 'PATH_INFO', URIs will always be URL-decoded!
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * --------------------------------------------------------------------------
     * Default Timezone
     * --------------------------------------------------------------------------
     *
     * This is the default timezone that will be used when displaying or
     * converting dates and times. This should match the timezone set in
     * your server configuration.
     */
    public string $appTimezone = 'UTC';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     *
     * This determines the default locale that will be used by the
     * Request class to ensure that the language, numbers, dates, etc.
     * are correctly formatted.
     */
    public string $defaultLocale = 'en';

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
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * Contains all languages your application supports. This can be used
     * by the `negotiateLocale()` method to pick the best locale.
     *
     * @var list<string>
     */
    public array $supportedLocales = ['en'];

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     *
     * This determines the default character set used in various methods
     * that require a character set to be provided.
     */
    public string $charset = 'UTF-8';

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
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * If your server is behind a reverse proxy, you must whitelist the proxy
     * IP addresses from which CodeIgniter should trust headers such as
     * X-Forwarded-For or Client-IP in order to properly identify
     * the visitor's IP address.
     *
     * You need to set a proxy IP address or IP address with subnets and
     * the HTTP header for the client IP address.
     *
     * Here are some examples:
     *     [
     *         '10.0.1.200'     => 'X-Forwarded-For',
     *         '192.168.5.0/24' => 'X-Real-IP',
     *     ]
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     *
     * Enables the Response's Content Secure Policy to restrict the sources that
     * can be used for images, scripts, CSS files, audio, video, etc. If enabled,
     * the Response object will populate default values for the policy from the
     * `ContentSecurityPolicy.php` file. Controllers can always add to those
     * restrictions at run time.
     *
     * For a better understanding of CSP, see these documents:
     *
     * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
     * @see http://www.w3.org/TR/CSP/
     */
    public bool $CSPEnabled = false;

    /**
     * --------------------------------------------------------------------------
     * Allowed URL Characters
     * --------------------------------------------------------------------------
     *
     * This lets you specify which characters are permitted within your URLs.
     * When someone tries to submit a URL with disallowed characters they will
     * get a warning message.
     *
     * As a security measure you are STRONGLY encouraged to restrict URLs to
     * as few characters as possible.
     *
     * By default, only these are allowed: `a-z 0-9~%.:_-`
     *
     * Set an empty string to allow all characters -- but only if you are insane.
     *
     * The configured value is actually a regular expression character group
     * and it will be used as: '/\A[<permittedURIChars>]+\z/iu'
     *
     * DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * RSA Keys Configuration
     * --------------------------------------------------------------------------
     */
    public string $rsaPrivateKeyPath = 'keys/private_key.pem';
    public string $rsaPublicKeyPath = 'keys/public_key.pem';

    /**
     * --------------------------------------------------------------------------
     * Server Agent Configuration
     * --------------------------------------------------------------------------
     */
    public int $agentDefaultPort = 6969;
    public int $agentTimeout = 30;
    public int $agentMaxRetries = 3;

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
                    
                    // Set baseURL if specified
                    if ($key === 'app.baseURL') {
                        $this->baseURL = $value;
                    }
                    
                    // Set indexPage if specified
                    if ($key === 'app.indexPage') {
                        $this->indexPage = $value;
                    }
                    
                    // Set uriProtocol if specified
                    if ($key === 'app.uriProtocol') {
                        $this->uriProtocol = $value;
                    }
                    
                    // Set appTimezone if specified
                    if ($key === 'app.appTimezone') {
                        $this->appTimezone = $value;
                    }
                    
                    // Set defaultLocale if specified
                    if ($key === 'app.defaultLocale') {
                        $this->defaultLocale = $value;
                    }
                    
                    // Set negotiateLocale if specified
                    if ($key === 'app.negotiateLocale') {
                        $this->negotiateLocale = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    
                    // Set supportedLocales if specified
                    if ($key === 'app.supportedLocales') {
                        $this->supportedLocales = explode(',', str_replace(['[', ']', "'", '"'], '', $value));
                    }
                    
                    // Set charset if specified
                    if ($key === 'app.charset') {
                        $this->charset = $value;
                    }
                    
                    // Set forceGlobalSecureRequests if specified
                    if ($key === 'app.forceGlobalSecureRequests') {
                        $this->forceGlobalSecureRequests = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                    
                    // Set permittedURIChars if specified
                    if ($key === 'app.permittedURIChars') {
                        $this->permittedURIChars = $value;
                    }
                    
                    // Set CSPEnabled if specified
                    if ($key === 'app.CSPEnabled') {
                        $this->CSPEnabled = filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
    public function getRsaPrivateKeyPath(): string
    {
        return $this->rsaPrivateKeyPath;
    }

    /**
     * Get RSA public key path
     */
    public function getRsaPublicKeyPath(): string
    {
        return $this->rsaPublicKeyPath;
    }

    /**
     * Get agent default port
     */
    public function getAgentDefaultPort(): int
    {
        return $this->agentDefaultPort;
    }

    /**
     * Get agent timeout
     */
    public function getAgentTimeout(): int
    {
        return $this->agentTimeout;
    }

    /**
     * Get agent max retries
     */
    public function getAgentMaxRetries(): int
    {
        return $this->agentMaxRetries;
    }
} 