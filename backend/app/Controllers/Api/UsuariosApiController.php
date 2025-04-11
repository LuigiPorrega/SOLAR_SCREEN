<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuariosApiController extends BaseController
{
    protected $usuariosModel;

    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
    }

    public function login()
    {
        $data = $this->request->getJSON();

        if (!isset($data->username) || !isset($data->password)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Faltan parámetros: username o password'
            ]);
        }

        $usuario = $this->usuariosModel
            ->where('Username', $data->username)
            ->first();

        if ($usuario && hash('sha256', $data->password) === $usuario['PasswordHash']) {
            $key = getenv('JWT_SECRET') ?: 'clave_secreta_demo'; // Usa .env si puedes
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600; // 1 hora

            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'data' => [
                    'user_id' => $usuario['ID'],
                    'username' => $usuario['Username'],
                    'rol' => $usuario['Rol']
                ]
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Login exitoso',
                'token' => $jwt,
                'data' => [
                    'user_id' => $usuario['ID'],
                    'username' => $usuario['Username'],
                    'nombre' => $usuario['Nombre'],
                    'rol' => $usuario['Rol'],
                    'correo' => $usuario['Correo'],
                    'fecha_registro' => $usuario['Fecha_Registro']
                ]
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Credenciales incorrectas'
        ]);
    }

    public function logout()
    {
        // Como JWT es stateless, no se puede "invalidar" directamente.
        // Opcionalmente, podrías manejar una blacklist de tokens si lo necesitas.

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Logout simulado (JWT no requiere logout real)'
        ]);
    }

    public function checkAccess()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $key = getenv('JWT_SECRET') ?: 'clave_secreta_demo';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No autorizado. Falta token Bearer.'
            ])->setStatusCode(401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Acceso válido',
                'data' => $decoded->data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Token inválido o expirado'
            ])->setStatusCode(401);
        }
    }
}
