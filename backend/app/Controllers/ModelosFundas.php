<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ModelosFundasModel;
use App\Models\FundasProveedoresModel;
use App\Models\UsuariosModel;
use App\Exceptions\PermissionException;
use CodeIgniter\HTTP\RedirectResponse;

class ModelosFundas extends BaseController
{
    protected $modelosFundasModel;
    protected $fundasProveedoresModel;
    protected $usuariosModel;

    public function __construct()
    {
        $this->modelosFundasModel = new ModelosFundasModel();
        $this->fundasProveedoresModel = new FundasProveedoresModel();
        $this->usuariosModel = new UsuariosModel();
    }

     /**
     * Verifica si el usuario tiene permisos de administrador.
     * Lanza una excepción si no tiene acceso.
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
     * Muestra una lista de todos los modelos de fundas.
     * Paginación incluida.
     *
     * @return string Vista con la lista de modelos de fundas.
     */
    public function index()
    {
        $data = [
            'modelosFundas' => $this->modelosFundasModel->getModelosFundas(null),
            'pager' => $this->modelosFundasModel->pager,
            'title' => 'Lista de Modelos de Fundas',
        ];

        return view('templates/header', $data)
            . view('modelosFundas/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de un modelo de funda específico.
     * Obtiene también los proveedores asociados a dicho modelo.
     *
     * @param int|null $id ID del modelo de funda.
     * @return string Vista con los detalles del modelo de funda.
     * @throws PageNotFoundException Si no se encuentra el modelo de funda.
     */
    public function view($id = null)
    {
        $modeloFunda = $this->modelosFundasModel->getModelosFundas($id);

        if (!$modeloFunda) {
            throw new PageNotFoundException("No se encontró el modelo de funda con ID: $id");
        }

        // Obtener los proveedores asociados a este modelo de funda
        $proveedores = $this->fundasProveedoresModel->getProveedoresByFunda($id);

        $data = [
            'modeloFunda' => $modeloFunda,
            'proveedores' => $proveedores,
            'title' => 'Detalle del Modelo de Funda',
        ];

        return view('templates/header', $data)
            . view('modelosFundas/view')
            . view('templates/footer');
    }

     /**
     * Muestra el formulario para crear un nuevo modelo de funda.
     * Obtiene los proveedores disponibles para asociar al modelo.
     *
     * @return string Vista del formulario de creación de modelo de funda.
     */
    public function new()
    {
        $this->checkAdminAccess();

        // Obtener todos los proveedores
        $proveedores = $this->fundasProveedoresModel->getProveedores();

        helper('form');

        // Pasar los proveedores a la vista
        return view('templates/header', ['title' => 'Crear Modelo de Funda'])
            . view('modelosFundas/create', [
                'proveedores' => $proveedores
            ])
            . view('templates/footer');
    }


    /**
     * Crea un nuevo modelo de funda y lo guarda en la base de datos.
     * Valida los datos del formulario y maneja la subida de la imagen.
     * Relaciona los proveedores seleccionados con el modelo de funda.
     *
     * @return RedirectResponse Redirige al listado de modelos de fundas con mensaje de éxito.
     */
    public function create()
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtener el archivo cargado
        $file = $this->request->getFile('ImagenURL');

        // Verificar si el archivo es válido
        if (!$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar una imagen válida.');
        }

        // Restricciones de validación del archivo
        $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2048; // Limitar el tamaño máximo a 2MB

        // Validar el tamaño y el tipo de archivo
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return redirect()->back()->withInput()->with('error', 'El archivo debe ser una imagen JPEG, PNG o GIF.');
        }

        if ($file->getSize() > $maxSize * 1024) {
            return redirect()->back()->withInput()->with('error', 'El archivo debe ser menor a 2MB.');
        }

        // Validar los datos del formulario
        if (!$this->validate([
            'Nombre' => 'required|min_length[3]|max_length[255]',
            'Tamaño' => 'required|min_length[3]|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|min_length[3]|max_length[100]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Por favor revisa los campos.');
        }

        // Obtener el nombre del archivo y la extensión
        $imagenNombre = $this->request->getVar('Nombre'); // Usamos el nombre proporcionado por el usuario
        $extension = $file->getExtension(); // Obtenemos la extensión del archivo

        // Generar el nombre final para la imagen, agregando la extensión correcta
        $imagenNombreFinal = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imagenNombre) . '.' . $extension;

        // Determinar el tipo de funda y la ruta correspondiente
        $tipoFunda = $this->request->getVar('TipoFunda') === 'expandible' ? 'fundas_expansibles' : 'fundas_fijas';
        $path = ROOTPATH . 'public/assets/imagenes/' . $tipoFunda;

        // Crear la carpeta si no existe
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // Mover el archivo al directorio
        $file->move($path, $imagenNombreFinal);

        // Obtener los datos del formulario
        $data = [
            'Nombre' => $this->request->getVar('Nombre'),
            'Tamaño' => $this->request->getVar('Tamaño'),
            'CapacidadCarga' => $this->request->getVar('CapacidadCarga'),
            'Expansible' => $this->request->getVar('Expansible'),
            'ImagenURL' => '/assets/imagenes/' . $tipoFunda . '/' . $imagenNombreFinal,  // Guardamos la URL de la imagen
            'TipoFunda' => $this->request->getVar('TipoFunda'),
            'FechaCreacion' => date('Y-m-d H:i:s'),
        ];

        // Guardar el modelo de funda en la base de datos
        if ($this->modelosFundasModel->save($data)) {
            // Obtener el ID del modelo de funda insertado
            $modeloFundaID = $this->modelosFundasModel->insertID();

            // Relacionar los proveedores seleccionados
            $proveedores = $this->request->getVar('ProveedorID');
            if (is_array($proveedores) && !empty($proveedores)) {
                $dataRelaciones = [];
                foreach ($proveedores as $proveedorID) {
                    // Insertar la relación entre el proveedor y la funda
                    $dataRelaciones[] = [
                        'FundaID' => $modeloFundaID,  // Utilizamos FundaID, no ModeloFundaID
                        'ProveedorID' => $proveedorID,
                    ];
                }

                // Usar insertBatch para insertar todas las relaciones de una vez
                try {
                    $this->fundasProveedoresModel->insertBatch($dataRelaciones);
                } catch (\Exception $e) {
                    log_message('error', 'Error al insertar relaciones: ' . $e->getMessage());
                    return redirect()->back()->withInput()->with('error', 'Error al guardar las relaciones.');
                }
            }

            // Redirigir al listado de fundas con un mensaje de éxito
            return redirect()->to('admin/modelosFundas')->with('success', 'El modelo de funda ha sido creado correctamente.');
        } else {
            // Si hay un error al guardar
            return redirect()->back()->withInput()->with('error', 'Hubo un error al crear el modelo de funda.');
        }
    }



