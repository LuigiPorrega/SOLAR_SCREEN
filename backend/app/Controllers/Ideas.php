<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\IdeasModel;
use App\Models\UsuariosModel;
use App\Exceptions\PermissionException;
use CodeIgniter\HTTP\RedirectResponse;

class Ideas extends BaseController
{
    protected $ideasModel;
    protected $usuariosModel;

    /**
     * Constructor del controlador.
     * Inicializa los modelos necesarios para las operaciones del controlador.
     */
    public function __construct()
    {
        $this->ideasModel = new IdeasModel();
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
     * Muestra la lista de todas las ideas.
     * Esta función es accesible para todos los usuarios.
     *
     * @return string Vista renderizada con la lista de ideas
     */
    public function index()
    {
        $data = [
            'ideas' => $this->ideasModel->getIdeas(null, 10), // Obtiene ideas con paginación (10 por página)
            'pager' => $this->ideasModel->pager, // Objeto de paginación
            'title' => 'Lista de Ideas',
        ];

        return view('templates/header', $data)
            . view('ideas/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de una idea específica.
     * Esta función es accesible para todos los usuarios.
     *
     * @param int|null $id ID de la idea a mostrar
     * @return string Vista renderizada con los detalles de la idea
     * @throws PageNotFoundException Si no se encuentra la idea
     */
    public function view($id = null)
    {
        $idea = $this->ideasModel->getIdeas($id);

        if (!$idea) {
            throw new PageNotFoundException("No se encontró la idea con ID: $id");
        }

        $data = [
            'idea' => $idea,
            'title' => 'Detalle de la Idea',
        ];

        return view('templates/header', $data)
            . view('ideas/view')
            . view('templates/footer');
    }

    /**
     * Muestra el formulario para crear una nueva idea.
     * Esta función requiere acceso de administrador.
     *
     * @return string Vista renderizada con el formulario de creación
     */
    public function new()
    {
        $this->checkAdminAccess();

        helper('form');
        return view('templates/header', ['title' => 'Crear Idea'])
            . view('ideas/create')
            . view('templates/footer');
    }

    /**
     * Procesa la creación de una nueva idea.
     * Esta función requiere acceso de administrador.
     *
     * @return RedirectResponse Redirección a la lista de ideas si la creación es exitosa
     */
    public function create()
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtener datos enviados por el formulario
        $data = $this->request->getPost(['UsuarioID', 'Titulo', 'Descripcion']);

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'UsuarioID' => 'required|integer',
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
        ])) {
            // Si la validación falla, redirigir al formulario con errores
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Agregar fecha de creación automáticamente
        $data['FechaCreacion'] = date('Y-m-d H:i:s');

        // Guardar la nueva idea en la base de datos
        $this->ideasModel->save($data);

        // Redirigir a la lista de ideas después de guardar exitosamente
        return redirect()->to(base_url('ideas'))->with('success', 'Idea creada exitosamente.');
    }

    /**
     * Muestra el formulario para editar una idea existente.
     * Esta función requiere acceso de administrador.
     *
     * @param int|null $id ID de la idea a editar
     * @return string Vista renderizada con el formulario de edición
     * @throws PageNotFoundException Si no se encuentra la idea
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        // Buscar la idea por ID
        $idea = $this->ideasModel->find($id);

        if (!$idea) {
            throw new PageNotFoundException("No se encontró la idea con ID: $id");
        }

        // Pasar los datos al formulario
        $data = [
            'idea' => $idea,
            'title' => 'Editar Idea',
        ];

        helper('form');

        return view('templates/header', $data)
            . view('ideas/edit')
            . view('templates/footer');
    }

    /**
     * Procesa la actualización de una idea existente.
     * Esta función requiere acceso de administrador.
     *
     * @param int|null $id ID de la idea a actualizar
     * @return RedirectResponse Redirección a la lista de ideas si la actualización es exitosa
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtener datos enviados por el formulario
        $data = $this->request->getPost(['UsuarioID', 'Titulo', 'Descripcion']);

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'UsuarioID' => 'required|integer',
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
        ])) {
            return redirect()->to(base_url("admin/ideas/update/$id"));
        }

        // Incluir el ID en los datos para actualizar el registro existente
        $data['ID'] = (int)$id;

        if (!$this->ideasModel->save($data)) {
            throw new \RuntimeException("Error al actualizar la idea.");
        }

        return redirect()->to(base_url('ideas'));
    }

    /**
     * Elimina una idea existente.
     * Esta función requiere acceso de administrador.
     *
     * @param int|null $id ID de la idea a eliminar
     * @return RedirectResponse Redirección a la lista de ideas si la eliminación es exitosa
     */
    public function delete($id)
    {
        $this->checkAdminAccess();

        if (!$this->ideasModel->delete($id)) {
            throw new PageNotFoundException("No se encontró la idea con ID: $id");
        }

        return redirect()->to(base_url('ideas'));
    }
}
