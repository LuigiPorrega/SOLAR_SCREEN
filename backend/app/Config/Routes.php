<?php

// Manejar todas las solicitudes OPTIONS
$routes->options('(:any)', function() {
    header('Access-Control-Allow-Origin: http://localhost:4200'); // Reemplaza '*' con tu dominio específico en producción
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('HTTP/1.1 204 No Content');
    exit();
});

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Home;
use App\Controllers\Simulaciones;
use App\Controllers\CondicionesMeteorologicas;
use App\Controllers\Ideas;
use App\Controllers\Usuarios;
use App\Controllers\Proveedores;
use App\Controllers\ModelosFundas;
use App\Controllers\LoginLog;
use App\Controllers\Carrito;
use App\Controllers\Api\CondicionesMeteorologicasApi;
use App\Controllers\Api\IdeasApi;
use App\Controllers\Api\UsuariosApiController;
use App\Controllers\Api\ModelosFundasApi;
use App\Controllers\Api\ProveedoresApi;
use App\Controllers\Api\SimulacionesApi;
use App\Filters\ApiAccessControl;



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


    // Rutas para el Carrito
    $routes->get('carrito', [Carrito::class, 'index']);
    $routes->post('carrito/add', [Carrito::class, 'add']);
    $routes->post('carrito/update/(:segment)', [Carrito::class, 'update']);
    $routes->post('carrito/delete/(:segment)', [Carrito::class, 'delete']);
    $routes->post('carrito/vaciarCarrito', [Carrito::class, 'vaciarCarrito']);
});

//FRONTEND
// Mostrar resumen de rutas disponibles al entrar a /api
$routes->get('api', 'Api\ApiOverview::index');

// API RESTful
$routes->group('api', function ($routes) {

    // Rutas para Condiciones Meteorológicas
    $routes->get('condicionesMeteorologicas', 'Api\CondicionesMeteorologicasApi::index');
    $routes->get('condicionesMeteorologicas/(:num)', 'Api\CondicionesMeteorologicasApi::view/$1');

    // Rutas para Ideas
    $routes->get('ideas', 'Api\IdeasApi::index');
    $routes->get('ideas/(:num)', 'Api\IdeasApi::view/$1');

    //Rutas para ModeloFundas
    $routes->get('modelosFundas', 'Api\ModelosFundasApi::index');
    $routes->get('modelosFundas/(:num)', 'Api\ModelosFundasApi::view/$1');

    //Rutas para Proveedores
    $routes->get('proveedores', 'Api\ProveedoresApi::index');
    $routes->get('proveedores/(:num)', 'Api\ProveedoresApi::view/$1');

    //Rutas para Simulaciones
    $routes->get('simulaciones', 'Api\SimulacionesApi::index');
    $routes->get('simulaciones/(:num)', 'Api\SimulacionesApi::view/$1');

    //Ruta para autenticación de Usuarios
    $routes->post('usuarios/login', 'Api\UsuariosApiController::login');
    $routes->get('usuarios/checkAccess', 'Api\UsuariosApiController::checkAccess');
    $routes->post('usuarios/registrarse', 'Api\UsuariosApiController::registrarse');

});

//API RESTfull protegidas
$routes->group('api', ['filter' => 'apiaccesscontrol'], function ($routes) {

    // Rutas para Condiciones Meteorológicas
    $routes->post('condicionesMeteorologicas', 'Api\CondicionesMeteorologicasApi::create');
    $routes->put('condicionesMeteorologicas/(:num)', 'Api\CondicionesMeteorologicasApi::update/$1');
    $routes->delete('condicionesMeteorologicas/(:num)', 'Api\CondicionesMeteorologicasApi::delete/$1');

    // Rutas para Ideas
    $routes->post('ideas', 'Api\IdeasApi::create');
    $routes->put('ideas/(:num)', 'Api\IdeasApi::update/$1');
    $routes->delete('ideas/(:num)', 'Api\IdeasApi::delete/$1');

    //Rutas para ModeloFundas
    $routes->post('modelosFundas', 'Api\ModelosFundasApi::create');
    $routes->put('modelosFundas/(:num)', 'Api\ModelosFundasApi::update/$1');
    $routes->delete('modelosFundas/(:num)', 'Api\ModelosFundasApi::delete/$1');

    //Rutas para Proveedores
    $routes->post('proveedores', 'Api\ProveedoresApi::create');
    $routes->put('proveedores/(:num)', 'Api\ProveedoresApi::update/$1');
    $routes->delete('proveedores/(:num)', 'Api\ProveedoresApi::delete/$1');

    //Rutas para Simulaciones
    $routes->post('simulaciones', 'Api\SimulacionesApi::create');
    $routes->put('simulaciones/(:num)', 'Api\SimulacionesApi::update/$1');
    $routes->delete('simulaciones/(:num)', 'Api\SimulacionesApi::delete/$1');

    //Ruta para autenticación de Usuarios
    $routes->post('usuarios/logout', 'Api\UsuariosApiController::logout');

    // Rutas para Carrito
    $routes->get('carrito', 'Api\CarritoApi::index'); 
    $routes->post('carrito', 'Api\CarritoApi::add');  
    $routes->put('carrito/(:num)', 'Api\CarritoApi::update/$1'); 
    $routes->delete('carrito/(:num)', 'Api\CarritoApi::delete/$1');

});
