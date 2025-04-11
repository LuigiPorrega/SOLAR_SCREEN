<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\IdeasModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;

class IdeasApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = IdeasModel::class;
    protected $format    = 'json';

    /**
     * Muestra todas las ideas.
     */
    public function index()
    {
        $ideas = $this->model->findAll();
        return $this->respond($ideas);
    }

    /**
     * Muestra una idea específica por ID.
     */
    public function view($id = null)
    {
        $idea = $this->model->find($id);

        if (!$idea) {
            return $this->failNotFound('Idea no encontrada con el ID: ' . $id);
        }

        return $this->respond($idea);
    }

    /**
     * Crea una nueva idea.
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
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
            'UsuarioID' => 'required|integer'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Aseguramos que el UsuarioID coincide con el usuario logueado
        $data['UsuarioID'] = $session->get('userId');

        // Agregar la fecha de creación
        $data['FechaCreacion'] = date('Y-m-d H:i:s');

        // Guardar la nueva idea
        $this->model->insert($data);

        return $this->respondCreated($data, 'Idea creada exitosamente.');
    }

    /**
     * Actualiza una idea existente.
     * Solo puede ser actualizada por el usuario que la creó o un admin.
     */
    public function update($id = null)
    {
        $session = session();
        $role = $session->get('role');
        $userId = $session->get('userId');

        // Verificar si la idea existe
        $idea = $this->model->find($id);
        if (!$idea) {
            return $this->failNotFound('Idea no encontrada con el ID: ' . $id);
        }

        // Verificar si el usuario tiene permiso para actualizar
        if ($role !== 'admin' && $idea['UsuarioID'] !== $userId) {
            return $this->failForbidden('No tienes permiso para modificar esta idea.');
        }

        // Obtener los datos de la solicitud PUT
        $data = $this->request->getJSON(true);

        // Validar los datos recibidos
        if (!$this->validate([
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la idea en la base de datos
        $this->model->update($id, $data);

        // Agregar un mensaje de éxito dentro de los datos
        $data['mensaje'] = 'Idea actualizada exitosamente.';

        // Responder con el código de estado 200 (OK) y los datos actualizados
        return $this->respond($data, 200);
    }

    /**
     * Elimina una idea existente.
     * Solo un admin puede eliminar ideas.
     */
    public function delete($id = null)
    {
        $session = session();
        $role = $session->get('role');

        // Verificar si la idea existe
        if (!$this->model->find($id)) {
            return $this->failNotFound('Idea no encontrada con el ID: ' . $id);
        }

        // Verificar si el usuario tiene permiso para eliminar
        if ($role !== 'admin') {
            return $this->failForbidden('Solo los administradores pueden eliminar ideas.');
        }

        // Eliminar la idea
        $this->model->delete($id);

        return $this->respondDeleted(['message' => 'Idea eliminada exitosamente.']);
    }
}
