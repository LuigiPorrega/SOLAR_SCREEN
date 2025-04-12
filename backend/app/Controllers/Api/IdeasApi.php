<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\IdeasModel;
use CodeIgniter\API\ResponseTrait;

class IdeasApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = IdeasModel::class;
    protected $format    = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function view($id = null)
    {
        $idea = $this->model->find($id);
        if (!$idea) {
            return $this->failNotFound("Idea no encontrada con el ID: $id");
        }
        return $this->respond($idea);
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

        // Asignar la fecha de creación
        $data['FechaCreacion'] = date('Y-m-d H:i:s');

        // Insertar en la base de datos
        $this->model->insert($data);

        // Devolver respuesta con mensaje
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

        // Devolver respuesta con mensaje
        return $this->respond([
            'status' => 'success',
            'message' => 'Idea actualizada exitosamente.',
            'data' => $data
        ]);
    }



    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound("Idea no encontrada con el ID: $id");
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'message' => 'Idea eliminada exitosamente.'
        ]);
    }
}
