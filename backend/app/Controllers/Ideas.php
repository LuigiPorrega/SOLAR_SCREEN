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

        // Obtener el ID del usuario desde la sesión
        $usuarioID = session()->get('user_id');

        // Obtener datos enviados por el formulario, asegurándote de pasar el UsuarioID
        $data = $this->request->getPost(['Titulo', 'Descripcion']);

        // Agregar el UsuarioID al array de datos
        $data['UsuarioID'] = $usuarioID;

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
        return redirect()->to(base_url('admin/ideas'))->with('success', 'Idea creada exitosamente.');
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
            . view('ideas/update')
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
        $this->checkAdminAccess(); // Aseguramos que el usuario tenga acceso como administrador

        helper('form');

        // Obtener el ID del usuario desde la sesión
        $usuarioID = session()->get('user_id');

        // Obtener datos enviados por el formulario
        $data = $this->request->getPost(['Titulo', 'Descripcion']);

        // Aseguramos que el UsuarioID esté en los datos para la actualización
        $data['UsuarioID'] = $usuarioID;

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'Titulo' => 'required|string|max_length[255]',
            'Descripcion' => 'required|string',
        ])) {
            // Si la validación falla, redirigir de vuelta al formulario con los errores
            return redirect()->to(base_url("admin/ideas/update/$id"))
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Guardar los cambios en la base de datos
        if ($this->ideasModel->update($id, $data)) {
            // Redirigir a la lista de ideas después de guardar exitosamente
            return redirect()->to(base_url('admin/ideas'))->with('success', 'Idea actualizada exitosamente.');
        }

        // Si ocurre un error al actualizar devolver un mensaje
        return redirect()->to(base_url("admin/ideas/update/$id"))->with('error', 'Hubo un problema al actualizar la idea.');
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
        // Verificar si el usuario tiene acceso de administrador
        $this->checkAdminAccess();

        // Verificar si el ID es válido (no vacío o nulo)
        if (is_null($id) || !is_numeric($id)) {
            // Redirigir con un mensaje de error si el ID es inválido
            return redirect()->to(base_url('admin/ideas'))->with('error', 'ID de idea inválido.');
        }

        // Intentar eliminar la idea
        $idea = $this->ideasModel->find($id);

        if (!$idea) {
            // Si no se encuentra la idea en la base de datos, redirigir con un error
            return redirect()->to(base_url('admin/ideas'))->with('error', "No se encontró la idea con ID: $id.");
        }

        // Realizar la eliminación de la idea
        if ($this->ideasModel->delete($id)) {
            // Redirigir con un mensaje de éxito si la eliminación es exitosa
            return redirect()->to(base_url('admin/ideas'))->with('success', 'Idea eliminada exitosamente.');
        }

        // Si algo salió mal al intentar eliminar, redirigir con un mensaje de error
        return redirect()->to(base_url('admin/ideas'))->with('error', 'Hubo un error al intentar eliminar la idea.');
    }
}
