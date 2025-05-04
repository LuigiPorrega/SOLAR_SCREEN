<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Permitir solicitudes desde cualquier origen durante desarrollo (reemplazar con tu dominio real en producción)
        header('Access-Control-Allow-Origin: http://localhost:4200');  // Cambia * por tu dominio específico en producción
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');

        // Responder a la solicitud preflight OPTIONS
        if ($request->getMethod() === 'options') {
            header('HTTP/1.1 204 No Content');
            exit();  // Detener la ejecución aquí para que no se procesen más filtros
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        
    }
}
