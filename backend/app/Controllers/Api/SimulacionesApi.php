<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SimulacionesModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SimulacionesApi extends BaseController
{
    use ResponseTrait;

    protected $simulacionesModel;

    public function __construct()
    {
        $this->simulacionesModel = new SimulacionesModel();
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

    // Obtener todas las simulaciones
    public function index()
    {
        $simulaciones = $this->simulacionesModel->findAll();
        return $this->respond($simulaciones);
    }

    // Obtener una simulación por ID
    public function view($id)
    {
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }
        return $this->respond($simulacion);
    }

    // Crear una nueva simulación
    public function create()
    {
        $data = $this->request->getJSON(true);

        // Validar los datos de entrada
        if (!$this->validate([
            'CondicionLuz' => 'required|string|max_length[255]',
            'EnergiaGenerada' => 'required|numeric',
            'Tiempo' => 'required|numeric',
            'Fecha' => 'required|valid_date',
            'CondicionesMeteorologicasID' => 'required|integer',
            'FundaID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Obtener datos del usuario
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Asignar el UsuarioID al crear la simulación
        $data['UsuarioID'] = $userData->user_id;

        // Insertar la nueva simulación
        $simulacionId = $this->simulacionesModel->insert($data);

        // Verificar si la inserción fue exitosa
        if ($simulacionId) {
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Simulación creada exitosamente.',
                'data' => $data
            ]);
        } else {
            return $this->failServerError("Error al crear la simulación.");
        }
    }

    // Actualizar una simulación existente
    public function update($id)
    {
        // Obtener los datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Obtener la simulación a actualizar
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }

        // Verificar si el usuario es el propietario o es admin
        if ($simulacion['UsuarioID'] !== $userData->user_id && $userData->rol !== 'admin') {
            return $this->failForbidden('No tienes permiso para actualizar esta simulación.');
        }

        // Obtener los datos del cuerpo de la solicitud (body)
        $data = $this->request->getJSON(true);

        // Validar los datos
        if (!$this->validate([
            'CondicionLuz' => 'required|string|max_length[50]',
            'Tiempo' => 'required|decimal',
            'CondicionesMeteorologicasID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la simulación
        $updated = $this->simulacionesModel->update($id, $data);

        if (!$updated) {
            return $this->failServerError('No se pudo actualizar la simulación.');
        }

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'Simulación actualizada exitosamente.',
            'data' => $data
        ]);
    }

    // Eliminar una simulación
    public function delete($id)
    {
        // Obtener los datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }

        // Obtener la simulación
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }

        // Verificar si el usuario es el propietario o es admin
        if ($simulacion['UsuarioID'] !== $userData->user_id && $userData->rol !== 'admin') {
            return $this->failForbidden('No tienes permiso para eliminar esta simulación.');
        }

        // Eliminar la simulación
        $this->simulacionesModel->delete($id);
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Simulación eliminada exitosamente.'
        ]);
    }
}
