<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UsuariosModel;
use App\Models\LoginLogModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class UsuariosApiController extends BaseController
{
    protected $usuariosModel;
    protected $loginLogModel; // Modelo de logs

    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
        $this->loginLogModel = new LoginLogModel(); // Inicializa el modelo de LoginLog
    }

    public function setCorsHeaders()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', 'http://localhost:4200');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
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
        $secretKey = getenv('JWT_SECRET') ?: 'clave_secreta_demo';
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => [
                'id' => $user['ID'],
                'username' => $user['Username'],
                'role' => $user['Rol'],
            ]
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');
        log_message('debug', 'Login exitoso, generando token y respuesta JSON');


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
        $authorizationHeader = $this->request->getHeaderLine('Authorization');

        if (empty($authorizationHeader) || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Token de autorización inválido o faltante'
            ]);
        }

        $token = trim(str_replace('Bearer', '', $authorizationHeader));
        $secretKey = getenv('JWT_SECRET') ?: 'clave_secreta_demo';

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Token inválido',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function checkAccess()
    {
        $this->setCorsHeaders();

        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(204);
        }

        $authHeader = $this->request->getHeaderLine('Authorization');
        $secretKey = getenv('JWT_SECRET') ?: 'clave_secreta_demo';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No autorizado. Falta token Bearer.'
            ])->setStatusCode(401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Acceso válido',
                'data' => $decoded->data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Token inválido o expirado',
                'error' => $e->getMessage()
            ])->setStatusCode(401);
        }
    }

    public function registrarse()
    {
        $this->setCorsHeaders();

        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(204);
        }

        $data = $this->request->getJSON();

        if (
            !isset($data->nombre) ||
            !isset($data->correo) ||
            !isset($data->fechaNacimiento) ||
            !isset($data->username) ||
            !isset($data->password)
        ) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Todos los campos obligatorios deben ser enviados.'
            ])->setStatusCode(400);
        }

        // Verificar si el usuario ya existe
        if ($this->usuariosModel->where('Username', $data->username)->first()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El usuario ya existe.'
            ])->setStatusCode(409);
        }

        $this->usuariosModel->insert([
            'Nombre' => $data->nombre,
            'Correo' => $data->correo,
            'FechaNacimiento' => $data->fechaNacimiento,
            'GoogleID' => isset($data->googleID) ? $data->googleID : null,
            'Username' => $data->username,
            'PasswordHash' => hash('sha256', $data->password),
            'Rol' => 'usuario'
            // 'Fecha_Registro' se llena automáticamente con curdate()
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Usuario registrado correctamente.'
        ])->setStatusCode(201);
    }
}
