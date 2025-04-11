<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProveedoresModel;
use App\Models\FundasProveedoresModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProveedoresApi extends ResourceController
{
    protected $proveedoresModel;
    protected $fundasProveedoresModel;

    public function __construct()
    {
        $this->proveedoresModel = new ProveedoresModel();
        $this->fundasProveedoresModel = new FundasProveedoresModel();
    }

    // GET /api/proveedores
    public function index()
    {
        $proveedores = $this->proveedoresModel->findAll();
        return $this->respond([
            'status' => 'success',
            'data' => $proveedores
        ]);
    }

    // GET /api/proveedores/{id}
    public function view($id = null)
    {
        $proveedor = $this->proveedoresModel->find($id);

        if (!$proveedor) {
            return $this->failNotFound("Proveedor no encontrado");
        }

        // Obtener las fundas asociadas a este proveedor
        $fundas = $this->fundasProveedoresModel->getFundasByProveedor($id);

        return $this->respond([
            'status' => 'success',
            'data' => [
                'proveedor' => $proveedor,
                'fundas' => $fundas
            ]
        ]);
    }

    // POST /api/proveedores
    public function create()
    {
        $session = session();

        // Verifica si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('No autenticado.');
        }

        // Verifica si el usuario tiene el rol adecuado (ej. Admin o Gerente)
        $role = $session->get('role');
        if ($role !== 'admin') {
            return $this->failForbidden('No tienes permiso para crear proveedores.');
        }

        $data = $this->request->getPost();

        // Validar los datos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Pais' => 'required|min_length[3]|max_length[100]',
            'ContactoNombre' => 'required|min_length[3]|max_length[255]',
            'ContactoTelefono' => 'required|regex_match[/^\+[0-9]{1,4}[0-9]{7,15}$/]', // Prefijo internacional y 7-15 dígitos
            'ContactoEmail' => 'required|valid_email',
            'SitioWeb' => 'permit_empty|valid_url',
            'Direccion' => 'required|min_length[3]|max_length[255]',
            'Descripcion' => 'required|min_length[10]',
            'FundaID' => 'required|is_array', // Validar que sea un array
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Procesar los datos del proveedor
        $data = [
            'Nombre' => $data['Nombre'],
            'Pais' => $data['Pais'],
            'ContactoNombre' => $data['ContactoNombre'],
            'ContactoTelefono' => preg_replace('/[^0-9]/', '', $data['ContactoTelefono']),
            'ContactoEmail' => strtolower($data['ContactoEmail']),
            'SitioWeb' => !empty($data['SitioWeb']) ? (preg_match('#^https?://#', $data['SitioWeb']) ? $data['SitioWeb'] : 'https://' . $data['SitioWeb']) : null,
            'Direccion' => $data['Direccion'],
            'Descripcion' => $data['Descripcion'],
            'FechaCreacion' => date('Y-m-d H:i:s'),
            'Activo' => 1,
        ];

        // Guardar el proveedor
        $proveedorID = $this->proveedoresModel->insert($data);
        $newProveedor = $this->proveedoresModel->find($proveedorID);

        // Asociar las fundas seleccionadas
        $fundasSeleccionadas = $data['FundaID'] ?? [];
        if (!empty($fundasSeleccionadas)) {
            $dataRelaciones = [];
            foreach ($fundasSeleccionadas as $fundaID) {
                $dataRelaciones[] = [
                    'FundaID' => $fundaID,
                    'ProveedorID' => $proveedorID
                ];
            }
            $this->fundasProveedoresModel->insertBatch($dataRelaciones);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Proveedor creado correctamente',
            'data' => $newProveedor
        ]);
    }

    // PUT /api/proveedores/{id}
    public function update($id = null)
    {
        $session = session();
        $role = $session->get('role');

        // Verificar si el proveedor existe
        $proveedor = $this->proveedoresModel->find($id);
        if (!$proveedor) {
            return $this->failNotFound("Proveedor no encontrado");
        }

        // Verificar si el usuario tiene permisos para actualizar
        if ($role !== 'admin') {
            return $this->failForbidden('Solo los administradores pueden actualizar proveedores.');
        }

        $data = $this->request->getRawInput();

        // Validar los datos
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Pais' => 'required|min_length[3]|max_length[100]',
            'ContactoNombre' => 'required|min_length[3]|max_length[255]',
            'ContactoTelefono' => 'required|regex_match[/^\+[0-9]{1,4}[0-9]{7,15}$/]', // Prefijo internacional y 7-15 dígitos
            'ContactoEmail' => 'required|valid_email',
            'SitioWeb' => 'permit_empty|valid_url',
            'Direccion' => 'required|min_length[3]|max_length[255]',
            'Descripcion' => 'required|min_length[10]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Procesar los datos
        $data = [
            'Nombre' => $data['Nombre'],
            'Pais' => $data['Pais'],
            'ContactoNombre' => $data['ContactoNombre'],
            'ContactoTelefono' => preg_replace('/[^0-9]/', '', $data['ContactoTelefono']),
            'ContactoEmail' => strtolower($data['ContactoEmail']),
            'SitioWeb' => !empty($data['SitioWeb']) ? (preg_match('#^https?://#', $data['SitioWeb']) ? $data['SitioWeb'] : 'https://' . $data['SitioWeb']) : null,
            'Direccion' => $data['Direccion'],
            'Descripcion' => $data['Descripcion'],
        ];

        // Actualizar el proveedor
        $this->proveedoresModel->update($id, $data);

        // Actualizar fundas asociadas
        $fundasSeleccionadas = $data['FundaID'] ?? [];
        $this->fundasProveedoresModel->deleteFundasByProveedor($id, $fundasSeleccionadas);

        $dataRelaciones = [];
        foreach ($fundasSeleccionadas as $fundaID) {
            $dataRelaciones[] = [
                'FundaID' => $fundaID,
                'ProveedorID' => $id
            ];
        }
        $this->fundasProveedoresModel->insertBatch($dataRelaciones);

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'Proveedor actualizado correctamente'
        ]);
    }

    // DELETE /api/proveedores/{id}
    public function delete($id = null)
    {
        $session = session();
        $role = $session->get('role');

        // Verificar si el proveedor existe
        $proveedor = $this->proveedoresModel->find($id);
        if (!$proveedor) {
            return $this->failNotFound("Proveedor no encontrado");
        }

        // Verificar si el usuario tiene permisos para eliminar
        if ($role !== 'admin') {
            return $this->failForbidden('Solo los administradores pueden eliminar proveedores.');
        }

        // Eliminar TODAS las relaciones con fundas (pasamos array vacío)
        $this->fundasProveedoresModel->deleteFundasByProveedor($id, []);

        // Eliminar proveedor
        $this->proveedoresModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Proveedor eliminado correctamente'
        ]);
    }
}
