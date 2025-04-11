<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CondicionesMeteorologicasModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;

class CondicionesMeteorologicasApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = CondicionesMeteorologicasModel::class;
    protected $format    = 'json';

    /**
     * Muestra todas las condiciones meteorológicas.
     */
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    /**
     * Muestra una condición meteorológica específica por ID.
     */
    public function view($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }
        return $this->respond($data);
    }

    /**
     * Crea una nueva condición meteorológica.
     * Requiere que el usuario esté logueado.
     */
    public function create()
    {
        $session = session();

        // Verifica si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('No autenticado.');
        }

        $data = $this->request->getJSON(true);

        // Validación de los datos recibidos
        if (!$this->validate([
            'Fecha' => 'required|valid_date',
            'LuzSolar' => 'required|numeric',
            'Temperatura' => 'required|numeric',
            'Humedad' => 'required|numeric',
            'Viento' => 'required|numeric',
            'UsuarioID' => 'required|integer',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Aseguramos que el UsuarioID coincide con el usuario logueado
        $data['UsuarioID'] = $session->get('userId');

        // Insertar la nueva condición meteorológica
        $this->model->insert($data);
        return $this->respondCreated($data);
    }

    /**
     * Actualiza una condición meteorológica existente.
     * Solo puede ser actualizada por el usuario que la creó o un admin.
     */
    public function update($id = null)
    {
        $session = session();
        $role = $session->get('role');
        $userId = $session->get('userId');

        // Verificar si la condición meteorológica existe
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }

        // Verificar si el usuario tiene permiso para actualizar
        if ($role !== 'admin' && $data['UsuarioID'] !== $userId) {
            return $this->failForbidden('No tienes permiso para modificar esta condición meteorológica.');
        }

        // Obtener los datos de la solicitud PUT
        $dataToUpdate = $this->request->getJSON(true);

        // Validar los datos recibidos
        if (!$this->validate([
            'Fecha' => 'required|valid_date',
            'LuzSolar' => 'required|numeric',
            'Temperatura' => 'required|numeric',
            'Humedad' => 'required|numeric',
            'Viento' => 'required|numeric',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la condición meteorológica en la base de datos
        $this->model->update($id, $dataToUpdate);
        return $this->respond($dataToUpdate);
    }

    /**
     * Elimina una condición meteorológica existente.
     * Solo un admin puede eliminar condiciones meteorológicas.
     */
    public function delete($id = null)
    {
        $session = session();
        $role = $session->get('role');

        // Verificar si la condición meteorológica existe
        if (!$this->model->find($id)) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }

        // Verificar si el usuario tiene permiso para eliminar
        if ($role !== 'admin') {
            return $this->failForbidden('Solo los administradores pueden eliminar condiciones meteorológicas.');
        }

        // Eliminar la condición meteorológica
        $this->model->delete($id);
        return $this->respondDeleted(["message" => "Condición meteorológica eliminada."]);
    }
}
