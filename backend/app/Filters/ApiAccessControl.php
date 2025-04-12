<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiAccessControl implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Obtener el token JWT del encabezado Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        $key = getenv('JWT_SECRET') ?: 'clave_secreta_demo';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $response = \Config\Services::response()->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED, 'No autorizado. Falta token Bearer.');
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        try {
            // Decodificar el token JWT
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Usar el session para almacenar los datos del usuario
            session()->set('userData', $decoded->data);
        } catch (\Exception $e) {
            // Retornar un error si el token no es válido
            return \Config\Services::response()->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED, 'Token inválido o expirado.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No es necesario hacer nada después de la solicitud
    }
}
