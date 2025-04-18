<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProveedoresModel;
use App\Models\FundasProveedoresModel;

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
        $data = $this->request->getJSON(true);

        // Validar la entrada
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Pais' => 'required|min_length[3]|max_length[100]',
            'ContactoNombre' => 'required|min_length[3]|max_length[255]',
            'ContactoTelefono' => 'required|regex_match[/^\+[0-9]{1,4}[0-9]{7,15}$/]',
            'ContactoEmail' => 'required|valid_email',
            'SitioWeb' => 'permit_empty|valid_url',
            'Direccion' => 'required|min_length[3]|max_length[255]',
            'Descripcion' => 'required|min_length[10]',
            'FundaID' => 'required|is_array',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $processedData = [
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

        $proveedorID = $this->proveedoresModel->insert($processedData);
        $newProveedor = $this->proveedoresModel->find($proveedorID);

        // Relacionar proveedores con fundas
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
        $proveedor = $this->proveedoresModel->find($id);
        if (!$proveedor) {
            return $this->failNotFound("Proveedor no encontrado");
        }

        // Obtener datos de la solicitud como objeto JSON
        $data = $this->request->getJSON(true);  

        // Verificar si los datos están presentes
        if (empty($data)) {
            return $this->failValidationErrors('No se recibieron datos para actualizar');
        }

        // Validar la entrada
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Pais' => 'required|min_length[3]|max_length[100]',
            'ContactoNombre' => 'required|min_length[3]|max_length[255]',
            'ContactoTelefono' => 'required|regex_match[/^\+[0-9]{1,4}[0-9]{7,15}$/]',
            'ContactoEmail' => 'required|valid_email',
            'SitioWeb' => 'permit_empty|valid_url',
            'Direccion' => 'required|min_length[3]|max_length[255]',
            'Descripcion' => 'required|min_length[10]',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Procesar los datos antes de guardarlos
        $processedData = [
            'Nombre' => $data->Nombre,  // Acceso usando la notación de objeto
            'Pais' => $data->Pais,
            'ContactoNombre' => $data->ContactoNombre,
            'ContactoTelefono' => preg_replace('/[^0-9+]/', '', $data->ContactoTelefono),  // Eliminamos caracteres no numéricos
            'ContactoEmail' => strtolower($data->ContactoEmail),
            'SitioWeb' => !empty($data->SitioWeb) ? (preg_match('#^https?://#', $data->SitioWeb) ? $data->SitioWeb : 'https://' . $data->SitioWeb) : null,
            'Direccion' => $data->Direccion,
            'Descripcion' => $data->Descripcion,
        ];

        // Actualizar los datos del proveedor
        $this->proveedoresModel->update($id, $processedData);

        // Verificar que FundaID esté presente y sea un array no vacío
        $fundasSeleccionadas = !empty($data->FundaID) && is_array($data->FundaID) ? $data->FundaID : [];

        // Si no hay fundas seleccionadas, evitamos la inserción
        if (!empty($fundasSeleccionadas)) {
            // Eliminar relaciones anteriores de fundas para este proveedor
            $this->fundasProveedoresModel->deleteFundasByProveedor($id, $fundasSeleccionadas);

            // Preparar datos para insertar
            $dataRelaciones = [];
            foreach ($fundasSeleccionadas as $fundaID) {
                $dataRelaciones[] = [
                    'FundaID' => $fundaID,
                    'ProveedorID' => $id
                ];
            }

            // Si el array de relaciones no está vacío, hacemos el insert
            if (!empty($dataRelaciones)) {
                $this->fundasProveedoresModel->insertBatch($dataRelaciones);
            } else {
                return $this->failValidationErrors('No se proporcionaron fundas válidas.');
            }
        }

        return $this->respondUpdated([
            'status' => 'success',
            'message' => 'Proveedor actualizado correctamente'
        ]);
    }


    // DELETE /api/proveedores/{id}
    public function delete($id = null)
    {
        $proveedor = $this->proveedoresModel->find($id);
        if (!$proveedor) {
            return $this->failNotFound("Proveedor no encontrado");
        }

        $this->fundasProveedoresModel->deleteFundasByProveedor($id, []);
        $this->proveedoresModel->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Proveedor eliminado correctamente'
        ]);
    }
}
