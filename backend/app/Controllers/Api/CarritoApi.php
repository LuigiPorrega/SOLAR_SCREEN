<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CarritoModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CarritoApi extends ResourceController
{
    use ResponseTrait;

    protected $modelName = CarritoModel::class;
    protected $format = 'json';

    // Función para obtener los datos del usuario desde el JWT
    private function getUserDataFromToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $key = getenv('JWT_SECRET') ?: 'clave_secreta_demo'; // Usa la clave secreta desde el env

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }

    // GET /api/carrito
    public function index()
    {
        // Obtener datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }
        $userId = $userData->user_id;

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
        // Obtener datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }
        $userId = $userData->user_id;

        $data = $this->request->getJSON(true);

        // Validar los datos de entrada
        if (!$this->validate([
            'ModelosFundasId' => 'required|integer',
            'Cantidad' => 'required|integer|greater_than_equal_to[1]',
            'Precio' => 'required|numeric|greater_than[0]'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Verificar si el producto ya está en el carrito
        $existente = $this->model
            ->where('UsuarioId', $userId)
            ->where('ModelosFundasId', $data['ModelosFundasId'])
            ->first();

        if ($existente) {
            // Si el producto ya existe, actualizar la cantidad
            $nuevaCantidad = $existente['Cantidad'] + $data['Cantidad'];
            $this->model->update($existente['ID'], ['Cantidad' => $nuevaCantidad]);

            $actualizado = $this->model
                ->select('Carrito.*, ModelosFundas.Nombre as NombreFunda')
                ->join('ModelosFundas', 'ModelosFundas.ID = Carrito.ModelosFundasId')
                ->where('Carrito.ID', $existente['ID'])
                ->first();

            return $this->respond([
                'status' => 'success',
                'message' => 'Cantidad actualizada en el carrito',
                'data' => $actualizado
            ]);
        } else {
            // Si el producto no está en el carrito, agregarlo
            $this->model->insert([
                'UsuarioId' => $userId,
                'ModelosFundasId' => $data['ModelosFundasId'],
                'Cantidad' => $data['Cantidad'],
                'Precio' => $data['Precio'],
                'Creado_en' => date('Y-m-d H:i:s')
            ]);

            $insertId = $this->model->insertID();

            $nuevo = $this->model
                ->select('Carrito.*, ModelosFundas.Nombre as NombreFunda')
                ->join('ModelosFundas', 'ModelosFundas.ID = Carrito.ModelosFundasId')
                ->where('Carrito.ID', $insertId)
                ->first();

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Producto añadido al carrito',
                'data' => $nuevo
            ]);
        }
    }

    // PUT /api/carrito/{id}
    public function update($id = null)
    {
        // Obtener datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }
        $userId = $userData->user_id;

        // Obtener los datos del cuerpo de la solicitud
        $input = $this->request->getJSON(true);

        // Validar la cantidad
        if (!$this->validate([
            'Cantidad' => 'required|integer|greater_than_equal_to[1]'
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Verificar si el producto está en el carrito del usuario
        $item = $this->model
            ->where('ID', $id)
            ->where('UsuarioId', $userId)
            ->first();

        if (!$item) {
            return $this->failNotFound('Producto no encontrado en tu carrito.');
        }

        // Actualizar la cantidad
        $this->model->update($id, ['Cantidad' => $input['Cantidad']]);

        $actualizado = $this->model
            ->select('Carrito.*, ModelosFundas.Nombre as NombreFunda')
            ->join('ModelosFundas', 'ModelosFundas.ID = Carrito.ModelosFundasId')
            ->where('Carrito.ID', $id)
            ->first();

        return $this->respond([
            'status' => 'success',
            'message' => 'Cantidad actualizada correctamente',
            'data' => $actualizado
        ]);
    }

    // DELETE /api/carrito/{id}
    public function delete($id = null)
    {
        // Obtener datos del usuario desde el JWT
        $userData = $this->getUserDataFromToken();
        if (!$userData) {
            return $this->failUnauthorized('No autorizado. Token inválido o expirado');
        }
        $userId = $userData->user_id;

        // Verificar si el producto está en el carrito del usuario
        $item = $this->model
            ->where('ID', $id)
            ->where('UsuarioId', $userId)
            ->first();

        if (!$item) {
            return $this->failNotFound('Producto no encontrado en tu carrito.');
        }

        // Eliminar el producto del carrito
        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Producto eliminado del carrito'
        ]);
    }
}
