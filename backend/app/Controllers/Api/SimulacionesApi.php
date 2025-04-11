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
        $session = session();

        // Verificar si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('No autenticado.');
        }

        // Verificar si el usuario tiene el rol adecuado (ej. Admin o Usuario con permisos)
        $role = $session->get('role');
        if ($role !== 'admin') {
            return $this->failForbidden('No tienes permiso para crear simulaciones.');
        }

        $data = $this->request->getPost();

        // Validar los datos del formulario
        if (!$this->validate([
            'CondicionLuz' => 'required|string|max_length[50]',
            'Tiempo' => 'required|decimal',
            'CondicionesMeteorologicasID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $this->simulacionesModel->save($data);
        return $this->respondCreated('Simulación creada exitosamente');
    }

    // Actualizar una simulación existente
    public function update($id)
    {
        $session = session();

        // Verificar si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('No autenticado.');
        }

        // Verificar si el usuario tiene el rol adecuado (ej. Admin o Usuario con permisos)
        $role = $session->get('role');
        if ($role !== 'admin') {
            return $this->failForbidden('No tienes permiso para actualizar simulaciones.');
        }

        // Verificar si la simulación existe
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }

        // Obtener datos crudos desde el body de la petición
        $data = $this->request->getRawInput();

        // Validar los datos recibidos
        if (!$this->validate([
            'CondicionLuz' => 'required|string|max_length[50]',
            'Tiempo' => 'required|decimal',
            'CondicionesMeteorologicasID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la simulación con los nuevos datos
        $this->simulacionesModel->update($id, $data);
        return $this->respondUpdated('Simulación actualizada exitosamente');
    }

    // Eliminar una simulación
    public function delete($id)
    {
        $session = session();

        // Verificar si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('No autenticado.');
        }

        // Verificar si el usuario tiene el rol adecuado (ej. Admin)
        $role = $session->get('role');
        if ($role !== 'admin') {
            return $this->failForbidden('No tienes permiso para eliminar simulaciones.');
        }

        // Verificar si la simulación existe
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return $this->failNotFound('Simulación no encontrada');
        }

        // Eliminar la simulación
        $this->simulacionesModel->delete($id);
        return $this->respondDeleted('Simulación eliminada exitosamente');
    }
}
