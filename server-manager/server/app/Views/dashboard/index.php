<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .stats-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        .server-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        
        .server-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        .server-card.online {
            border-left-color: #28a745;
        }
        
        .server-card.offline {
            border-left-color: #dc3545;
        }
        
        .server-card.error {
            border-left-color: #ffc107;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Server Manager</h4>
                        <small class="text-white-50">Control Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= base_url('dashboard') ?>">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/servers') ?>">
                                <i class="bi bi-server"></i>
                                Servers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/terminal') ?>">
                                <i class="bi bi-terminal"></i>
                                Terminal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/apache2') ?>">
                                <i class="bi bi-globe"></i>
                                Apache2
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/php_fpm') ?>">
                                <i class="bi bi-code-slash"></i>
                                PHP-FPM
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/services') ?>">
                                <i class="bi bi-gear"></i>
                                Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/system') ?>">
                                <i class="bi bi-cpu"></i>
                                System
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/settings') ?>">
                                <i class="bi bi-gear-wide-connected"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white-50">
                    
                    <div class="text-center">
                        <small class="text-white-50">
                            Logged in as: <?= $user['username'] ?>
                        </small>
                        <br>
                        <a href="<?= base_url('auth/logout') ?>" class="btn btn-outline-light btn-sm mt-2">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card card border-0 h-100">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-server text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="card-title text-primary"><?= $totalServers ?></h4>
                                <p class="card-text text-muted">Total Servers</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card card border-0 h-100">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="card-title text-success"><?= $onlineServers ?></h4>
                                <p class="card-text text-muted">Online Servers</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card card border-0 h-100">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="card-title text-danger"><?= $offlineServers ?></h4>
                                <p class="card-text text-muted">Offline Servers</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card card border-0 h-100">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-activity text-info" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="card-title text-info">0</h4>
                                <p class="card-text text-muted">Active Sites</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Servers Overview -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-server"></i> Servers Overview
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($servers)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-server text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="text-muted mt-3">No servers configured</h5>
                                        <p class="text-muted">Add your first server to get started</p>
                                        <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Add Server
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($servers as $server): ?>
                                            <div class="col-lg-4 col-md-6 mb-3">
                                                <div class="server-card card h-100 <?= $server['status'] ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0"><?= $server['name'] ?></h6>
                                                            <span class="badge status-badge bg-<?= $server['status'] === 'online' ? 'success' : ($server['status'] === 'offline' ? 'danger' : 'warning') ?>">
                                                                <?= ucfirst($server['status']) ?>
                                                            </span>
                                                        </div>
                                                        <p class="card-text text-muted small mb-2">
                                                            <i class="bi bi-hdd-network"></i> <?= $server['hostname'] ?>
                                                        </p>
                                                        <p class="card-text text-muted small mb-2">
                                                            <i class="bi bi-geo-alt"></i> <?= $server['ip_address'] ?>:<?= $server['port'] ?>
                                                        </p>
                                                        <?php if ($server['description']): ?>
                                                            <p class="card-text small"><?= $server['description'] ?></p>
                                                        <?php endif; ?>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                Last seen: <?= $server['last_seen'] ? date('M j, Y H:i', strtotime($server['last_seen'])) : 'Never' ?>
                                                            </small>
                                                            <a href="<?= base_url('dashboard/server/' . $server['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-activity"></i> Recent Activity
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentCommands)): ?>
                                    <div class="text-center py-3">
                                        <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No recent activity</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>Server</th>
                                                    <th>Command</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentCommands as $command): ?>
                                                    <tr>
                                                        <td><?= date('M j, H:i', strtotime($command['created_at'])) ?></td>
                                                        <td><?= $command['server_id'] ?></td>
                                                        <td><code><?= substr($command['command'], 0, 50) ?><?= strlen($command['command']) > 50 ? '...' : '' ?></code></td>
                                                        <td>
                                                            <span class="badge bg-<?= $command['status'] === 'success' ? 'success' : ($command['status'] === 'error' ? 'danger' : 'warning') ?>">
                                                                <?= ucfirst($command['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function refreshDashboard() {
            location.reload();
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            // Only refresh if user is on dashboard
            if (window.location.pathname === '/dashboard' || window.location.pathname === '/dashboard/') {
                refreshDashboard();
            }
        }, 30000);
    </script>
</body>
</html> 