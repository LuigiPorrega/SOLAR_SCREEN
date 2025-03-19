<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Home;
use App\Controllers\Simulaciones;
use App\Controllers\Ideas;
use App\Controllers\Usuarios;
use App\Controllers\LoginLog;

$routes->setAutoRoute(false);

// Ruta principal
$routes->get('/', [Home::class, 'index']);

// Rutas de autenticación
$routes->get('login', [Usuarios::class, 'loginForm']);
$routes->post('login', [Usuarios::class, 'checkUser']);
$routes->get('logout', [Usuarios::class, 'closeSession']);

// Rutas para el backend (requieren autenticación)
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    // Dashboard
    $routes->get('inicio', [Home::class, 'dashboard']); 

    // Rutas para Simulaciones
    $routes->get('simulaciones', [Simulaciones::class, 'index']);
    $routes->get('simulaciones/new', [Simulaciones::class, 'new']);
    $routes->post('simulaciones/create', [Simulaciones::class, 'create']);
    $routes->get('simulaciones/update/(:segment)', [Simulaciones::class, 'update']);
    $routes->post('simulaciones/update/updated/(:segment)', [Simulaciones::class, 'updatedItem']);
    $routes->get('simulaciones/delete/(:segment)', [Simulaciones::class, 'delete']);
    $routes->get('simulaciones/(:segment)', [Simulaciones::class, 'view']);

    // Rutas para Ideas
    $routes->get('ideas', [Ideas::class, 'index']);
    $routes->get('ideas/new', [Ideas::class, 'new']);
    $routes->post('ideas/create', [Ideas::class, 'create']);
    $routes->get('ideas/update/(:segment)', [Ideas::class, 'update']);
    $routes->post('ideas/update/updated/(:segment)', [Ideas::class, 'updatedItem']);
    $routes->get('ideas/delete/(:segment)', [Ideas::class, 'delete']);
    $routes->get('ideas/(:segment)', [Ideas::class, 'view']);

    // Rutas para Usuarios (solo accesibles por administradores)
    $routes->get('users', [Usuarios::class, 'index']);
    $routes->get('users/new', [Usuarios::class, 'new']);
    $routes->post('users/create', [Usuarios::class, 'create']);
    $routes->get('users/update/(:segment)', [Usuarios::class, 'update']);
    $routes->post('users/update/updated/(:segment)', [Usuarios::class, 'updatedItem']);
    $routes->get('users/delete/(:segment)', [Usuarios::class, 'delete']);
    $routes->get('users/(:segment)', [Usuarios::class, 'view']);

    // Rutas para LoginLog (solo accesible por administradores)
    $routes->get('loginlog', [LoginLog::class, 'index']);
    $routes->get('loginlog/view/(:segment)', [LoginLog::class, 'view']);
    $routes->get('loginlog/export', [LoginLog::class, 'export']);
});

