<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:4200');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');

        if ($request->getMethod() === 'options') {
            return $response->setStatusCode(204);
        }

        // Devuelve el response para asegurarte de que los headers se apliquen
        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
