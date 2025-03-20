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
        $this->simulacionesModel = new SimulacionesModel();
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
     * Solo accesible por administradores.
     */
    /**
     * Procesa la creación de una nueva simulación.
     * Solo accesible por administradores.
     */
    public function create()
    {
        $this->checkAdminAccess(); // Verifica si el usuario tiene permisos de administrador

        helper('form');

        // Obtenemos el ID del usuario logueado desde la sesión
        $session = session();
        $userId = $session->get('user_id'); // Esto recupera el ID del usuario logueado

        // Si no se ha recuperado el ID del usuario logueado, redirigimos
        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Debes estar logueado para crear una simulación.');
        }

        // Obtenemos los datos del formulario, excluyendo UsuarioID ya que lo sacamos de la sesión
        $data = $this->request->getPost(['CondicionLuz', 'Tiempo']);  // Obtenemos la condición de luz y el tiempo

        // Validar los datos del formulario (ya no validamos UsuarioID, solo CondicionLuz y Tiempo)
        if (!$this->validateData($data, [
            'CondicionLuz' => 'required|string|max_length[50]',
            'Tiempo' => 'required|integer',
        ])) {
            return redirect()->to(base_url('admin/simulaciones'))->with('validation', $this->validator);
        }

        // Llamamos al método calcularEnergia del modelo
        $energiaGenerada = $this->simulacionesModel->calcularEnergia($data['CondicionLuz'], $data['Tiempo']);

        // Añadimos la energía generada al array de datos
        $data['EnergiaGenerada'] = $energiaGenerada;
        $data['Fecha'] = date('Y-m-d'); // Añadimos la fecha de creación
        $data['UsuarioID'] = $userId;  // Asociamos el ID del usuario logueado con la simulación

        // Guardamos los datos en la base de datos
        if (!$this->simulacionesModel->save($data)) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'Error al crear la simulación.');
        }

        return redirect()->to(base_url('admin/simulaciones'))->with('success', 'Simulación creada exitosamente.');
    }



    /**
     * Muestra el formulario para editar una simulación existente.
     * Solo accesible por administradores.
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        helper('form');

        $simulacion = $this->simulacionesModel->find($id);

        if (!$simulacion) {
            throw new PageNotFoundException("No se encontró la simulación con ID: $id");
        }

        $data = [
            'simulacion' => $simulacion,
            'title' => 'Editar Simulación',
        ];

        return view('templates/header', $data)
            . view('simulaciones/update')
            . view('templates/footer');
    }

    /**
     * Procesa la actualización de una simulación existente.
     * Solo accesible por administradores.
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        helper('form');

        // Verificar si el ID es válido
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'ID de simulación no válido.');
        }

        // Obtener los datos enviados por el formulario
        $data = $this->request->getPost(['CondicionLuz', 'EnergiaGenerada']);

        // Depuración: Verifica los datos recibidos
        log_message('debug', 'Datos recibidos: ' . print_r($data, true));

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'CondicionLuz' => 'required|string|max_length[50]',
            'EnergiaGenerada' => 'required|decimal',
        ])) {
            return redirect()->to(base_url("admin/simulaciones/update/$id"))->withInput()->with('error', 'Validación fallida');
        }

        // Obtener los datos actuales de la simulación para asegurarse de que el ID existe
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'Simulación no encontrada.');
        }

        // Calcular la nueva energía generada si no fue proporcionada
        if (empty($data['EnergiaGenerada'])) {
            $energiaGenerada = $this->simulacionesModel->calcularEnergia($data['CondicionLuz'], $simulacion['Tiempo']); // Usar el tiempo original
            $data['EnergiaGenerada'] = $energiaGenerada;
        }

        // Asegúrate de que el ID de la simulación está presente
        $data['ID'] = (int)$id;
        $data['Fecha'] = date('Y-m-d'); // Si quieres actualizar la fecha también

        // Depuración: Verifica los datos antes de guardar
        log_message('debug', 'Datos a guardar: ' . print_r($data, true));

        // Guardar la simulación actualizada
        if (!$this->simulacionesModel->save($data)) {
            log_message('error', 'Error al actualizar la simulación. Datos: ' . print_r($data, true));
            return redirect()->to(base_url("admin/simulaciones/update/$id"))->with('error', 'Error al actualizar.');
        }

        // Redirigir a la lista de simulaciones con un mensaje de éxito
        return redirect()->to(base_url('admin/simulaciones'))->with('success', 'Simulación actualizada exitosamente.');
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

        // Verificar si el ID es válido
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'ID no válido para eliminar.');
        }

        // Verificar si la simulación existe
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', "Simulación con ID: $id no encontrada.");
        }

        try {
            // Eliminar la simulación
            if (!$this->simulacionesModel->delete($id)) {
                // Si el método delete falla, lanzar una excepción
                throw new \RuntimeException("No se pudo eliminar la simulación con ID: $id.");
            }

            // Si la eliminación es exitosa, redirigir con un mensaje de éxito
            return redirect()->to(base_url('admin/simulaciones'))->with('success', 'Simulación eliminada exitosamente.');
        } catch (\RuntimeException $e) {
            // Capturar cualquier excepción que ocurra y redirigir con un mensaje de error
            log_message('error', $e->getMessage());
            return redirect()->to(base_url('admin/simulaciones'))->with('error', $e->getMessage());
        }
    }
}
