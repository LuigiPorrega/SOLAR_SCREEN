<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\SimulacionesModel;
use App\Models\UsuariosModel;
use App\Models\CondicionesMeteorologicasModel;
use App\Exceptions\PermissionException;
use App\Models\ModelosFundasModel;
use CodeIgniter\HTTP\RedirectResponse;


class Simulaciones extends BaseController
{
    protected $simulacionesModel;
    protected $usuariosModel;
    protected $condicionesMeteorologicasModel;
    protected $fundasModel;


    public function __construct()
    {
        $this->simulacionesModel = new SimulacionesModel();
        $this->usuariosModel = new UsuariosModel();
        $this->condicionesMeteorologicasModel = new CondicionesMeteorologicasModel();
        $this->fundasModel = new ModelosFundasModel();
    }

    /**
     * Verifica si el usuario tiene acceso de administrador.
     * Si no tiene acceso, lanza una excepción de permiso.
     *
     * @return void
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
     * Muestra la lista de simulaciones.
     * Permite al usuario realizar una simulación y ver la lista de simulaciones existentes.
     *
     * @return string
     */
    public function index()
    {
        // Verificamos si el tiempo fue enviado por el usuario
        $tiempo = $this->request->getPost('tiempo') ?? 60;

        // Validamos que el tiempo sea un número y mayor que 0
        if (!is_numeric($tiempo) || $tiempo <= 0) {
            // Si el tiempo no es válido, asignamos un valor predeterminado 
            $tiempo = 60;
        }

        // Obtener las simulaciones de la base de datos
        $simulaciones = $this->simulacionesModel->getSimulaciones();

        // Iterar sobre las simulaciones y calcular la energía generada para cada una
        foreach ($simulaciones as &$simulacion) {
            // Obtener las condiciones meteorológicas para cada simulación
            $condicionesMeteorologicas = $this->condicionesMeteorologicasModel->find($simulacion['CondicionesMeteorologicasID']);

            // Verificamos si las condiciones meteorológicas existen
            if ($condicionesMeteorologicas) {
                // Extraemos los valores de las condiciones meteorológicas
                $luzSolar = $condicionesMeteorologicas['LuzSolar'];  // Lux
                $temperatura = $condicionesMeteorologicas['Temperatura']; // °C
                $humedad = $condicionesMeteorologicas['Humedad']; // %
                $viento = $condicionesMeteorologicas['Viento']; // km/h
            } else {
                // Si no se encuentran las condiciones meteorológicas, usamos valores predeterminados
                $luzSolar = 0;
                $temperatura = 25;  // Temperatura base de 25°C
                $humedad = 50;      // Humedad base de 50%
                $viento = 10;       // Viento base de 10 km/h
            }

            // Obtener la condición de luz de la simulación
            $condicionLuz = $simulacion['CondicionLuz']; // Ejemplo: 'Luz solar directa'

            // Llamamos a la función para calcular la energía generada, pasando el tiempo proporcionado por el usuario
            $simulacion['EnergiaGenerada'] = $this->simulacionesModel->calcularEnergia(
                $condicionLuz,       // Condición de luz (ej. 'Luz solar directa')
                $tiempo,             // Tiempo en minutos, tomado del formulario del usuario
                $luzSolar,           // Lux
                $temperatura,        // Temperatura en °C
                $humedad,            // Humedad en %
                $viento              // Viento en km/h
            );
        }

        // Preparar los datos para la vista
        $data = [
            'simulaciones' => $simulaciones,
            'pager' => $this->simulacionesModel->pager,
            'fundasModel' => $this->fundasModel,
            'title' => 'Lista de Simulaciones',
        ];

        // Renderizar la vista
        return view('templates/header', $data)
            . view('simulaciones/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de una simulación específica.
     * Permite ver los datos de la simulación, las condiciones meteorológicas y la funda recomendada.
     *
     * @param int|null $id
     * @return string
     * @throws PageNotFoundException
     */
    public function view($id = null)
    {
        try {
            // Obtener la simulación
            $simulacion = $this->simulacionesModel->getSimulaciones($id);

            if (!$simulacion) {
                throw new PageNotFoundException("No se encontró la simulación con ID: $id");
            }

            // Obtener el ID de las condiciones meteorológicas desde la simulación
            $condicionesMeteorologicasID = $simulacion['CondicionesMeteorologicasID'];

            // Usamos el modelo CondicionesMeteorologicasModel para obtener las condiciones meteorológicas
            $condicionesMeteorologicas = $this->condicionesMeteorologicasModel->find($condicionesMeteorologicasID);

            // Verificar que las condiciones meteorológicas existen
            if (!$condicionesMeteorologicas) {
                throw new \Exception("No se encontraron las condiciones meteorológicas para esta simulación.");
            }

            // Obtener la funda propuesta (asociada con la simulación)
            $fundaPropuesta = $this->fundasModel->find($simulacion['FundaID']);

            // Verifica si la funda propuesta contiene la clave 'CapacidadCarga'
            if (!isset($fundaPropuesta['CapacidadCarga'])) {
                throw new \Exception("La funda propuesta no tiene la clave 'CapacidadCarga'.");
            }

            // Obtener la energía generada de la simulación
            $energiaGenerada = $simulacion['EnergiaGenerada'];

            // Obtener la funda recomendada con base en la energía generada y la capacidad de carga
            $fundaRecomendada = $this->simulacionesModel->obtenerFundaRecomendada($energiaGenerada, $fundaPropuesta['CapacidadCarga']);

            // Obtener la justificación dinámica para la funda propuesta
            $justificacionFunda = $this->simulacionesModel->generarJustificacionFunda($energiaGenerada, $fundaPropuesta['CapacidadCarga']);

            // Obtener otras 4 fundas propuestas basadas en la simulación (ajustar según los parámetros de la simulación)
            $otrasFundasPropuestas = $this->simulacionesModel->obtenerFundasSimilares(
                $simulacion['CondicionLuz'],
                $fundaPropuesta['CapacidadCarga'],
                $fundaPropuesta['ID']
            );

            // Preparar los datos para la vista
            $data = [
                'simulacion' => $simulacion,
                'condicionesMeteorologicas' => $condicionesMeteorologicas,
                'fundaPropuesta' => $fundaPropuesta,
                'fundaRecomendada' => $fundaRecomendada,
                'justificacionFunda' => $justificacionFunda,
                'otrasFundasPropuestas' => $otrasFundasPropuestas, // Pasamos las fundas propuestas aquí
                'title' => 'Detalle de la Simulación',
            ];

            // Mostrar la vista
            return view('templates/header', $data)
                . view('simulaciones/view', $data)
                . view('templates/footer');
        } catch (\Exception $e) {
            // En caso de error, mostramos un mensaje adecuado
            return redirect()->to('/error')->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para crear una nueva simulación.
     * Esta función es accesible solo para administradores.
     *
     * @return string
     */
    public function new()
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtener las condiciones meteorológicas para el formulario
        $condicionesMeteorologicas = $this->condicionesMeteorologicasModel->findAll();

        return view('templates/header', ['title' => 'Crear Simulación'])
            . view('simulaciones/create', ['condicionesMeteorologicas' => $condicionesMeteorologicas])
            . view('templates/footer');
    }


    /**
     * Procesa el formulario para crear una nueva simulación.
     * Esta función es accesible solo para administradores.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtenemos el ID del usuario logueado desde la sesión
        $session = session();
        $userId = $session->get('user_id');

        // Si no se ha recuperado el ID del usuario logueado, redirigimos
        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Debes estar logueado para crear una simulación.');
        }

        // Obtenemos los datos del formulario
        $data = $this->request->getPost(['CondicionLuz', 'Tiempo', 'LuzSolar', 'Temperatura', 'Humedad', 'Viento', 'CondicionesMeteorologicasID']);

        // Validar los datos del formulario
        if (!$this->validate([
            'CondicionLuz' => 'required|string|max_length[50]',
            'Tiempo' => 'required|decimal',
            'CondicionesMeteorologicasID' => 'required|integer', // Validamos que la condición meteorológica se pase correctamente
        ])) {
            return redirect()->to(base_url('admin/simulaciones'))->with('success', $this->validator);
        }

        // Calcular la energía generada
        $energiaGenerada = $this->simulacionesModel->calcularEnergia(
            $data['CondicionLuz'],
            $data['Tiempo'],
            $data['LuzSolar'],
            $data['Temperatura'],
            $data['Humedad'],
            $data['Viento']
        );

        // Obtener la funda recomendada
        $capacidadCarga = 20; // Aquí debes poner la capacidad de carga para determinar la funda recomendada
        $fundaRecomendada = $this->simulacionesModel->obtenerFundaRecomendada($energiaGenerada, $capacidadCarga);
        $justificacionFunda = $this->simulacionesModel->generarJustificacionFunda($energiaGenerada, $capacidadCarga);

        // Obtener las fundas opcionales
        $fundasOpcionales = $this->simulacionesModel->obtenerFundasSimilares($data['CondicionLuz'], $capacidadCarga, $fundaRecomendada);

        // Preparar los datos para la vista
        $data = [
            'title' => 'Crear Simulación',
            'condicionesMeteorologicas' => $this->condicionesMeteorologicasModel->findAll(),
            'energiaGenerada' => $energiaGenerada,
            'fundaRecomendada' => $fundaRecomendada,
            'justificacionFunda' => $justificacionFunda,
            'fundasOpcionales' => $fundasOpcionales,
        ];

        // Pasar los datos a la vista
        return view('templates/header', $data)
            . view('simulaciones/create', $data)
            . view('templates/footer');
    }


    /**
     * Muestra el formulario para editar una simulación existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int $id
     * @return string
     * @throws PageNotFoundException
     */
    public function update($id)
    {
        $this->checkAdminAccess();
        helper('form');

        // Obtener la simulación existente por su ID
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            throw new PageNotFoundException("No se encontró la simulación con ID: $id");
        }

        // Obtener las condiciones meteorológicas relacionadas
        $condicionesMeteorologicas = $this->condicionesMeteorologicasModel->find($simulacion->CondicionesMeteorologicasID);
        if (!$condicionesMeteorologicas) {
            throw new PageNotFoundException("No se encontraron las condiciones meteorológicas para esta simulación.");
        }

        // Obtenemos los valores necesarios de las condiciones meteorológicas
        $luzSolar = $condicionesMeteorologicas['LuzSolar'];
        $temperatura = $condicionesMeteorologicas['Temperatura'];
        $humedad = $condicionesMeteorologicas['Humedad'];
        $viento = $condicionesMeteorologicas['Viento'];

        // Asegurarnos de que LuzSolar tiene un valor válido
        if (empty($luzSolar) || $luzSolar <= 0) {
            return redirect()->to(base_url("admin/simulaciones/update/$id"))->with('error', 'LuzSolar no es válida');
        }

        // Calcular la energía generada
        $energiaGenerada = $this->simulacionesModel->calcularEnergia(
            $simulacion['CondicionLuz'],  // Condición de luz (por ejemplo, "Luz Solar Directa")
            $simulacion['Tiempo'],         // Tiempo (en minutos)
            $luzSolar,                     // Luz solar en lux (de las condiciones meteorológicas)
            $temperatura,                  // Temperatura en °C
            $humedad,                      // Humedad en porcentaje
            $viento                        // Viento en km/h
        );

        // Otros cálculos para la funda recomendada y similares
        $capacidadCarga = 20; // Definir capacidad de carga
        $fundaRecomendada = $this->simulacionesModel->obtenerFundaRecomendada($energiaGenerada, $capacidadCarga);
        $justificacionFunda = $this->simulacionesModel->generarJustificacionFunda($energiaGenerada, $capacidadCarga);

        // Obtener las fundas opcionales
        $fundasOpcionales = $this->simulacionesModel->obtenerFundasSimilares($simulacion['CondicionLuz'], $capacidadCarga, $fundaRecomendada);

        // Obtener las condiciones meteorológicas para el formulario
        $condicionesMeteorologicasList = $this->condicionesMeteorologicasModel->findAll();

        // Pasar los datos de la simulación, la energía generada, la funda recomendada y las fundas opcionales a la vista
        $data = [
            'title' => 'Editar Simulación',
            'simulacion' => $simulacion,
            'energiaGenerada' => $energiaGenerada,
            'fundaRecomendada' => $fundaRecomendada,
            'justificacionFunda' => $justificacionFunda,
            'fundasOpcionales' => $fundasOpcionales,
            'condicionesMeteorologicas' => $condicionesMeteorologicasList
        ];

        return view('templates/header', $data)
            . view('simulaciones/update', $data)
            . view('templates/footer');
    }


    /**
     * Actualiza una simulación existente con nuevos datos.
     * Esta función es accesible solo para administradores.
     *
     * @param int $id ID de la simulación que se actualizará.
     * @return RedirectResponse
     * @throws PageNotFoundException
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();
        helper(['form', 'url']);

        // Validación de datos del formulario
        if (!$this->validate([
            'CondicionLuz' => 'required',
            'Tiempo' => 'required|numeric',
            'CondicionesMeteorologicasID' => 'required|is_not_unique[CondicionesMeteorologicas.ID]'
        ])) {
            return redirect()->to(base_url('admin/simulaciones/update/' . $id))
                ->withInput()
                ->with('error', 'Por favor complete todos los campos correctamente.');
        }

        // Obtener la simulación existente
        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            throw new PageNotFoundException("No se encontró la simulación con ID: $id");
        }

        // Obtener las condiciones meteorológicas para recalcular la energía
        $condicionesMeteorologicas = $this->condicionesMeteorologicasModel->find($this->request->getPost('CondicionesMeteorologicasID'));
        if (!$condicionesMeteorologicas) {
            return redirect()->to(base_url('admin/simulaciones/update/' . $id))
                ->with('error', 'Las condiciones meteorológicas seleccionadas no son válidas.');
        }

        // Obtener los valores necesarios de las condiciones meteorológicas
        $luzSolar = $condicionesMeteorologicas['LuzSolar'];
        $temperatura = $condicionesMeteorologicas['Temperatura'];
        $humedad = $condicionesMeteorologicas['Humedad'];
        $viento = $condicionesMeteorologicas['Viento'];

        // Calcular la energía generada con la función en la base de datos
        $energiaGenerada = $this->simulacionesModel->calcularEnergia(
            $this->request->getPost('CondicionLuz'),  // Condición de luz (por ejemplo, "Luz Solar Directa")
            $this->request->getPost('Tiempo'),        // Tiempo (en minutos)
            $luzSolar,                                // Luz solar en lux
            $temperatura,                             // Temperatura
            $humedad,                                 // Humedad
            $viento                                   // Viento
        );

        // Actualizar la simulación con los nuevos datos
        $this->simulacionesModel->update($id, [
            'CondicionLuz' => $this->request->getPost('CondicionLuz'),
            'Tiempo' => $this->request->getPost('Tiempo'),
            'CondicionesMeteorologicasID' => $this->request->getPost('CondicionesMeteorologicasID'),
            'EnergiaGenerada' => $energiaGenerada  // Guardar la nueva energía generada
        ]);

        // Redirigir con un mensaje de éxito
        return redirect()->to(base_url('admin/simulaciones'))
            ->with('success', 'Simulación actualizada exitosamente.');
    }


    /**
     * Elimina una simulación existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int $id ID de la simulación que se eliminará.
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $this->checkAdminAccess();

        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'ID no válido para eliminar.');
        }

        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', "Simulación con ID: $id no encontrada.");
        }

        try {
            if (!$this->simulacionesModel->delete($id)) {
                throw new \RuntimeException("No se pudo eliminar la simulación con ID: $id.");
            }

            return redirect()->to(base_url('admin/simulaciones'))->with('success', 'Simulación eliminada exitosamente.');
        } catch (\RuntimeException $e) {
            log_message('error', $e->getMessage());
            return redirect()->to(base_url('admin/simulaciones'))->with('error', $e->getMessage());
        }
    }
}
