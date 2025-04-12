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

        // Validación con ProveedorID obligatorio
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
            'ProveedorID' => 'required|array|min_length[1]', // Asegura que sea obligatorio
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Establecer valores predeterminados si no están en la solicitud
        $data['FechaCreacion'] = date('Y-m-d H:i:s');
        $data['UsuarioID'] = session()->get('userId') ?: 1; // Si no hay sesión, usar un valor por defecto

        // Insertar el nuevo modelo de funda
        $this->model->insert($data);
        $newModelId = $this->model->insertID();

        // Relacionar la nueva funda con los proveedores especificados
        if (!empty($data['ProveedorID'])) {
            $relaciones = [];
            foreach ($data['ProveedorID'] as $proveedorID) {
                $relaciones[] = [
                    'FundaID' => $newModelId,
                    'ProveedorID' => $proveedorID
                ];
            }
            $this->fundasProveedoresModel->insertBatch($relaciones); // Insertar en la tabla intermedia
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

        // Validación
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar la funda
        $this->model->update($id, $data);

        // Actualizar la relación con los proveedores
        if (isset($data['ProveedorID']) && !empty($data['ProveedorID'])) {
            // Eliminar las relaciones existentes
            $this->fundasProveedoresModel->where('FundaID', $id)->delete();

            // Insertar las nuevas relaciones
            $relaciones = [];
            foreach ($data['ProveedorID'] as $proveedorID) {
                $relaciones[] = [
                    'FundaID' => $id,
                    'ProveedorID' => $proveedorID
                ];
            }
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

        // Eliminar las relaciones en la tabla intermedia
        $this->fundasProveedoresModel->where('FundaID', $id)->delete();
        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Modelo de funda eliminado correctamente'
        ]);
    }
}
