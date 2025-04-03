<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Home;
use App\Controllers\Simulaciones;
use App\Controllers\CondicionesMeteorologicas;
use App\Controllers\Ideas;
use App\Controllers\Usuarios;
use App\Controllers\Proveedores;
use App\Controllers\ModelosFundas;
use App\Controllers\LoginLog;



$routes->setAutoRoute(false);

// Ruta principal
$routes->get('/', [Home::class, 'index']);

// Rutas de autenticación
$routes->get('login', [Usuarios::class, 'loginForm']);
$routes->post('login', [Usuarios::class, 'checkUser']);
$routes->get('admin/logout', [Usuarios::class, 'closeSession']);

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

    // Rutas para CondicionesMeteorologicas
    $routes->get('condicionesMeteorologicas', [CondicionesMeteorologicas::class, 'index']);
    $routes->get('condicionesMeteorologicas/new', [CondicionesMeteorologicas::class, 'new']);
    $routes->post('condicionesMeteorologicas/create', [CondicionesMeteorologicas::class, 'create']);
    $routes->get('condicionesMeteorologicas/update/(:segment)', [CondicionesMeteorologicas::class, 'update']);
    $routes->post('condicionesMeteorologicas/update/updated/(:segment)', [CondicionesMeteorologicas::class, 'updatedItem']);
    $routes->get('condicionesMeteorologicas/delete/(:segment)', [CondicionesMeteorologicas::class, 'delete']);
    $routes->get('condicionesMeteorologicas/(:segment)', [CondicionesMeteorologicas::class, 'view']);


    // Rutas para Proveedores
    $routes->get('proveedores', [Proveedores::class, 'index']);
    $routes->get('proveedores/new', [Proveedores::class, 'new']);
    $routes->post('proveedores/create', [Proveedores::class, 'create']);
    $routes->get('proveedores/update/(:segment)', [Proveedores::class, 'update']);
    $routes->post('proveedores/update/updated/(:segment)', [Proveedores::class, 'updatedItem']);
    $routes->get('proveedores/delete/(:segment)', [Proveedores::class, 'delete']);
    $routes->get('proveedores/(:segment)', [Proveedores::class, 'view']);

    // Rutas para ModelosFundas
    $routes->get('modelosFundas', [ModelosFundas::class, 'index']);
    $routes->get('modelosFundas/new', [ModelosFundas::class, 'new']);
    $routes->post('modelosFundas/create', [ModelosFundas::class, 'create']);
    $routes->get('modelosFundas/update/(:segment)', [ModelosFundas::class, 'update']);
    $routes->post('modelosFundas/update/updated/(:segment)', [ModelosFundas::class, 'updatedItem']);
    $routes->get('modelosFundas/delete/(:segment)', [ModelosFundas::class, 'delete']);
    $routes->get('modelosFundas/(:segment)', [ModelosFundas::class, 'view']);

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
    $routes->get('loginlog/export', [LoginLog::class, 'export']);
});

