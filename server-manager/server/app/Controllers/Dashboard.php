<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ServerModel;
use App\Models\CommandHistoryModel;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $serverModel;
    protected $commandHistoryModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->serverModel = new ServerModel();
        $this->commandHistoryModel = new CommandHistoryModel();
    }

    /**
     * Main dashboard page
     */
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Dashboard - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'totalServers' => $this->serverModel->countAll(),
            'onlineServers' => $this->serverModel->where('status', 'online')->countAllResults(),
            'offlineServers' => $this->serverModel->where('status', 'offline')->countAllResults(),
            'recentCommands' => $this->commandHistoryModel->orderBy('created_at', 'DESC')->limit(10)->find(),
            'servers' => $this->serverModel->findAll()
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Servers overview page
     */
    public function servers()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Servers - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->findAll()
        ];

        return view('dashboard/servers', $data);
    }

    /**
     * Individual server details page
     */
    public function server($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $server = $this->serverModel->find($id);
        if (!$server) {
            return redirect()->to('/dashboard/servers')->with('error', 'Server not found');
        }

        $data = [
            'title' => 'Server Details - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'server' => $server,
            'recentCommands' => $this->commandHistoryModel->where('server_id', $id)->orderBy('created_at', 'DESC')->limit(20)->find()
        ];

        return view('dashboard/server', $data);
    }

    /**
     * Terminal access page
     */
    public function terminal()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Terminal - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->where('is_active', 1)->findAll()
        ];

        return view('dashboard/terminal', $data);
    }

    /**
     * Apache2 management page
     */
    public function apache2()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Apache2 Management - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->where('is_active', 1)->findAll()
        ];

        return view('dashboard/apache2', $data);
    }

    /**
     * PHP-FPM management page
     */
    public function php_fpm()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'PHP-FPM Management - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->where('is_active', 1)->findAll()
        ];

        return view('dashboard/php_fpm', $data);
    }

    /**
     * Services management page
     */
    public function services()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Services Management - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->where('is_active', 1)->findAll()
        ];

        return view('dashboard/services', $data);
    }

    /**
     * System information page
     */
    public function system()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'System Information - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->where('is_active', 1)->findAll()
        ];

        return view('dashboard/system', $data);
    }

    /**
     * Settings page
     */
    public function settings()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Settings - Multi-Server Control Panel',
            'user' => session()->get('user'),
            'servers' => $this->serverModel->findAll()
        ];

        return view('dashboard/settings', $data);
    }
} 