<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\SimulacionesModel;
use App\Models\UsuariosModel;
use App\Exceptions\PermissionException;
use CodeIgniter\HTTP\RedirectResponse;

class Simulaciones extends BaseController
{
    protected $simulacionesModel;
    protected $usuariosModel;

    /**
     * Constructor del controlador.
     * Inicializa los modelos necesarios para las operaciones del controlador.
     */
    public function __construct()
    {
        $this->simulacionesModel = new SimulacionesModel();
        $this->usuariosModel = new UsuariosModel();
    }

    /**
     * Verifica si el usuario actual tiene acceso de administrador.
     * Lanza una excepción si el usuario no tiene los permisos necesarios.
     *
     * @throws PermissionException
     */
    private function checkAdminAccess()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId || !$this->usuariosModel->canAccessBackend($userId)) {
            throw PermissionException::forUnauthorizedAccess();
        }
    }

    /**
     * Muestra la lista de todas las simulaciones.
     *
     * @return string Vista renderizada con la lista de simulaciones
     */
    public function index()
    {
        $data = [
            'simulaciones' => $this->simulacionesModel->getSimulaciones(null, 10),
            'pager' => $this->simulacionesModel->pager,
            'title' => 'Lista de Simulaciones',
        ];

        return view('templates/header', $data)
            . view('simulaciones/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de una simulación específica.
     *
     * @param int|null $id ID de la simulación a mostrar
     * @return string Vista renderizada con los detalles de la simulación
     * @throws PageNotFoundException Si no se encuentra la simulación
     */
    public function view($id = null)
    {
        $simulacion = $this->simulacionesModel->getSimulaciones($id);

        if (!$simulacion) {
            throw new PageNotFoundException("No se encontró la simulación con ID: $id");
        }

        $data = [
            'simulacion' => $simulacion,
            'title' => 'Detalle de la Simulación',
        ];

        return view('templates/header', $data)
            . view('simulaciones/view')
            . view('templates/footer');
    }

    /**
     * Muestra el formulario para crear una nueva simulación.
     *
     * @return string Vista renderizada con el formulario de creación
     */
    public function new()
    {
        $this->checkAdminAccess();

        helper('form');
        return view('templates/header', ['title' => 'Crear Simulación'])
            . view('simulaciones/create')
            . view('templates/footer');
    }

    /**
     * Procesa la creación de una nueva simulación.
     *
     * @return RedirectResponse Redirección a la lista de simulaciones
     */
    public function create()
    {
        $this->checkAdminAccess();

        helper('form');

        $data = $this->request->getPost(['UsuarioID', 'CondicionLuz', 'EnergiaGenerada']);

        if (!$this->validateData($data, [
            'UsuarioID' => 'required|integer',
            'CondicionLuz' => 'required|string|max_length[50]',
            'EnergiaGenerada' => 'required|decimal',
        ])) {
            // Si la validación falla, redirigir de vuelta al formulario con los datos y errores
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->simulacionesModel->save($data);

        // Redirigir a la lista de simulaciones con un mensaje de éxito
        return redirect()->to(base_url('simulaciones'))->with('success', 'Simulación creada exitosamente.');
    }


    /**
     * Muestra el formulario para editar una simulación existente.
     *
     * @param int $id ID de la simulación a editar
     * @return string Vista renderizada con el formulario de edición
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        helper('form');

        // Buscar la simulación por ID
        $simulacion = $this->simulacionesModel->find($id);

        if (!$simulacion) {
            throw new PageNotFoundException("No se encontró la simulación con ID: $id");
        }

        // Pasar los datos al formulario
        $data = [
            'simulacion' => $simulacion,
            'title' => 'Editar Simulación',
        ];

        return view('templates/header', $data)
            . view('simulaciones/edit')
            . view('templates/footer');
    }

    /**
     * Procesa la actualización de una simulación existente.
     *
     * @param int $id ID de la simulación a actualizar
     * @return RedirectResponse Redirección a la lista de simulaciones
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtener los datos enviados por el formulario
        $data = $this->request->getPost(['UsuarioID', 'CondicionLuz', 'EnergiaGenerada']);

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'UsuarioID' => 'required|integer',
            'CondicionLuz' => 'required|string|max_length[50]',
            'EnergiaGenerada' => 'required|decimal',
        ])) {
            return redirect()->to(base_url("admin/simulaciones/update/$id"));
        }

        // Incluir el ID en los datos para actualizar el registro existente
        $data['ID'] = (int)$id;

        if (!$this->simulacionesModel->save($data)) {
            throw new \RuntimeException("Error al actualizar la simulación.");
        }

        return redirect()->to(base_url('simulaciones'));
    }

    /**
     * Elimina una simulación existente.
     *
     * @param int $id ID de la simulación a eliminar
     * @return RedirectResponse Redirección a la lista de simulaciones
     */
    public function delete($id)
    {
        $this->checkAdminAccess();

        if (!$this->simulacionesModel->delete($id)) {
            throw new PageNotFoundException("No se encontró la simulación con ID: $id");
        }

        return redirect()->to(base_url('simulaciones'));
    }
}
