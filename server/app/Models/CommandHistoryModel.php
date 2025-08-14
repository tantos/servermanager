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
     * Get command history for a specific server
     */
    public function getByServer($serverId, $limit = null)
    {
        $query = $this->where('server_id', $serverId)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->findAll();
    }

    /**
     * Get command history for a specific user
     */
    public function getByUser($userId, $limit = null)
    {
        $query = $this->where('user_id', $userId)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->findAll();
    }

    /**
     * Get commands by status
     */
    public function getByStatus($status, $limit = null)
    {
        $query = $this->where('status', $status)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->findAll();
    }

    /**
     * Get successful commands
     */
    public function getSuccessfulCommands($limit = null)
    {
        return $this->getByStatus('success', $limit);
    }

    /**
     * Get failed commands
     */
    public function getFailedCommands($limit = null)
    {
        return $this->getByStatus('error', $limit);
    }

    /**
     * Get timed out commands
     */
    public function getTimedOutCommands($limit = null)
    {
        return $this->getByStatus('timeout', $limit);
    }

    /**
     * Get recent commands
     */
    public function getRecentCommands($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    /**
     * Search command history
     */
    public function searchCommands($searchTerm, $limit = null)
    {
        $query = $this->like('command', $searchTerm)
                      ->orLike('output', $searchTerm)
                      ->orLike('error', $searchTerm)
                      ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->findAll();
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
     * Clean old command history
     */
    public function cleanOldHistory($days = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }

    /**
     * Get average execution time
     */
    public function getAverageExecutionTime()
    {
        $result = $this->select('AVG(execution_time) as avg_time')
                       ->where('execution_time IS NOT NULL')
                       ->first();
        
        return $result ? $result['avg_time'] : 0;
    }

    /**
     * Get commands executed today
     */
    public function getCommandsToday()
    {
        $today = date('Y-m-d');
        return $this->where('DATE(created_at)', $today)->countAllResults();
    }

    /**
     * Get commands executed this week
     */
    public function getCommandsThisWeek()
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        
        return $this->where('DATE(created_at) >=', $weekStart)
                    ->where('DATE(created_at) <=', $weekEnd)
                    ->countAllResults();
    }

    /**
     * Get commands executed this month
     */
    public function getCommandsThisMonth()
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        
        return $this->where('DATE(created_at) >=', $monthStart)
                    ->where('DATE(created_at) <=', $monthEnd)
                    ->countAllResults();
    }
} 