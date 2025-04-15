<?php

namespace App\Controllers;

use App\Models\CarritoModel;
use App\Models\ModelosFundasModel;
use App\Exceptions\PermissionException;
use CodeIgniter\Exceptions\PageNotFoundException;

class Carrito extends BaseController
{
    protected $carritoModel;
    protected $fundasModel;

    public function __construct()
    {
        $this->carritoModel = new CarritoModel();
        $this->fundasModel = new ModelosFundasModel();
    }

    private function checkLogin()
    {
        if (!session()->has('user_id')) {
            throw PermissionException::forUnauthorizedAccess();
        }
    }

    /**
     * Mostrar el contenido del carrito
     */

    public function index()
    {
        $this->checkLogin();
        $userId = session()->get('user_id');

        $carrito = $this->carritoModel
            ->select('Carrito.*, ModelosFundas.Nombre AS NombreFunda')
            ->join('ModelosFundas', 'ModelosFundas.ID = Carrito.ModelosFundasId')
            ->where('UsuarioId', $userId)
            ->orderBy('Carrito.Creado_en', 'desc')
            ->findAll();

        $data = [
            'title' => 'Mi Carrito',
            'carrito' => $carrito
        ];

        return view('templates/header', $data)
            . view('carrito/index', $data)
            . view('templates/footer');
    }



    /**
     * Añadir producto al carrito o actualizar cantidad si ya existe
     */
    public function add()
    {
        $this->checkLogin(); // Verificar que el usuario esté autenticado
        $userId = session()->get('user_id');

        $modelosFundasId = $this->request->getPost('ModelosFundasId');
        $cantidad        = (int) $this->request->getPost('Cantidad');
        $precio          = $this->request->getPost('Precio');

        // Verificar si la cantidad y el precio son válidos
        if ($cantidad <= 0 || $precio <= 0) {
            return redirect()->back()->with('error', 'La cantidad y el precio deben ser mayores que 0.');
        }

        // Ver si ya hay una entrada en el carrito con ese producto
        $existing = $this->carritoModel
            ->where('UsuarioId', $userId)
            ->where('ModelosFundasId', $modelosFundasId)
            ->first();

        try {
            if ($existing) {
                // Si ya existe, actualizamos la cantidad
                $newCantidad = $existing['Cantidad'] + $cantidad;
                $this->carritoModel->update($existing['ID'], [
                    'Cantidad' => $newCantidad,
                ]);
            } else {
                // Si no, lo insertamos como nuevo
                $this->carritoModel->insert([
                    'UsuarioId' => $userId,
                    'ModelosFundasId' => $modelosFundasId,
                    'Cantidad' => $cantidad,
                    'Precio' => $precio,
                    'Creado_en' => date('Y-m-d H:i:s'),
                ]);
            }

            return redirect()->to(base_url('admin/carrito'))->with('success', 'Producto añadido al carrito.');
        } catch (\Exception $e) {
            return redirect()->to(base_url('admin/carrito'))->with('error', 'Hubo un error al añadir el producto al carrito.');
        }
    }

    /**
     * Actualizar cantidad de un producto del carrito
     */
    public function update($id)
    {
        $this->checkLogin();
        $userId = session()->get('user_id');

        $item = $this->carritoModel->find($id);

        if (!$item || $item['UsuarioId'] != $userId) {
            throw new PageNotFoundException("Producto no encontrado.");
        }

        $nuevaCantidad = (int) $this->request->getPost('Cantidad');
        if ($nuevaCantidad < 1) {
            return redirect()->back()->with('error', 'La cantidad debe ser al menos 1.');
        }

        $this->carritoModel->update($id, ['Cantidad' => $nuevaCantidad]);

        return redirect()->to(base_url('admin/carrito'))->with('success', 'Cantidad actualizada.');
    }

    /**
     * Eliminar un producto del carrito
     */
    public function delete($id)
    {
        $this->checkLogin();
        $userId = session()->get('user_id');

        $item = $this->carritoModel->find($id);
        if (!$item || $item['UsuarioId'] != $userId) {
            return redirect()->to(base_url('admin/carrito'))->with('error', 'Producto no encontrado.');
        }

        $this->carritoModel->delete($id);

        return redirect()->to(base_url('admin/carrito'))->with('success', 'Producto eliminado del carrito.');
    }

    public function vaciarCarrito()
    {
        $userId = session()->get('user_id');

        // Eliminar todos los elementos del carrito del usuario
        $carritoModel = new CarritoModel();
        $carritoModel->where('UsuarioId', $userId)->delete();

        // Redirigir con mensaje de éxito
        return redirect()->to(base_url('admin/carrito'))->with('success', 'El carrito ha sido vaciado exitosamente.');
    }
}
