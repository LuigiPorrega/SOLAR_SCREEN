<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ApiOverview extends ResourceController
{
    public function index()
    {
        return $this->respond([
            'condicionesMeteorologicas' => [
                'GET' => '/api/condicionesMeteorologicas',
                'GET paginado' => '/api/condicionesMeteorologicas?page=1&limit=10',
                'GET by ID' => '/api/condicionesMeteorologicas/{id}',
                'POST' => '/api/condicionesMeteorologicas',
                'PUT' => '/api/condicionesMeteorologicas/{id}',
                'DELETE' => '/api/condicionesMeteorologicas/{id}',
            ],
            'ideas' => [
                'GET' => '/api/ideas',
                'GET paginado' => '/api/ideas?page=1&limit=10',
                'GET by ID' => '/api/ideas/{id}',
                'POST' => '/api/ideas',
                'PUT' => '/api/ideas/{id}',
                'DELETE' => '/api/ideas/{id}',
            ],
            'modelosFundas' => [
                'GET' => '/api/modelosFundas',
                'GET paginado' => '/api/modelosFundas?page=1&limit=9',
                'GET by ID' => '/api/modelosFundas/{id}',
                'POST' => '/api/modelosFundas',
                'PUT' => '/api/modelosFundas/{id}',
                'DELETE' => '/api/modelosFundas/{id}',
            ],
            'proveedores' => [
                'GET' => '/api/proveedores',
                'GET paginado' => '/api/proveedores?page=1&limit=10',
                'GET by ID' => '/api/proveedores/{id}',
                'POST' => '/api/proveedores',
                'PUT' => '/api/proveedores/{id}',
                'DELETE' => '/api/proveedores/{id}',
            ],
            'simulaciones' => [
                'GET' => '/api/simulaciones',
                'GET paginado' => '/api/simulaciones?page=1&limit=10',
                'GET by ID' => '/api/simulaciones/{id}',
                'POST' => '/api/simulaciones',
                'PUT' => '/api/simulaciones/{id}',
                'DELETE' => '/api/simulaciones/{id}',
            ],
            'usuarios' => [
                'POST login' => '/api/usuarios/login',
                'GET checkAccess' => '/api/usuarios/checkAccess',
                'POST logout' => '/api/usuarios/logout',
                'POST registrarse' => '/api/usuarios/registrarse',
            ],
            'carrito' => [
                'GET' => '/api/carrito',
                'POST' => '/api/carrito',
                'PUT' => '/api/carrito/{id}',
                'DELETE' => '/api/carrito/{id}',
            ]
        ]);
    }
}
