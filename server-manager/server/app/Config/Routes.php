<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Dashboard::index');

// Dashboard routes
$routes->group('dashboard', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('servers', 'Dashboard::servers');
    $routes->get('server/(:num)', 'Dashboard::server/$1');
    $routes->get('terminal', 'Dashboard::terminal');
    $routes->get('apache2', 'Dashboard::apache2');
    $routes->get('php_fpm', 'Dashboard::php_fpm');
    $routes->get('services', 'Dashboard::services');
    $routes->get('system', 'Dashboard::system');
    $routes->get('settings', 'Dashboard::settings');
});

// Auth routes
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/login', 'Auth::login');
$routes->get('auth/logout', 'Auth::logout');

// API routes for server communication
$routes->group('api', function($routes) {
    $routes->post('servers/(:num)/command', 'Api::executeCommand/$1');
    $routes->get('servers/(:num)/status', 'Api::getServerStatus/$1');
    $routes->get('servers/(:num)/services', 'Api::getServicesStatus/$1');
    $routes->get('servers/(:num)/apache2/sites', 'Api::getApache2Sites/$1');
    $routes->post('servers/(:num)/apache2/sites/(:any)/(:any)', 'Api::manageApache2Site/$1/$2/$3');
    $routes->get('servers/(:num)/php/(:any)/info', 'Api::getPHPInfo/$1/$2');
    $routes->get('servers/(:num)/system', 'Api::getSystemInfo/$1');
});

// Catch-all route for 404
$routes->set404Override(function() {
    return view('errors/html/error_404');
}); 