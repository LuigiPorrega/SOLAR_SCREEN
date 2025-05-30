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

    // GET /api/modelosFundas?page=1&perPage=10
    public function index()
    {
        $perPage = $this->request->getGet('perPage') ?? 9;
        $page = $this->request->getGet('page') ?? 1;
        $tipoFunda = $this->request->getGet('tipoFunda');  // Puede ser 'fija' o 'expandible'

        $offset = ($page - 1) * $perPage;
        $total = $this->model->countAll();

        // Si tipoFunda es "fija" o "expandible", se filtra por Expansible
        if ($tipoFunda) {
            $expansible = ($tipoFunda == 'expandible') ? 1 : 0;
            $modelos = $this->model->where('Expansible', $expansible)->findAll($perPage, $offset);
        } else {
            $modelos = $this->model->findAll($perPage, $offset);
        }

        foreach ($modelos as &$modelo) {
            $url = $modelo['ImagenURL'] ?? '';

            // Normalizamos para asegurarnos de que se forme una URL completa
            if (!empty($url) && !str_starts_with($url, 'http')) {
                if ($url[0] !== '/') {
                    $url = '/' . $url;
                }

                $modelo['ImagenURL'] = base_url($url);
            }
        }

        return $this->respond([
            'status' => 'success',
            'data' => $modelos,
            'currentPage' => (int) $page,
            'perPage' => (int) $perPage,
            'totalItems' => $total,
            'totalPages' => ceil($total / $perPage),
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
        $data = $this->request->getJSON(true);

        // Validación con los nuevos campos incluidos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
            'Cantidad' => 'required|integer|greater_than_equal_to[0]',
            'Precio' => 'required|numeric|greater_than_equal_to[0]',
            'ProveedorID' => 'permit_empty'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (isset($data['ProveedorID']) && !is_array($data['ProveedorID'])) {
            $data['ProveedorID'] = [$data['ProveedorID']];
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
            'Cantidad' => 'required|integer|greater_than_equal_to[0]',
            'Precio' => 'required|numeric|greater_than_equal_to[0]',
            'ProveedorID' => 'permit_empty'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (isset($data['ProveedorID']) && !is_array($data['ProveedorID'])) {
            $data['ProveedorID'] = [$data['ProveedorID']];
        }

        $this->model->update($id, $data);

        // Actualizar relaciones con proveedores si vienen en el payload
        if (isset($data['ProveedorID'])) {
            $this->fundasProveedoresModel->where('FundaID', $id)->delete();

            // Asegurar que sea un array
            $proveedores = is_array($data['ProveedorID'])
                ? $data['ProveedorID']
                : [$data['ProveedorID']];

            $relaciones = array_map(fn($provID) => [
                'FundaID' => $id,
                'ProveedorID' => $provID
            ], $proveedores);

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
