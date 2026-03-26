<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/upload', 'Home::upload');
$routes->get('/gethistory', 'Home::gethistory');
$routes->post('/gethistory', 'Home::gethistoryitem');
$routes->post('/deletehistory', 'Home::deletehistory');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');

// Metrics route
$routes->post('/metrics/receive', 'Metrics::receive');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');
