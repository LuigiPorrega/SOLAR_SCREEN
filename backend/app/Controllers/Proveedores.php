<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ProveedoresModel;
use App\Models\FundasProveedoresModel;
use App\Models\UsuariosModel;
use App\Exceptions\PermissionException;
use CodeIgniter\HTTP\RedirectResponse;

class Proveedores extends BaseController
{
    protected $proveedoresModel;
    protected $usuariosModel;
    protected $fundasProveedoresModel;

    public function __construct()
    {
        $this->proveedoresModel = new ProveedoresModel();
        $this->usuariosModel = new UsuariosModel();
        $this->fundasProveedoresModel = new FundasProveedoresModel();
    }

    /**
     * Verifica si el usuario tiene acceso de administrador.
     * Lanza una excepción si el acceso no está autorizado.
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
     * Muestra la lista de proveedores con paginación.
     * Solo accesible por administradores.
     *
     * @return string
     */
    public function index()
    {
        // Obtener los proveedores con paginación
        $proveedores = $this->proveedoresModel->getProveedores();

        // Asegurarse de que 'pager' esté disponible en los datos
        $data = [
            'proveedores' => $proveedores,
            'pager' => $this->proveedoresModel->pager,  // Asegurarse de pasar el objeto pager
            'title' => 'Lista de Proveedores',
        ];

        // Cargar la vista con los datos
        return view('templates/header', $data)
            . view('proveedores/index')
            . view('templates/footer');
    }


    /**
     * Muestra el detalle de un proveedor específico.
     * Lanza una excepción si el proveedor no se encuentra.
     *
     * @param int|null $id ID del proveedor.
     * @return string
     * @throws PageNotFoundException
     */
    public function view($id = null)
    {
        $proveedor = $this->proveedoresModel->getProveedores($id);

        if (!$proveedor) {
            throw new PageNotFoundException("No se encontró el proveedor con ID: $id");
        }

        // Obtener las fundas asociadas a este proveedor
        $fundas = $this->fundasProveedoresModel->getFundasByProveedor($id);

        $data = [
            'proveedor' => $proveedor,
            'fundas' => $fundas,
            'title' => 'Detalle del Proveedor',
        ];

        return view('templates/header', $data)
            . view('proveedores/view')
            . view('templates/footer');
    }

    /**
     * Muestra el formulario para crear un nuevo proveedor.
     * Solo accesible para administradores.
     *
     * @return string
     */
    public function new()
    {
        $this->checkAdminAccess();

        // Obtener todas las fundas disponibles desde el modelo FundasProveedoresModel
        $fundas = $this->fundasProveedoresModel->getFundas();

        helper('form');

        return view('templates/header', ['title' => 'Crear Proveedor'])
            . view('proveedores/create', ['fundas' => $fundas])
            . view('templates/footer');
    }


