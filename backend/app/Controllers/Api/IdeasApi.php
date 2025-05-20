<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\IdeasModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class IdeasApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = IdeasModel::class;
    protected $format    = 'json';
    protected $db; 

    public function __construct()
    {
        $this->db = \Config\Database::connect(); 
    }

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

    public function index()
    {
        $perPage = $this->request->getGet('perPage') ?? 10;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;

        // Usamos el query builder para hacer JOIN
        $builder = $this->db->table('Ideas i')
            ->select('i.*, u.Nombre as UsuarioNombre')
            ->join('Usuarios u', 'u.ID = i.UsuarioID', 'left')
            ->orderBy('i.FechaCreacion', 'DESC')
            ->limit($perPage, $offset);

        $ideas = $builder->get()->getResultArray();

        // Contamos total sin límite para paginación
        $totalIdeas = $this->db->table('Ideas')->countAllResults();

        return $this->respond([
            'status'       => 'success',
            'data'         => $ideas,
            'currentPage'  => (int)$page,
            'perPage'      => (int)$perPage,
            'totalItems'   => $totalIdeas,
            'totalPages'   => ceil($totalIdeas / $perPage),
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validación de los datos
        if (!$this->validate([
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Obtener datos del usuario
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Asignar el usuario actual al crear la idea
        $data['UsuarioID'] = property_exists($userData, 'id') ? $userData->id : null;
        $data['FechaCreacion'] = date('Y-m-d H:i:s');

        // Insertar en la base de datos
        $this->model->insert($data);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Idea creada exitosamente.',
            'data' => $data
        ]);
    }

    public function update($id = null)
    {
        $idea = $this->model->find($id);
        if (!$idea) {
            return $this->failNotFound("Idea no encontrada con el ID: $id");
        }

        // Obtener datos del usuario
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Verificar si el usuario es el autor o es admin
        if ($idea['usuario_id'] !== $userData->user_id && $userData->rol !== 'admin') {
            return $this->failForbidden('No autorizado para modificar esta idea');
        }

        $data = $this->request->getJSON(true);

        // Validación de los datos
        if (!$this->validate([
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la idea en la base de datos
        $this->model->update($id, $data);

        return $this->respond([
            'status' => 'success',
            'message' => 'Idea actualizada exitosamente.',
            'data' => $data
        ]);
    }

    public function delete($id = null)
    {
        $idea = $this->model->find($id);
        if (!$idea) {
            return $this->failNotFound("Idea no encontrada con el ID: $id");
        }

        // Obtener datos del usuario
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Verificar si el usuario es el autor o es admin
        if ($idea['usuario_id'] !== $userData->user_id && $userData->rol !== 'admin') {
            return $this->failForbidden('No autorizado para eliminar esta idea');
        }

        // Eliminar la idea de la base de datos
        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Idea eliminada exitosamente.'
        ]);
    }
}
