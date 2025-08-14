<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;

class ServerAgent
{
    protected $curl;
    protected $privateKey;
    protected $publicKey;

    public function __construct()
    {
        $this->curl = \Config\Services::curlrequest();
    }

    /**
     * Load RSA private key for signing requests
     */
    public function loadPrivateKey($privateKeyPath)
    {
        if (!file_exists($privateKeyPath)) {
            throw new \Exception("Private key not found: {$privateKeyPath}");
        }

        $this->privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
        if (!$this->privateKey) {
            throw new \Exception("Failed to load private key");
        }
    }

    /**
     * Sign data with RSA private key
     */
    protected function signData($data)
    {
        if (!$this->privateKey) {
            throw new \Exception("Private key not loaded");
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception("Failed to sign data");
        }

        return base64_encode($signature);
    }

    /**
     * Make authenticated request to server agent
     */
    protected function makeRequest($server, $endpoint, $method = 'GET', $data = null)
    {
        $url = "http://{$server['ip_address']}:{$server['port']}{$endpoint}";
        $timestamp = time();
        
        // Prepare request data
        $requestData = '';
        if ($data) {
            $requestData = json_encode($data);
        }

        // Sign the request data
        $signature = $this->signData($requestData);

        // Set headers
        $headers = [
            'X-Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $this->curl->request($method, $url, [
                'headers' => $headers,
                'json' => $data,
                'timeout' => 30,
            ]);

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'server' => $server['name'],
                'endpoint' => $endpoint,
            ];
        }
    }

    /**
     * Parse response from server agent
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $body = $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response',
                'raw_response' => $body,
            ];
        }

        return $data;
    }

    /**
     * Get server information
     */
    public function getServerInfo($server)
    {
        return $this->makeRequest($server, '/health');
    }

    /**
     * Get services status
     */
    public function getServicesStatus($server)
    {
        return $this->makeRequest($server, '/api/services/status');
    }

    /**
     * Manage service (start/stop/restart)
     */
    public function manageService($server, $serviceName, $action)
    {
        return $this->makeRequest($server, "/api/services/{$serviceName}/{$action}", 'POST');
    }

    /**
     * Get Apache2 sites
     */
    public function getApache2Sites($server)
    {
        return $this->makeRequest($server, '/api/apache2/sites');
    }

    /**
     * Manage Apache2 site (enable/disable)
     */
    public function manageApache2Site($server, $siteName, $action)
    {
        return $this->makeRequest($server, "/api/apache2/sites/{$siteName}/{$action}", 'POST');
    }

    /**
     * Get Apache2 modules
     */
    public function getApache2Modules($server)
    {
        return $this->makeRequest($server, '/api/apache2/modules');
    }

    /**
     * Manage Apache2 module (enable/disable)
     */
    public function manageApache2Module($server, $moduleName, $action)
    {
        return $this->makeRequest($server, "/api/apache2/modules/{$moduleName}/{$action}", 'POST');
    }

    /**
     * Get Apache2 configuration
     */
    public function getApache2Config($server)
    {
        return $this->makeRequest($server, '/api/config/apache2');
    }

    /**
     * Update Apache2 configuration
     */
    public function updateApache2Config($server, $content)
    {
        return $this->makeRequest($server, '/api/config/apache2', 'POST', ['content' => $content]);
    }

    /**
     * Get PHP information for specific version
     */
    public function getPHPInfo($server, $version = null)
    {
        if ($version) {
            return $this->makeRequest($server, "/api/php/{$version}/info");
        }

        // Get info for all supported versions
        $versions = ['7.4', '8.4'];
        $results = [];

        foreach ($versions as $ver) {
            $results[$ver] = $this->makeRequest($server, "/api/php/{$ver}/info");
        }

        return $results;
    }

    /**
     * Get system information
     */
    public function getSystemInfo($server)
    {
        return $this->makeRequest($server, '/api/system/info');
    }

    /**
     * Execute terminal command
     */
    public function executeCommand($server, $command, $timeout = 30)
    {
        return $this->makeRequest($server, '/api/terminal/execute', 'POST', [
            'command' => $command,
            'timeout' => $timeout,
        ]);
    }

    /**
     * Generate new RSA key pair on server
     */
    public function generateKeys($server)
    {
        return $this->makeRequest($server, '/api/keys/generate', 'POST');
    }

    /**
     * Test server connectivity
     */
    public function testConnection($server)
    {
        try {
            $response = $this->makeRequest($server, '/health');
            return [
                'success' => $response['success'] ?? false,
                'status' => $response['status'] ?? 'unknown',
                'response_time' => $response['timestamp'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get server status summary
     */
    public function getServerStatus($server)
    {
        $status = [
            'server' => $server,
            'health' => $this->getServerInfo($server),
            'services' => $this->getServicesStatus($server),
            'system' => $this->getSystemInfo($server),
        ];

        // Determine overall status
        $status['overall_status'] = 'offline';
        if ($status['health']['success'] && $status['health']['status'] === 'healthy') {
            $status['overall_status'] = 'online';
        }

        return $status;
    }

    /**
     * Batch operation on multiple servers
     */
    public function batchOperation($servers, $operation, $params = [])
    {
        $results = [];
        
        foreach ($servers as $server) {
            try {
                switch ($operation) {
                    case 'get_status':
                        $results[$server['id']] = $this->getServerStatus($server);
                        break;
                    case 'get_services':
                        $results[$server['id']] = $this->getServicesStatus($server);
                        break;
                    case 'restart_service':
                        $serviceName = $params['service_name'] ?? 'apache2';
                        $results[$server['id']] = $this->manageService($server, $serviceName, 'restart');
                        break;
                    default:
                        $results[$server['id']] = [
                            'success' => false,
                            'error' => "Unknown operation: {$operation}",
                        ];
                }
            } catch (\Exception $e) {
                $results[$server['id']] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
} 