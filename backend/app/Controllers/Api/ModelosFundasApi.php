<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ModelosFundasModel;
use App\Models\FundasProveedoresModel;

class ModelosFundasApi extends ResourceController
{
    protected $model;
    protected $fundasProveedoresModel;

    public function __construct()
    {
        $this->model = new ModelosFundasModel();
        $this->fundasProveedoresModel = new FundasProveedoresModel();
    }

    // GET /api/modelosFundas
    public function index()
    {
        $modelosFundas = $this->model->findAll();
        return $this->respond([
            'status' => 'success',
            'data' => $modelosFundas
        ]);
    }

    // GET /api/modelosFundas/{id}
    public function view($id = null)
    {
        $modeloFunda = $this->model->find($id);
        if (!$modeloFunda) {
            return $this->failNotFound("Modelo de funda no encontrado");
        }

        $proveedores = $this->fundasProveedoresModel->getProveedoresByFunda($id);

        return $this->respond([
            'status' => 'success',
            'data' => [
                'modeloFunda' => $modeloFunda,
                'proveedores' => $proveedores
            ]
        ]);
    }

    // POST /api/modelosFundas
    public function create()
    {
        $data = $this->request->getPost();

        // Validación con los nuevos campos incluidos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
            'Cantidad' => 'required|integer|min[0]',
            'Precio' => 'required|numeric|min[0]',
            'ProveedorID' => 'required|array|min_length[1]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data['FechaCreacion'] = date('Y-m-d H:i:s');

        $this->model->insert($data);
        $newModelId = $this->model->insertID();

        // Insertar relaciones con proveedores
        if (!empty($data['ProveedorID'])) {
            $relaciones = array_map(fn($id) => [
                'FundaID' => $newModelId,
                'ProveedorID' => $id
            ], $data['ProveedorID']);

            $this->fundasProveedoresModel->insertBatch($relaciones);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Modelo de funda creado correctamente',
            'data' => $data
        ]);
    }

    // PUT /api/modelosFundas/{id}
    public function update($id = null)
    {
        $modeloFunda = $this->model->find($id);
        if (!$modeloFunda) {
            return $this->failNotFound("Modelo de funda no encontrado");
        }

        $data = $this->request->getJSON(true);

        // Validación con los nuevos campos incluidos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
            'Cantidad' => 'required|integer|min[0]',
            'Precio' => 'required|numeric|min[0]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $this->model->update($id, $data);

        // Actualizar relaciones con proveedores si vienen en el payload
        if (isset($data['ProveedorID'])) {
            $this->fundasProveedoresModel->where('FundaID', $id)->delete();

            $relaciones = array_map(fn($provID) => [
                'FundaID' => $id,
                'ProveedorID' => $provID
            ], $data['ProveedorID']);

            $this->fundasProveedoresModel->insertBatch($relaciones);
        }

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'Modelo de funda actualizado correctamente',
            'data' => $data
        ]);
    }

    // DELETE /api/modelosFundas/{id}
    public function delete($id = null)
    {
        $modeloFunda = $this->model->find($id);
        if (!$modeloFunda) {
            return $this->failNotFound("Modelo de funda no encontrado");
        }

        $this->fundasProveedoresModel->where('FundaID', $id)->delete();
        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Modelo de funda eliminado correctamente'
        ]);
    }
}
