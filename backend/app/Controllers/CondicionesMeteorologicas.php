<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\CondicionesMeteorologicasModel;
use App\Models\UsuariosModel;
use App\Exceptions\PermissionException;
use CodeIgniter\HTTP\RedirectResponse;

class CondicionesMeteorologicas extends BaseController
{
    protected $condicionesModel;
    protected $usuariosModel;

    /**
     * Constructor del controlador.
     * Inicializa los modelos necesarios para las operaciones del controlador.
     */
    public function __construct()
    {
        $this->condicionesModel = new CondicionesMeteorologicasModel();
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
     * Muestra la lista de todas las condiciones meteorológicas.
     * Esta función es accesible para todos los usuarios.
     *
     * @return string Vista renderizada con la lista de condiciones meteorológicas
     */
    public function index()
    {
        $data = [
            'condiciones' => $this->condicionesModel->getCondiciones(null, 10), // Obtiene condiciones con paginación (10 por página)
            'pager' => $this->condicionesModel->pager, // Objeto de paginación
            'title' => 'Lista de Condiciones Meteorológicas',
        ];

        return view('templates/header', $data)
            . view('condicionesMeteorologicas/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de una condición meteorológica específica.
     * Esta función es accesible para todos los usuarios.
     *
     * @param int|null $id ID de la condición meteorológica a mostrar
     * @return string Vista renderizada con los detalles de la condición meteorológica
     * @throws PageNotFoundException Si no se encuentra la condición meteorológica
     */
    public function view($id = null)
    {
        $condicion = $this->condicionesModel->getCondiciones($id);

        if (!$condicion) {
            throw new PageNotFoundException("No se encontró la condición meteorológica con ID: $id");
        }

        $data = [
            'condicion' => $condicion,
            'title' => 'Detalle de Condición Meteorológica',
        ];

        return view('templates/header', $data)
            . view('condicionesMeteorologicas/view')
            . view('templates/footer');
    }

    /**
     * Muestra el formulario para crear una nueva condición meteorológica.
     * Esta función requiere acceso de administrador.
     *
     * @return string Vista renderizada con el formulario de creación
     */
    public function new()
    {
        $this->checkAdminAccess();

        helper('form');
        return view('templates/header', ['title' => 'Crear Condición Meteorológica'])
            . view('condicionesMeteorologicas/create')
            . view('templates/footer');
    }

    /**
     * Procesa la creación de una nueva condición meteorológica.
     * Esta función requiere acceso de administrador.
     *
     * @return RedirectResponse 
     */
    public function create()
{
    $this->checkAdminAccess();

    helper('form');

    // Obtener el ID del usuario desde la sesión (si es necesario, o si puedes agregarlo al formulario como oculto)
    $usuarioID = session()->get('user_id');

    // Obtener los datos enviados por el formulario
    $data = $this->request->getPost(['Fecha', 'LuzSolar', 'Temperatura', 'Humedad', 'Viento']);

    // Agregar el UsuarioID al array de datos (si es necesario)
    $data['UsuarioID'] = $usuarioID;

    // Validar los datos del formulario
    if (!$this->validateData($data, [
        'UsuarioID' => 'required|integer',
        'Fecha' => 'required|valid_date',
        'LuzSolar' => 'required|numeric',
        'Temperatura' => 'required|numeric',
        'Humedad' => 'required|numeric',
        'Viento' => 'required|numeric',
    ])) {
        // Si la validación falla, redirigir al formulario con errores
        return redirect()->back()->withInput()->with('validation', $this->validator);
    }

    // Guardar la nueva condición meteorológica en la base de datos
    $this->condicionesModel->save($data);

    // Redirigir a la lista de condiciones meteorológicas después de guardar exitosamente
    return redirect()->to(base_url('admin/condicionesMeteorologicas'))->with('success', 'Condición meteorológica creada exitosamente.');
}

    /**
     * Muestra el formulario para editar una condición meteorológica existente.
     * Esta función requiere acceso de administrador.
     *
     * @param int|null $id ID de la condición meteorológica a editar
     * @return string Vista renderizada con el formulario de edición
     * @throws PageNotFoundException Si no se encuentra la condición meteorológica
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        // Buscar la condición meteorológica por ID
        $condicion = $this->condicionesModel->find($id);

        if (!$condicion) {
            throw new PageNotFoundException("No se encontró la condición meteorológica con ID: $id");
        }

        // Pasar los datos al formulario
        $data = [
            'condicion' => $condicion,
            'title' => 'Editar Condición Meteorológica',
        ];

        helper('form');

        return view('templates/header', $data)
            . view('condicionesMeteorologicas/update')
            . view('templates/footer');
    }

    /**
     * Procesa la actualización de una condición meteorológica existente.
     * Esta función requiere acceso de administrador.
     *
     * @param int|null $id 
     * @return RedirectResponse 
     */
    public function updatedItem($id)
{
    $this->checkAdminAccess(); 

    helper('form');

    // Obtener los datos enviados por el formulario, asegurándonos de capturar todos los campos relevantes
    $data = $this->request->getPost(['Fecha', 'LuzSolar', 'Temperatura', 'Humedad', 'Viento']);

    // Validar los datos del formulario
    if (!$this->validateData($data, [
        'Fecha' => 'required|valid_date',
        'LuzSolar' => 'required|numeric', 
        'Temperatura' => 'required|numeric', 
        'Humedad' => 'required|numeric', 
        'Viento' => 'required|numeric', 
    ])) {
        // Si la validación falla, redirigir de vuelta al formulario con los errores
        return redirect()->to(base_url("admin/condicionesMeteorologicas/update/$id"))
            ->withInput()
            ->with('validation', $this->validator);
    }

    // Guardar los cambios en la base de datos
    if ($this->condicionesModel->update($id, $data)) {
        // Redirigir a la lista de condiciones después de guardar exitosamente
        return redirect()->to(base_url('admin/condicionesMeteorologicas'))->with('success', 'Condición meteorológica actualizada exitosamente.');
    }

    // Si ocurre un error al actualizar, devolver un mensaje de error
    return redirect()->to(base_url("admin/condicionesMeteorologicas/update/$id"))
        ->with('error', 'Hubo un problema al actualizar la condición meteorológica.');
}


    /**
     * Elimina una condición meteorológica existente.
     * Esta función requiere acceso de administrador.
     *
     * @param int|null $id ID de la condición meteorológica a eliminar
     * @return RedirectResponse Redirección a la lista de condiciones meteorológicas si la eliminación es exitosa
     */
    public function delete($id)
    {
        // Verificar si el usuario tiene acceso de administrador
        $this->checkAdminAccess();

        // Verificar si el ID es válido (no vacío o nulo)
        if (is_null($id) || !is_numeric($id)) {
            // Redirigir con un mensaje de error si el ID es inválido
            return redirect()->to(base_url('admin/condicionesMeteorologicas'))->with('error', 'ID de condición meteorológica inválido.');
        }

        // Intentar eliminar la condición meteorológica
        $condicion = $this->condicionesModel->find($id);

        if (!$condicion) {
            // Si no se encuentra la condición meteorológica en la base de datos, redirigir con un error
            return redirect()->to(base_url('admin/condicionesMeteorologicas'))->with('error', "No se encontró la condición meteorológica con ID: $id.");
        }

        // Realizar la eliminación de la condición meteorológica
        if ($this->condicionesModel->delete($id)) {
            // Redirigir con un mensaje de éxito si la eliminación es exitosa
            return redirect()->to(base_url('admin/condicionesMeteorologicas'))->with('success', 'Condición meteorológica eliminada exitosamente.');
        }

        // Si algo salió mal al intentar eliminar, redirigir con un mensaje de error
        return redirect()->to(base_url('admin/condicionesMeteorologicas'))->with('error', 'Hubo un error al intentar eliminar la condición meteorológica.');
    }
}
