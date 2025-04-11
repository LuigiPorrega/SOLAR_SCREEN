<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ModelosFundasModel;
use App\Models\FundasProveedoresModel;
use CodeIgniter\HTTP\ResponseInterface;

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

        if ($modeloFunda) {
            // Obtener los proveedores asociados al modelo de funda
            $proveedores = $this->fundasProveedoresModel->getProveedoresByFunda($id);
            return $this->respond([
                'status' => 'success',
                'data' => [
                    'modeloFunda' => $modeloFunda,
                    'proveedores' => $proveedores
                ]
            ]);
        } else {
            return $this->failNotFound("Modelo de funda no encontrado");
        }
    }

    // POST /api/modelosFundas
    public function create()
    {
        $session = session();

        // Verifica si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('No autenticado.');
        }

        $data = $this->request->getPost();

        // Validar datos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Guardar el nuevo modelo de funda
        $modelData = [
            'Nombre' => $data['Nombre'],
            'Tamaño' => $data['Tamaño'],
            'CapacidadCarga' => $data['CapacidadCarga'],
            'Expansible' => $data['Expansible'],
            'TipoFunda' => $data['TipoFunda'],
            'FechaCreacion' => date('Y-m-d H:i:s')
        ];

        $this->model->insert($modelData);
        $newModelId = $this->model->insertID();

        // Asociar proveedores (si existen)
        if (!empty($data['ProveedorID'])) {
            $dataRelaciones = [];
            foreach ($data['ProveedorID'] as $proveedorID) {
                $dataRelaciones[] = [
                    'FundaID' => $newModelId,
                    'ProveedorID' => $proveedorID
                ];
            }
            $this->fundasProveedoresModel->insertBatch($dataRelaciones);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Modelo de funda creado correctamente',
            'data' => $modelData
        ]);
    }

    // PUT /api/modelosFundas/{id}
    public function update($id = null)
    {
        $session = session();
        $role = $session->get('role');
        $userId = $session->get('userId');

        // Verificar si el modelo de funda existe
        $modeloFunda = $this->model->find($id);
        if (!$modeloFunda) {
            return $this->failNotFound("Modelo de funda no encontrado");
        }

        // Verificar si el usuario tiene permisos para actualizar
        if ($role !== 'admin' && $modeloFunda['UsuarioID'] !== $userId) {
            return $this->failForbidden('No tienes permiso para actualizar este modelo de funda.');
        }

        $data = $this->request->getRawInput();

        // Validar los datos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $modelData = [
            'Nombre' => $data['Nombre'],
            'Tamaño' => $data['Tamaño'],
            'CapacidadCarga' => $data['CapacidadCarga'],
            'Expansible' => $data['Expansible'],
            'TipoFunda' => $data['TipoFunda'],
        ];

        $this->model->update($id, $modelData);

        // Actualizar proveedores
        if (!empty($data['ProveedorID'])) {
            // Eliminar las relaciones anteriores
            $this->fundasProveedoresModel->where('FundaID', $id)->delete();

            // Insertar nuevas relaciones
            $dataRelaciones = [];
            foreach ($data['ProveedorID'] as $proveedorID) {
                $dataRelaciones[] = [
                    'FundaID' => $id,
                    'ProveedorID' => $proveedorID
                ];
            }
            $this->fundasProveedoresModel->insertBatch($dataRelaciones);
        }

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'Modelo de funda actualizado correctamente',
            'data' => $modelData
        ]);
    }

    // DELETE /api/modelosFundas/{id}
    public function delete($id = null)
    {
        $session = session();
        $role = $session->get('role');

        // Verificar si el modelo de funda existe
        $modeloFunda = $this->model->find($id);
        if (!$modeloFunda) {
            return $this->failNotFound("Modelo de funda no encontrado");
        }

        // Verificar si el usuario tiene permisos para eliminar
        if ($role !== 'admin') {
            return $this->failForbidden('Solo los administradores pueden eliminar modelos de funda.');
        }

        // Eliminar las relaciones con proveedores
        $this->fundasProveedoresModel->where('FundaID', $id)->delete();

        // Eliminar el modelo de funda
        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Modelo de funda eliminado correctamente'
        ]);
    }
}