    /**
     * Muestra el formulario para editar un modelo de funda.
     * Obtiene los proveedores asociados y todos los proveedores disponibles.
     *
     * @param int $id ID del modelo de funda que se editará.
     * @return string Vista del formulario de edición de modelo de funda.
     * @throws PageNotFoundException Si no se encuentra el modelo de funda.
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        // Buscar el modelo de funda por ID
        $modeloFunda = $this->modelosFundasModel->find($id);

        if (!$modeloFunda) {
            throw new PageNotFoundException('error', "No se encontró el modelo de funda con ID: $id");
        }

        // Obtener los proveedores actuales asociados a la funda
        $proveedores = $this->fundasProveedoresModel->getProveedoresByFunda($id);

        // Obtener todos los proveedores para seleccionar
        $todosLosProveedores = $this->fundasProveedoresModel->getProveedores();

        // Pasa los datos a la vista
        $data = [
            'modeloFunda' => $modeloFunda,
            'proveedores' => $proveedores,
            'todosLosProveedores' => $todosLosProveedores,
            'title' => 'Editar Modelo de Funda',
        ];

        helper('form');

        return view('templates/header', $data)
            . view('modelosFundas/update')
            . view('templates/footer');
    }


    /**
     * Actualiza los datos de un modelo de funda en la base de datos.
     * Maneja la actualización de imagen y proveedores.
     *
     * @param int $id ID del modelo de funda que se actualizará.
     * @return RedirectResponse Redirige al listado de modelos de fundas con mensaje de éxito.
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        // Obtener los datos enviados por el formulario
        $data = $this->request->getPost([
            'Nombre',
            'Tamaño',
            'CapacidadCarga',
            'Expansible',
            'TipoFunda',
        ]);

        // Verificar si el campo de la imagen ha cambiado
        $file = $this->request->getFile('ImagenURL');

        // Validar los datos del formulario
        if (!$this->validateData($data, [
            'Nombre' => 'required|string|max_length[255]',
            'Tamaño' => 'required|string|max_length[100]',
            'CapacidadCarga' => 'required|numeric',
            'Expansible' => 'required|in_list[0,1]',
            'TipoFunda' => 'required|string|max_length[100]',
        ])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Verificar si se subió una nueva imagen
        if ($file && $file->isValid()) {
            // Obtener la ruta de la imagen actual
            $modeloFunda = $this->modelosFundasModel->find($id);
            if (!$modeloFunda) {
                throw new PageNotFoundException("No se encontró el modelo de funda con ID: $id");
            }
            
            // Acceder a las propiedades como un objeto
            $imagenActual = $modeloFunda->ImagenURL;

            // Si existe una imagen actual, eliminarla antes de guardar la nueva
            if (!empty($imagenActual) && file_exists(ROOTPATH . 'public/' . $imagenActual)) {
                unlink(ROOTPATH . 'public/' . $imagenActual);  // Eliminar la imagen antigua
            }

            // Generar el nuevo nombre de la imagen y mover el archivo
            $extension = $file->getExtension();
            $nuevoNombreImagen = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->request->getVar('Nombre')) . '.' . $extension;

            $tipoFunda = $this->request->getVar('TipoFunda') === 'expandible' ? 'fundas_expansibles' : 'fundas_fijas';
            $path = ROOTPATH . 'public/assets/imagenes/' . $tipoFunda;

            // Crear la carpeta si no existe
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            // Mover el archivo al directorio
            $file->move($path, $nuevoNombreImagen);

            // Actualizar la URL de la imagen en el data
            $data['ImagenURL'] = '/assets/imagenes/' . $tipoFunda . '/' . $nuevoNombreImagen;
        }

        // Actualizar el modelo de funda con los nuevos datos
        $this->modelosFundasModel->update($id, $data);

        // Obtener los proveedores seleccionados
        $proveedoresSeleccionados = $this->request->getPost('ProveedorID');

        // Si hay proveedores seleccionados, actualizamos la relación en la tabla Fundas_Proveedores
        if ($proveedoresSeleccionados) {
            // Eliminar los proveedores anteriores asociados a esta funda
            $this->fundasProveedoresModel->where('FundaID', $id)->delete();

            // Preparar los nuevos registros para la tabla de relaciones
            $dataProveedores = [];
            foreach ($proveedoresSeleccionados as $proveedorID) {
                $dataProveedores[] = [
                    'FundaID' => $id,
                    'ProveedorID' => $proveedorID,
                ];
            }

            // Insertar los nuevos proveedores asociados
            $this->fundasProveedoresModel->insertBatch($dataProveedores);
        }

        // Redirigir a la vista de modelos de fundas con un mensaje de éxito
        return redirect()->to('/admin/modelosFundas')->with('success', 'Modelo de Funda actualizado exitosamente');
    }

    /**
     * Elimina un modelo de funda de la base de datos.
     * También elimina la imagen asociada y las relaciones con proveedores.
     *
     * @param int $id ID del modelo de funda que se eliminará.
     * @return RedirectResponse Redirige al listado de modelos de fundas con mensaje de éxito.
     */
    public function delete($id)
    {
        $this->checkAdminAccess();

        // Verificar si el ID es válido
        if (is_null($id) || !is_numeric($id)) {
            return redirect()->to(base_url('admin/modelosFundas'))->with('error', 'ID de modelo de funda inválido.');
        }

        // Obtener la funda que queremos eliminar
        $modeloFunda = $this->modelosFundasModel->find($id);

        if (!$modeloFunda) {
            return redirect()->to(base_url('admin/modelosFundas'))->with('error', "No se encontró el modelo de funda con ID: $id.");
        }

        // Obtener la imagen de la funda para eliminarla
        $imagenActual = $modeloFunda->ImagenURL;
        $tipoFunda = $modeloFunda->TipoFunda === 'expandible' ? 'fundas_expansibles' : 'fundas_fijas';

        // Eliminar la imagen si existe
        if (!empty($imagenActual) && file_exists(ROOTPATH . 'public/' . $imagenActual)) {
            unlink(ROOTPATH . 'public/' . $imagenActual);  // Eliminar la imagen antigua
        }

        // Obtener los proveedores asociados a esta funda
        $proveedoresAsociados = $this->fundasProveedoresModel->getProveedoresByFunda($id);

        // Extraer los IDs de los proveedores asociados
        $proveedoresIDs = array_map(function ($proveedor) {
            return $proveedor['ID'];
        }, $proveedoresAsociados);

        // Eliminar las relaciones entre esta funda y los proveedores asociados
        if (!empty($proveedoresIDs)) {
            $this->fundasProveedoresModel->deleteProveedorByFunda($id, $proveedoresIDs);
        }

        // Finalmente eliminar el modelo de funda de la base de datos
        if ($this->modelosFundasModel->delete($id)) {
            return redirect()->to(base_url('admin/modelosFundas'))->with('success', 'Modelo de Funda y sus relaciones eliminadas exitosamente.');
        }

        // Si algo falla en el proceso de eliminación
        return redirect()->to(base_url('admin/modelosFundas'))->with('error', 'Hubo un error al intentar eliminar el modelo de funda.');
    }

}
