<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SimulacionesModel;
use CodeIgniter\API\ResponseTrait;

class SimulacionesApi extends BaseController
{
    use ResponseTrait;

    protected $simulacionesModel;

    public function __construct()
    {
        $this->simulacionesModel = new SimulacionesModel();
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
            'UsuarioID' => 'required|integer',
            'CondicionLuz' => 'required|string|max_length[255]',
            'EnergiaGenerada' => 'required|numeric',
            'Tiempo' => 'required|numeric',
            'Fecha' => 'required|valid_date',
            'CondicionesMeteorologicasID' => 'required|integer',
            'FundaID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Si la validación pasa, proceder con la inserción
        try {
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
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return $this->failServerError("Error al crear la simulación: " . $e->getMessage());
        }
    }


    // Actualizar una simulación existente
    public function update($id)
    {
        // Obtener los datos del usuario desde la sesión
        $usuario = session()->get('userData');

        // Verificar si el usuario tiene rol 'admin'
        if ($usuario && $usuario->rol !== 'admin') {
            return $this->failForbidden('No tienes permiso para actualizar simulaciones.');
        }

        // Obtener la simulación a actualizar
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }

        // Obtener los datos del cuerpo de la solicitud (body)
        $data = $this->request->getJSON(true); // `true` convierte el JSON a un array

        // Validar los datos
        if (!$this->validate([
            'CondicionLuz' => 'required|string|max_length[50]',
            'Tiempo' => 'required|decimal',
            'CondicionesMeteorologicasID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Asegurarse de que el ID esté incluido en los datos para la actualización
        $data['ID'] = $id; // Es posible que el ID venga en el cuerpo de la solicitud, pero se asegura de que esté presente

        // Actualizar la simulación
        $updated = $this->simulacionesModel->update($id, $data);

        // Verificar si la actualización fue exitosa
        if (!$updated) {
            return $this->failServerError('No se pudo actualizar la simulación.');
        }

        return $this->respondUpdated('Simulación actualizada exitosamente');
    }


    // Eliminar una simulación
    public function delete($id)
    {
        // Obtener los datos del usuario desde la sesión
        $usuario = session()->get('userData');

        // Verificar si el usuario tiene rol 'admin'
        if ($usuario->rol !== 'admin') {
            return $this->failForbidden('No tienes permiso para eliminar simulaciones.');
        }

        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }

        // Eliminar la simulación
        $this->simulacionesModel->delete($id);
        return $this->respondDeleted('Simulación eliminada exitosamente');
    }
}
