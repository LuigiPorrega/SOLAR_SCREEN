<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\LoginLogModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuariosApiController extends BaseController
{
    protected $usuariosModel;
    protected $loginLogModel; // Modelo de logs

    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
        $this->loginLogModel = new LoginLogModel(); // Inicializa el modelo de LoginLog
    }

    private function setCorsHeaders()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', 'http://localhost:8000/login');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    public function login()
    {
        $this->setCorsHeaders();

        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(204);
        }

        // Obtener datos JSON o datos POST
        $data = $this->request->getJSON(true); // Devuelve como array asociativo
        if (!$data) {
            $data = $this->request->getPost(); // Alternativa: application/x-www-form-urlencoded
        }

        // Validar que se obtuvieron los datos necesarios
        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Campos de usuario y contraseña son requeridos.'
            ])->setStatusCode(400);
        }

        $username = $data['username'];
        $password = $data['password'];

        // Verificar el usuario en la base de datos
        $user = $this->usuariosModel->where('username', $username)->first();

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Usuario no encontrado.'
            ])->setStatusCode(404);
        }

        // Verificar la contraseña
        if (hash('sha256', $password) !== $user['PasswordHash']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Contraseña incorrecta.'
            ])->setStatusCode(401);
        }

        // Generar token JWT
        $key = getenv('JWT_SECRET') ?: 'clave_secreta_demo';
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => [
                'id' => $user['ID'],
                'username' => $user['Username']
            ]
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'token' => $jwt,
                'username' => $user['Username'],
                'role' => $user['Rol'],
            ]
        ])->setStatusCode(200);
    }



    public function logout()
    {
        $this->setCorsHeaders(); // Configurar encabezados CORS

        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(204);
        }

        // Como JWT es stateless, no se puede "invalidar" directamente.
        // Opcionalmente, podrías manejar una blacklist de tokens si lo necesitas.

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Logout simulado (JWT no requiere logout real)'
        ]);
    }

    public function checkAccess()
    {
        $this->setCorsHeaders(); // Configurar encabezados CORS

        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(204);
        }

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