    /**
     * Crea un nuevo proveedor.
     * Valida los datos del formulario y los guarda en la base de datos.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        // Validar los datos del formulario
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Pais' => 'required|min_length[3]|max_length[100]',
            'ContactoNombre' => 'required|min_length[3]|max_length[255]',
            'ContactoTelefono' => 'required|regex_match[/^\+[0-9]{1,4}[0-9]{7,15}$/]', // Prefijo internacional y 7-15 dígitos
            'ContactoEmail' => 'required|valid_email',
            'SitioWeb' => 'permit_empty|valid_url',
            'Direccion' => 'required|min_length[3]|max_length[255]',
            'Descripcion' => 'required|min_length[10]',
            'FundaID' => 'required|is_array', // Validar que sea un array
        ])) {
            return redirect()->back()->withInput()->with('error', 'Por favor revisa los campos.');
        }

        // Obtener los datos del formulario
        $sitioWeb = $this->request->getVar('SitioWeb');

        // Asegurarse de que el sitio web comience con http:// o https://
        if (!empty($sitioWeb)) {
            // Si no comienza con "http://" o "https://", se añade el "https://"
            if (!preg_match('#^https?://#', $sitioWeb)) {
                $sitioWeb = 'https://' . $sitioWeb;
            }
        }

        $data = [
            'Nombre' => $this->request->getVar('Nombre'),
            'Pais' => $this->request->getVar('Pais'),
            'ContactoNombre' => $this->request->getVar('ContactoNombre'),
            'ContactoTelefono' => preg_replace('/[^0-9]/', '', $this->request->getVar('ContactoTelefono')),
            'ContactoEmail' => strtolower($this->request->getVar('ContactoEmail')),
            'SitioWeb' => $sitioWeb,
            'Direccion' => $this->request->getVar('Direccion'),
            'Descripcion' => $this->request->getVar('Descripcion'),
            'FechaCreacion' => date('Y-m-d H:i:s'),
            'Activo' => 1,
        ];

        // Guardar el proveedor en la base de datos
        $proveedorID = $this->proveedoresModel->insert($data);

        if (!$proveedorID) {
            return redirect()->back()->withInput()->with('error', 'No se pudo crear el proveedor.');
        }

        // Obtener las fundas seleccionadas
        $fundasSeleccionadas = $this->request->getVar('FundaID');

        if (is_array($fundasSeleccionadas) && !empty($fundasSeleccionadas)) {
            $dataRelaciones = [];

            foreach ($fundasSeleccionadas as $fundaID) {

                $dataRelaciones[] = [
                    'FundaID' => $fundaID,
                    'ProveedorID' => $proveedorID,
                ];
            }

            try {
                // Insertar los datos en la tabla intermedia
                $this->fundasProveedoresModel->insertBatch($dataRelaciones);
            } catch (\Exception $e) {
                log_message('error', 'Error al insertar relaciones: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Error al guardar las relaciones.');
            }
        }

        return redirect()->to('admin/proveedores')->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un proveedor existente.
     * Solo accesible por administradores.
     *
     * @param int $id ID del proveedor que se actualizará.
     * @return string
     * @throws PageNotFoundException
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        // Buscar el proveedor por ID
        $proveedor = $this->proveedoresModel->find($id);

        if (!$proveedor) {
            throw new PageNotFoundException("No se encontró el proveedor con ID: $id");
        }

        // Obtener las fundas asociadas a este proveedor
        $fundas = $this->fundasProveedoresModel->getFundasByProveedor($id);

        // Pasar los datos al formulario
        $data = [
            'proveedor' => $proveedor,
            'fundas' => $fundas,
            'title' => 'Editar Proveedor',
        ];

        helper('form');

        return view('templates/header', $data)
            . view('proveedores/update')
            . view('templates/footer');
    }


    /**
     * Actualiza los datos de un proveedor existente.
     * Realiza las validaciones y actualiza la base de datos.
     *
     * @param int $id ID del proveedor que se actualizará.
     * @return RedirectResponse
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        // Obtener los datos enviados por el formulario
        $data = $this->request->getPost([
            'Nombre',
            'Pais',
            'ContactoNombre',
            'ContactoTelefono',
            'ContactoEmail',
            'SitioWeb',
            'Direccion',
            'Descripcion'
        ]);

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'Nombre' => 'required|string|max_length[255]',
            'Pais' => 'required|string|max_length[100]',
            'ContactoNombre' => 'required|string|max_length[255]',
            'ContactoTelefono' => 'required|string|max_length[20]',
            'ContactoEmail' => 'required|valid_email',
            'SitioWeb' => 'required|valid_url',
            'Direccion' => 'required|string',
            'Descripcion' => 'required|string',
        ])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Actualizar los datos del proveedor
        $this->proveedoresModel->update($id, $data);

        // Obtener las fundas seleccionadas
        $selectedFundas = $this->request->getPost('FundaID') ?: [];

        // Eliminar las fundas antiguas del proveedor
        $this->fundasProveedoresModel->deleteFundasByProveedor($id, $selectedFundas);

        // Asignar las nuevas fundas al proveedor
        foreach ($selectedFundas as $fundaID) {
            $this->fundasProveedoresModel->insert([
                'ProveedorID' => $id,
                'FundaID' => $fundaID
            ]);
        }

        // Redirigir a la vista de proveedores
        return redirect()->to('/admin/proveedores')->with('message', 'Proveedor actualizado exitosamente');
    }


    /**
     * Elimina un proveedor de la base de datos.
     * Solo accesible para administradores.
     *
     * @param int $id ID del proveedor a eliminar.
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $this->checkAdminAccess();

        if (is_null($id) || !is_numeric($id)) {
            return redirect()->to(base_url('admin/proveedores'))->with('error', 'ID de proveedor inválido.');
        }

        $proveedor = $this->proveedoresModel->find($id);

        if (!$proveedor) {
            return redirect()->to(base_url('admin/proveedores'))->with('error', "No se encontró el proveedor con ID: $id.");
        }

        if ($this->proveedoresModel->delete($id)) {
            return redirect()->to(base_url('admin/proveedores'))->with('success', 'Proveedor eliminado exitosamente.');
        }

        return redirect()->to(base_url('admin/proveedores'))->with('error', 'Hubo un error al intentar eliminar el proveedor.');
    }
}
