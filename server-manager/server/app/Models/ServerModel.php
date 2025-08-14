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
     * Get active servers
     */
    public function getActiveServers()
    {
        return $this->where('is_active', 1)->findAll();
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
     * Get servers by status
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)->where('is_active', 1)->findAll();
    }

    /**
     * Update server status
     */
    public function updateStatus($serverId, $status, $lastSeen = null)
    {
        $data = ['status' => $status];
        if ($lastSeen) {
            $data['last_seen'] = $lastSeen;
        }
        return $this->update($serverId, $data);
    }

    /**
     * Update server last seen
     */
    public function updateLastSeen($serverId)
    {
        return $this->update($serverId, ['last_seen' => date('Y-m-d H:i:s')]);
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
    public function searchServers($query)
    {
        return $this->like('name', $query)
                    ->orLike('hostname', $query)
                    ->orLike('ip_address', $query)
                    ->orLike('description', $query)
                    ->findAll();
    }

    /**
     * Get servers with recent activity
     */
    public function getServersWithRecentActivity($days = 7)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('last_seen >=', $date)->findAll();
    }

    /**
     * Activate/deactivate server
     */
    public function toggleStatus($serverId)
    {
        $server = $this->find($serverId);
        if ($server) {
            $newStatus = $server['is_active'] ? 0 : 1;
            return $this->update($serverId, ['is_active' => $newStatus]);
        }
        return false;
    }
} 