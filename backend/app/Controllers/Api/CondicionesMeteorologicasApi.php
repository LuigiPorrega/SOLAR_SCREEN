<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CondicionesMeteorologicasModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CondicionesMeteorologicasApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = CondicionesMeteorologicasModel::class;
    protected $format    = 'json';

    // Función para obtener datos del usuario desde el JWT
    private function getUserDataFromToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $key = getenv('JWT_SECRET') ?: 'clave_secreta_demo'; // Usa la clave secreta desde el env

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }

    // GET /api/condicionesMeteorologicas?page=1
    public function index()
    {
        // Número de elementos por página
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Obtener datos paginados
        $data = $this->model->paginate($perPage);
        $pager = $this->model->pager;

        // Enviar respuesta con info de paginación
        return $this->respond([
            'status'       => 'success',
            'data'         => $data,
            'currentPage'  => $pager->getCurrentPage(),
            'perPage'      => $pager->getPerPage(),
            'totalItems'   => $pager->getTotal(),
            'totalPages'   => $pager->getPageCount(),
        ]);
    }

    // GET /api/condicionesMeteorologicas/{id}
    public function view($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }
        return $this->respond($data);
    }

    // POST /api/condicionesMeteorologicas
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validar los datos de entrada
        if (!$this->validate([
            'Fecha' => 'required|valid_date',
            'LuzSolar' => 'required|numeric',
            'Temperatura' => 'required|numeric',
            'Humedad' => 'required|numeric',
            'Viento' => 'required|numeric',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Guardar directamente sin usuario
        $id = $this->model->insert($data);
        $data['ID'] = $id;

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Condición meteorológica creada correctamente.',
            'data' => $data
        ]);
    }



    // PUT /api/condicionesMeteorologicas/{id}
    public function update($id = null)
    {
        // Obtener los datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Obtener la condición meteorológica a actualizar
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }

        // Verificar si el usuario es el propietario o es admin
        if ($data['UsuarioID'] !== $userData->user_id && $userData->rol !== 'admin') {
            return $this->failForbidden('No tienes permiso para actualizar esta condición meteorológica.');
        }

        // Obtener los datos del cuerpo de la solicitud (body)
        $input = $this->request->getJSON(true);

        // Validar los datos
        if (!$this->validate([
            'Fecha' => 'required|valid_date',
            'LuzSolar' => 'required|numeric',
            'Temperatura' => 'required|numeric',
            'Humedad' => 'required|numeric',
            'Viento' => 'required|numeric',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la condición meteorológica
        $this->model->update($id, $input);

        // Devolver respuesta con mensaje adicional
        return $this->respond([
            'status' => 'success',
            'message' => 'Condición meteorológica actualizada correctamente.',
            'data' => $input
        ]);
    }

    // DELETE /api/condicionesMeteorologicas/{id}
    public function delete($id = null)
    {
        // Obtener los datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Obtener la condición meteorológica a eliminar
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }

        // Verificar si el usuario es el propietario o es admin
        if ($data['UsuarioID'] !== $userData->user_id && $userData->rol !== 'admin') {
            return $this->failForbidden('No tienes permiso para eliminar esta condición meteorológica.');
        }

        // Eliminar la condición meteorológica
        $this->model->delete($id);
        return $this->respondDeleted([
            'status' => 'success',
            "message" => "Condición meteorológica eliminada."
        ]);
    }
}
