<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CarritoModel;
use CodeIgniter\API\ResponseTrait;

class CarritoApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = CarritoModel::class;
    protected $format = 'json';

    // GET /api/carrito
    public function index()
    {
        $user = session()->get('userData');
        $userId = $user->user_id;

        $items = $this->model
            ->select('Carrito.*, ModelosFundas.Nombre as NombreFunda')
            ->join('ModelosFundas', 'ModelosFundas.ID = Carrito.ModelosFundasId')
            ->where('UsuarioId', $userId)
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'data' => $items
        ]);
    }

    // POST /api/carrito
    public function add()
    {
        $user = session()->get('userData');
        $userId = $user->user_id;

        $data = $this->request->getJSON(true);

        if (!$this->validate([
            'ModelosFundasId' => 'required|integer',
            'Cantidad' => 'required|integer|greater_than_equal_to[1]',
            'Precio' => 'required|numeric|greater_than[0]'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $existente = $this->model
            ->where('UsuarioId', $userId)
            ->where('ModelosFundasId', $data['ModelosFundasId'])
            ->first();

        if ($existente) {
            $nuevaCantidad = $existente['Cantidad'] + $data['Cantidad'];
            $this->model->update($existente['ID'], ['Cantidad' => $nuevaCantidad]);
        } else {
            $this->model->insert([
                'UsuarioId' => $userId,
                'ModelosFundasId' => $data['ModelosFundasId'],
                'Cantidad' => $data['Cantidad'],
                'Precio' => $data['Precio'],
                'Creado_en' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Producto añadido al carrito'
        ]);
    }

    // PUT /api/carrito/{id}
    public function update($id = null)
    {
        $user = session()->get('userData');
        $userId = $user->user_id;

        $input = $this->request->getJSON(true);

        if (!$this->validate([
            'Cantidad' => 'required|integer|greater_than_equal_to[1]'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $item = $this->model
            ->where('ID', $id)
            ->where('UsuarioId', $userId)
            ->first();

        if (!$item) {
            return $this->failNotFound('Producto no encontrado en tu carrito.');
        }

        $this->model->update($id, ['Cantidad' => $input['Cantidad']]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Cantidad actualizada correctamente'
        ]);
    }

    // DELETE /api/carrito/{id}
    public function delete($id = null)
    {
        $user = session()->get('userData');
        $userId = $user->user_id;

        $item = $this->model
            ->where('ID', $id)
            ->where('UsuarioId', $userId)
            ->first();

        if (!$item) {
            return $this->failNotFound('Producto no encontrado en tu carrito.');
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Producto eliminado del carrito'
        ]);
    }
}
