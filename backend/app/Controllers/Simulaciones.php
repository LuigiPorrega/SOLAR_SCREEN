<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\SimulacionesModel;
use App\Models\UsuariosModel;
use App\Models\CondicionesMeteorologicasModel;
use App\Exceptions\PermissionException;
use App\Models\ModelosFundasModel;
use CodeIgniter\HTTP\RedirectResponse;
use \TCPDF;

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

    private function checkAdminAccess()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId || !$this->usuariosModel->canAccessBackend($userId)) {
            throw PermissionException::forUnauthorizedAccess();
        }
    }

    public function index()
    {
        // Verificamos si el tiempo fue enviado por el usuario
        $tiempo = $this->request->getPost('tiempo');  // Suponiendo que el campo en el formulario se llama 'tiempo'

        // Validamos que el tiempo sea un número y mayor que 0
        if (!is_numeric($tiempo) || $tiempo <= 0) {
            // Si el tiempo no es válido, asignamos un valor predeterminado o devolvemos un error
            $tiempo = 60; // Asignamos un valor predeterminado, 60 minutos
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

    public function view($id = null)
    {
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

        // Extraer las condiciones meteorológicas del resultado de la base de datos
        $condiciones = [
            'LuzSolar' => $condicionesMeteorologicas['LuzSolar'] ?? null,
            'Temperatura' => $condicionesMeteorologicas['Temperatura'] ?? null,
            'Humedad' => $condicionesMeteorologicas['Humedad'] ?? null,
            'Viento' => $condicionesMeteorologicas['Viento'] ?? null
        ];

        // Verificar que todas las condiciones necesarias estén presentes
        if (in_array(null, $condiciones, true)) {
            throw new \Exception("Faltan algunas condiciones meteorológicas necesarias.");
        }

        // Obtener la condición climática usando el modelo de simulaciones
        $condicionClimatica = $this->simulacionesModel->obtenerCondicionMeteorologica($condiciones);

        // Obtener la funda propuesta
        $fundaPropuesta = $this->fundasModel->find($simulacion['FundaID']);

        // Obtener otras 4 fundas propuestas (basadas en condiciones similares)
        $otrasFundasPropuestas = $this->simulacionesModel->getFundasSimilares($simulacion['ID']);

        // Obtener la justificación de la funda
        $justificacionFunda = $this->simulacionesModel->obtenerJustificacionFunda($simulacion, $fundaPropuesta);

        // Preparar los datos para la vista
        $data = [
            'simulacion' => $simulacion,
            'condicionesMeteorologicas' => $condicionesMeteorologicas,
            'fundaPropuesta' => $fundaPropuesta,
            'otrasFundasPropuestas' => $otrasFundasPropuestas,
            'justificacionFunda' => $justificacionFunda,
            'condicionClimatica' => $condicionClimatica,
            'title' => 'Detalle de la Simulación',
        ];

        // Mostrar la vista
        return view('templates/header', $data)
            . view('simulaciones/view')
            . view('templates/footer');
    }




    public function new()
    {
        $this->checkAdminAccess();

        helper('form');
        return view('templates/header', ['title' => 'Crear Simulación'])
            . view('simulaciones/create')
            . view('templates/footer');
    }

    public function create()
    {
        $this->checkAdminAccess(); // Verifica si el usuario tiene permisos de administrador

        helper('form');

        // Obtenemos el ID del usuario logueado desde la sesión
        $session = session();
        $userId = $session->get('user_id');

        // Si no se ha recuperado el ID del usuario logueado, redirigimos
        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Debes estar logueado para crear una simulación.');
        }

        // Obtenemos los datos del formulario
        $data = $this->request->getPost(['CondicionLuz', 'Tiempo']); // Obtenemos la condición de luz y el tiempo

        // Validar los datos del formulario
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
        $data['UsuarioID'] = $userId; // Asociamos el ID del usuario logueado con la simulación

        // Obtener la funda más apropiada para la simulación (basado en la condición de luz)
        $funda = $this->fundasModel->getFundaPorCondicionLuz($data['CondicionLuz']); // Necesitas una función que obtenga la funda según la luz

        // Añadir la funda recomendada al array de datos de simulación
        $data['FundaID'] = $funda ? $funda['ID'] : null;

        // Guardamos los datos en la base de datos
        if (!$this->simulacionesModel->save($data)) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'Error al crear la simulación.');
        }

        return redirect()->to(base_url('admin/simulaciones'))->with('success', 'Simulación creada exitosamente.');
    }

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

    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        helper('form');

        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'ID de simulación no válido.');
        }

        $data = $this->request->getPost(['CondicionLuz', 'EnergiaGenerada']);

        if (!$this->validateData($data, [
            'CondicionLuz' => 'required|string|max_length[50]',
            'EnergiaGenerada' => 'required|decimal',
        ])) {
            return redirect()->to(base_url("admin/simulaciones/update/$id"))->withInput()->with('error', 'Validación fallida');
        }

        $simulacion = $this->simulacionesModel->find($id);
        if (!$simulacion) {
            return redirect()->to(base_url('admin/simulaciones'))->with('error', 'Simulación no encontrada.');
        }

        if (empty($data['EnergiaGenerada'])) {
            $energiaGenerada = $this->simulacionesModel->calcularEnergia($data['CondicionLuz'], $simulacion['Tiempo']);
            $data['EnergiaGenerada'] = $energiaGenerada;
        }

        $data['ID'] = (int)$id;
        $data['Fecha'] = date('Y-m-d');

        if (!$this->simulacionesModel->save($data)) {
            return redirect()->to(base_url("admin/simulaciones/update/$id"))->with('error', 'Error al actualizar.');
        }

        return redirect()->to(base_url('admin/simulaciones'))->with('success', 'Simulación actualizada exitosamente.');
    }

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

    /**
     * Descarga la simulación en formato PDF.
     *
     * @param int $id ID de la simulación
     */
    /* public function generarPDF($id)
    {
        $simulacion = $this->simulacionesModel->getSimulaciones($id);

        if (!$simulacion) {
            throw new PageNotFoundException("Simulación no encontrada.");
        }

        $funda = $this->fundasModel->find($simulacion['FundaID']);

        // Configuración y generación del PDF usando TCPDF
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'Simulación #' . $simulacion['ID'], '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, 'Condición de luz: ' . $simulacion['CondicionLuz'], '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, 'Energía generada: ' . $simulacion['EnergiaGenerada'], '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, 'Funda recomendada: ' . $funda['Nombre'], '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, 'Capacidad de carga: ' . $funda['CapacidadCarga'], '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, 'Tipo de funda: ' . $funda['TipoFunda'], '', 0, 'L', true, 0, false, false, 0);

        // Imprimir la imagen de la funda
        $pdf->Image($funda['ImagenURL'], 15, 80, 30, 30, '', '', '', false, 300, '', false, false, 0, false, false, false);

        $pdf->Output('simulacion_' . $simulacion['ID'] . '.pdf', 'D');
    }*/
}
