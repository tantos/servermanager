<?php

namespace App\Models;

use CodeIgniter\Model;

class CommandHistoryModel extends Model
{
    protected $table = 'command_history';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'server_id',
        'user_id',
        'command',
        'output',
        'error',
        'exit_code',
        'execution_time',
        'status',
        'ip_address',
        'user_agent'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'server_id' => 'required|integer|is_natural_no_zero',
        'user_id' => 'required|integer|is_natural_no_zero',
        'command' => 'required|min_length[1]',
        'status' => 'required|in_list[success,error,timeout]'
    ];

    protected $validationMessages = [
        'server_id' => [
            'required' => 'Server ID is required',
            'integer' => 'Server ID must be a number',
            'is_natural_no_zero' => 'Server ID must be a positive number'
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a number',
            'is_natural_no_zero' => 'User ID must be a positive number'
        ],
        'command' => [
            'required' => 'Command is required',
            'min_length' => 'Command must be at least 1 character long'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be success, error, or timeout'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get commands by server
     */
    public function getByServer($serverId, $limit = 50)
    {
        return $this->where('server_id', $serverId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get commands by user
     */
    public function getByUser($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get recent commands
     */
    public function getRecentCommands($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    /**
     * Get commands by status
     */
    public function getByStatus($status, $limit = 50)
    {
        return $this->where('status', $status)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get failed commands
     */
    public function getFailedCommands($limit = 50)
    {
        return $this->whereIn('status', ['error', 'timeout'])
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get command statistics
     */
    public function getCommandStats()
    {
        return [
            'total' => $this->countAll(),
            'success' => $this->where('status', 'success')->countAllResults(),
            'error' => $this->where('status', 'error')->countAllResults(),
            'timeout' => $this->where('status', 'timeout')->countAllResults()
        ];
    }

    /**
     * Get command statistics by server
     */
    public function getCommandStatsByServer($serverId)
    {
        return [
            'total' => $this->where('server_id', $serverId)->countAllResults(),
            'success' => $this->where('server_id', $serverId)->where('status', 'success')->countAllResults(),
            'error' => $this->where('server_id', $serverId)->where('status', 'error')->countAllResults(),
            'timeout' => $this->where('server_id', $serverId)->where('status', 'timeout')->countAllResults()
        ];
    }

    /**
     * Get command statistics by user
     */
    public function getCommandStatsByUser($userId)
    {
        return [
            'total' => $this->where('user_id', $userId)->countAllResults(),
            'success' => $this->where('user_id', $userId)->where('status', 'success')->countAllResults(),
            'error' => $this->where('user_id', $userId)->where('status', 'error')->countAllResults(),
            'timeout' => $this->where('user_id', $userId)->where('status', 'timeout')->countAllResults()
        ];
    }

    /**
     * Search commands
     */
    public function searchCommands($query, $limit = 50)
    {
        return $this->like('command', $query)
                    ->orLike('output', $query)
                    ->orLike('error', $query)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get commands by date range
     */
    public function getByDateRange($startDate, $endDate, $limit = 100)
    {
        return $this->where('created_at >=', $startDate)
                    ->where('created_at <=', $endDate)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get commands with long execution time
     */
    public function getLongRunningCommands($threshold = 10, $limit = 50)
    {
        return $this->where('execution_time >', $threshold)
                    ->orderBy('execution_time', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Clean old command history
     */
    public function cleanOldHistory($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('created_at <', $date)->delete();
    }

    /**
     * Get command history summary
     */
    public function getHistorySummary($days = 7)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->select('DATE(created_at) as date, COUNT(*) as total, 
                             SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success,
                             SUM(CASE WHEN status = "error" THEN 1 ELSE 0 END) as error,
                             SUM(CASE WHEN status = "timeout" THEN 1 ELSE 0 END) as timeout')
                    ->where('created_at >=', $date)
                    ->groupBy('DATE(created_at)')
                    ->orderBy('date', 'DESC')
                    ->findAll();
    }
} 