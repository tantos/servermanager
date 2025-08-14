<?php

namespace App\Models;

use CodeIgniter\Model;

class ServerModel extends Model
{
    protected $table = 'servers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'hostname',
        'ip_address',
        'port',
        'description',
        'os_info',
        'status',
        'last_seen',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'hostname' => 'required|min_length[2]|max_length[100]',
        'ip_address' => 'required|valid_ip',
        'port' => 'required|integer|greater_than[0]|less_than[65536]',
        'description' => 'permit_empty|max_length[500]',
        'os_info' => 'permit_empty|max_length[200]',
        'status' => 'required|in_list[online,offline,error]',
        'is_active' => 'required|in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Server name is required',
            'min_length' => 'Server name must be at least 2 characters long',
            'max_length' => 'Server name cannot exceed 100 characters'
        ],
        'hostname' => [
            'required' => 'Hostname is required',
            'min_length' => 'Hostname must be at least 2 characters long',
            'max_length' => 'Hostname cannot exceed 100 characters'
        ],
        'ip_address' => [
            'required' => 'IP address is required',
            'valid_ip' => 'Please enter a valid IP address'
        ],
        'port' => [
            'required' => 'Port is required',
            'integer' => 'Port must be a number',
            'greater_than' => 'Port must be greater than 0',
            'less_than' => 'Port must be less than 65536'
        ],
        'description' => [
            'max_length' => 'Description cannot exceed 500 characters'
        ],
        'os_info' => [
            'max_length' => 'OS info cannot exceed 200 characters'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be online, offline, or error'
        ],
        'is_active' => [
            'required' => 'Active status is required',
            'in_list' => 'Active status must be 0 or 1'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all active servers
     */
    public function getActiveServers()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get servers by status
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }

    /**
     * Get online servers
     */
    public function getOnlineServers()
    {
        return $this->where('status', 'online')->where('is_active', 1)->findAll();
    }

    /**
     * Get offline servers
     */
    public function getOfflineServers()
    {
        return $this->where('status', 'offline')->where('is_active', 1)->findAll();
    }

    /**
     * Get servers with errors
     */
    public function getErrorServers()
    {
        return $this->where('status', 'error')->where('is_active', 1)->findAll();
    }

    /**
     * Update server status
     */
    public function updateStatus($serverId, $status, $lastSeen = null)
    {
        $data = ['status' => $status];
        
        if ($lastSeen) {
            $data['last_seen'] = $lastSeen;
        } else {
            $data['last_seen'] = date('Y-m-d H:i:s');
        }

        return $this->update($serverId, $data);
    }

    /**
     * Mark server as online
     */
    public function markOnline($serverId)
    {
        return $this->updateStatus($serverId, 'online');
    }

    /**
     * Mark server as offline
     */
    public function markOffline($serverId)
    {
        return $this->updateStatus($serverId, 'offline');
    }

    /**
     * Mark server as error
     */
    public function markError($serverId)
    {
        return $this->updateStatus($serverId, 'error');
    }

    /**
     * Get server by IP address
     */
    public function getByIpAddress($ipAddress)
    {
        return $this->where('ip_address', $ipAddress)->first();
    }

    /**
     * Get server by hostname
     */
    public function getByHostname($hostname)
    {
        return $this->where('hostname', $hostname)->first();
    }

    /**
     * Check if server exists by IP
     */
    public function serverExistsByIp($ipAddress, $excludeId = null)
    {
        $query = $this->where('ip_address', $ipAddress);
        
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        
        return $query->countAllResults() > 0;
    }

    /**
     * Check if server exists by hostname
     */
    public function serverExistsByHostname($hostname, $excludeId = null)
    {
        $query = $this->where('hostname', $hostname);
        
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        
        return $query->countAllResults() > 0;
    }

    /**
     * Get server statistics
     */
    public function getServerStats()
    {
        return [
            'total' => $this->countAll(),
            'online' => $this->where('status', 'online')->countAllResults(),
            'offline' => $this->where('status', 'offline')->countAllResults(),
            'error' => $this->where('status', 'error')->countAllResults(),
            'active' => $this->where('is_active', 1)->countAllResults()
        ];
    }

    /**
     * Search servers
     */
    public function searchServers($searchTerm)
    {
        return $this->like('name', $searchTerm)
                    ->orLike('hostname', $searchTerm)
                    ->orLike('ip_address', $searchTerm)
                    ->orLike('description', $searchTerm)
                    ->findAll();
    }
} 