<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CondicionesMeteorologicasModel;
use CodeIgniter\API\ResponseTrait;

class CondicionesMeteorologicasApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = CondicionesMeteorologicasModel::class;
    protected $format    = 'json';

   // GET /api/condicionesMeteorologicas?page=1
public function index()
{
    // Número de elementos por página (puedes ajustar este valor o tomarlo por query param si quieres)
    $perPage = $this->request->getGet('perPage') ?? 10;

    // Obtener datos paginados
    $data = $this->model->paginate($perPage);
    $pager = $this->model->pager;

    // Enviar respuesta con info de paginación
    return $this->respond([
        'status'       => 'success',
        'data'         => $data,
        'currentPage'  => $pager->getCurrentPage(),
        'perPage'      => $pager->getPerPage(),
        'totalItems'   => $pager->getTotal(),
        'totalPages'   => $pager->getPageCount(),
    ]);
}

    // GET /api/condicionesMeteorologicas/{id}
    public function view($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }
        return $this->respond($data);
    }

    // POST /api/condicionesMeteorologicas
    public function create()
    {
        $session = session();
        $data = $this->request->getJSON(true);

        if (!$this->validate([
            'Fecha' => 'required|valid_date',
            'LuzSolar' => 'required|numeric',
            'Temperatura' => 'required|numeric',
            'Humedad' => 'required|numeric',
            'Viento' => 'required|numeric',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data['UsuarioID'] = $session->get('userId');

        // Insertar datos en la base de datos
        $this->model->insert($data);

        // Devolver respuesta con mensaje adicional
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Condición meteorológica creada correctamente.',
            'data' => $data
        ]);
    }

    // PUT /api/condicionesMeteorologicas/{id}
    public function update($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }

        $input = $this->request->getJSON(true);

        if (!$this->validate([
            'Fecha' => 'required|valid_date',
            'LuzSolar' => 'required|numeric',
            'Temperatura' => 'required|numeric',
            'Humedad' => 'required|numeric',
            'Viento' => 'required|numeric',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Actualizar datos en la base de datos
        $this->model->update($id, $input);

        // Devolver respuesta con mensaje adicional
        return $this->respond([
            'status' => 'success',
            'message' => 'Condición meteorológica actualizada correctamente.',
            'data' => $input
        ]);
    }


    // DELETE /api/condicionesMeteorologicas/{id}
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound("Condición meteorológica con ID $id no encontrada.");
        }

        $this->model->delete($id);
        return $this->respondDeleted([
            'status' => 'success',
            "message" => "Condición meteorológica eliminada."
        ]);
    }
}
