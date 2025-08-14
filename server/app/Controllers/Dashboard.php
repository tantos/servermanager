<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServerModel;
use App\Models\ServerKeyModel;
use App\Libraries\ServerAgent;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $serverModel;
    protected $serverKeyModel;
    protected $serverAgent;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->serverModel = new ServerModel();
        $this->serverKeyModel = new ServerKeyModel();
        $this->serverAgent = new ServerAgent();
    }

    public function index()
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Dashboard - Server Manager',
            'user' => $this->userModel->find(session()->get('user_id')),
            'servers' => $this->serverModel->where('is_active', true)->findAll(),
            'total_servers' => $this->serverModel->where('is_active', true)->countAllResults(),
            'online_servers' => $this->serverModel->where('is_active', true)->where('status', 'online')->countAllResults(),
        ];

        return view('dashboard/index', $data);
    }

    public function servers()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Servers - Server Manager',
            'user' => $this->userModel->find(session()->get('user_id')),
            'servers' => $this->serverModel->where('is_active', true)->findAll(),
        ];

        return view('dashboard/servers', $data);
    }

    public function server($id)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($id);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        // Get server status and info
        $serverInfo = $this->serverAgent->getServerInfo($server);
        
        $data = [
            'title' => "Server: {$server['name']} - Server Manager",
            'user' => $this->userModel->find(session()->get('user_id')),
            'server' => $server,
            'serverInfo' => $serverInfo,
        ];

        return view('dashboard/server_detail', $data);
    }

    public function terminal($serverId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        $data = [
            'title' => "Terminal: {$server['name']} - Server Manager",
            'user' => $this->userModel->find(session()->get('user_id')),
            'server' => $server,
        ];

        return view('dashboard/terminal', $data);
    }

    public function apache2($serverId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        // Get Apache2 information from server
        $apache2Info = $this->serverAgent->getApache2Info($server);

        $data = [
            'title' => "Apache2: {$server['name']} - Server Manager",
            'user' => $this->userModel->find(session()->get('user_id')),
            'server' => $server,
            'apache2Info' => $apache2Info,
        ];

        return view('dashboard/apache2', $data);
    }

    public function php_fpm($serverId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        // Get PHP-FPM information from server
        $phpInfo = $this->serverAgent->getPHPInfo($server);

        $data = [
            'title' => "PHP-FPM: {$server['name']} - Server Manager",
            'user' => $this->userModel->find(session()->get('user_id')),
            'server' => $server,
            'phpInfo' => $phpInfo,
        ];

        return view('dashboard/php_fpm', $data);
    }

    public function services($serverId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        // Get services status from server
        $servicesInfo = $this->serverAgent->getServicesStatus($server);

        $data = [
            'title' => "Services: {$server['name']} - Server Manager",
            'user' => $this->userModel->find(session()->get('user_id')),
            'server' => $server,
            'servicesInfo' => $servicesInfo,
        ];

        return view('dashboard/services', $data);
    }

    public function system($serverId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($serverId);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        // Get system information from server
        $systemInfo = $this->serverAgent->getSystemInfo($server);

        $data = [
            'title' => "System: {$server['name']} - Server Manager",
            'user' => $this->userModel->find(session()->get('user_id')),
            'server' => $server,
            'systemInfo' => $systemInfo,
        ];

        return view('dashboard/system', $data);
    }

    public function settings()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/auth/login');
        }

        $user = $this->userModel->find(session()->get('user_id'));
        if ($user['role'] !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $data = [
            'title' => 'Settings - Server Manager',
            'user' => $user,
            'servers' => $this->serverModel->findAll(),
        ];

        return view('dashboard/settings', $data);
    }
} 