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
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            border: none;
        }
        .status-online {
            color: #28a745;
        }
        .status-offline {
            color: #dc3545;
        }
        .status-maintenance {
            color: #ffc107;
        }
        .server-card {
            transition: transform 0.2s;
        }
        .server-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
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
                            <a class="nav-link" href="<?= base_url('dashboard/services') ?>">
                                <i class="bi bi-gear"></i>
                                Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/apache2') ?>">
                                <i class="bi bi-globe"></i>
                                Apache2
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/php-fpm') ?>">
                                <i class="bi bi-code-slash"></i>
                                PHP-FPM
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/terminal') ?>">
                                <i class="bi bi-terminal"></i>
                                Terminal
                            </a>
                        </li>
                        <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard/settings') ?>">
                                <i class="bi bi-gear-fill"></i>
                                Settings
                            </a>
                        </li>
                        <?php endif; ?>
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
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Servers
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_servers ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-server fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Online Servers
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $online_servers ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Active Sites
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-globe fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            System Load
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cpu fa-2x text-gray-300"></i>
                                    </div>
                                </div>
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
                                                <div class="card server-card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0"><?= $server['name'] ?></h6>
                                                            <span class="badge bg-<?= $server['status'] === 'online' ? 'success' : ($server['status'] === 'maintenance' ? 'warning' : 'danger') ?>">
                                                                <?= ucfirst($server['status']) ?>
                                                            </span>
                                                        </div>
                                                        <p class="card-text text-muted small mb-2">
                                                            <i class="bi bi-hdd-network"></i> <?= $server['ip_address'] ?>
                                                        </p>
                                                        <p class="card-text text-muted small mb-3">
                                                            <i class="bi bi-info-circle"></i> <?= $server['description'] ?: 'No description' ?>
                                                        </p>
                                                        <div class="btn-group btn-group-sm w-100">
                                                            <a href="<?= base_url("dashboard/server/{$server['id']}") ?>" class="btn btn-outline-primary">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                            <a href="<?= base_url("dashboard/terminal/{$server['id']}") ?>" class="btn btn-outline-secondary">
                                                                <i class="bi bi-terminal"></i> Terminal
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
                                <div class="text-center py-3">
                                    <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">No recent activity</p>
                                </div>
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