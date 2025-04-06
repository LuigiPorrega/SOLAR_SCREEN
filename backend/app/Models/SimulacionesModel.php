<?php

namespace App\Models;

use App\Controllers\CondicionesMeteorologicas;
use CodeIgniter\Model;

class SimulacionesModel extends Model
{
    protected $table = 'Simulaciones';
    protected $primaryKey = 'ID';

    // Campos presentes en la tabla 'Simulaciones'
    protected $allowedFields = [
        'UsuarioID',
        'CondicionLuz',
        'EnergiaGenerada',
        'Fecha',
        'CondicionesMeteorologicasID',
        'FundaID',
    ];

    
    // Método para obtener las simulaciones con JOIN hacia Usuarios
    public function getSimulaciones($id = null)
    {
        // Si no se pasa un ID, obtenemos las simulaciones con paginación
        if ($id === null) {
            return $this->select('Simulaciones.*, Usuarios.Nombre AS UsuarioNombre')
                ->join('Usuarios', 'Usuarios.ID = Simulaciones.UsuarioID')
                ->join('CondicionesMeteorologicas', 'CondicionesMeteorologicas.ID = Simulaciones.CondicionesMeteorologicasID')
                ->paginate(10);
        }

        // Si se pasa un ID, obtenemos una simulación específica
        return $this->select('Simulaciones.*, Usuarios.Nombre AS UsuarioNombre, CondicionesMeteorologicas.*')
            ->join('Usuarios', 'Usuarios.id = Simulaciones.UsuarioID')
            ->join('CondicionesMeteorologicas', 'CondicionesMeteorologicas.ID = Simulaciones.CondicionesMeteorologicasID')
            ->find($id);
    }

    //Metodo para calcular las condiciones climaticas entre extremas o normales
    public function obtenerCondicionMeteorologica($condiciones)
    {
    
        // Definir umbrales para condiciones extremas
        $umbralTemperaturaExtrema = 35;
        $umbralHumedadExtrema = 90;
        $umbralVientoExtremo = 50;
        $umbralLuzSolarExtrema = 1000;

        // Evaluar las condiciones para determinar si son extremas
        $esExtrema = false;

        //LLamar a las condicionesMeteorologicas antes de compararalas

        // Verificar que las claves existan antes de usarlas
        if (!isset($condiciones['Temperatura']) || !isset($condiciones['Humedad']) || !isset($condiciones['Viento']) || !isset($condiciones['LuzSolar'])) {
            throw new \Exception("Faltan algunas condiciones meteorológicas necesarias.");
        }

        if ($condiciones['Temperatura'] >= $umbralTemperaturaExtrema) {
            $esExtrema = true;
        } elseif ($condiciones['Humedad'] >= $umbralHumedadExtrema) {
            $esExtrema = true;
        } elseif ($condiciones['Viento'] >= $umbralVientoExtremo) {
            $esExtrema = true;
        } elseif ($condiciones['LuzSolar'] >= $umbralLuzSolarExtrema) {
            $esExtrema = true;
        }

        return $esExtrema ? 'extrema' : 'normal';
    }


    // Función para justificar la funda fija o expansible
    public function obtenerJustificacionFunda($simulacion, $fundaPropuesta)
    {
        if (isset($simulacion['LuzSolar'], $simulacion['Temperatura'], $simulacion['Humedad'], $simulacion['Viento'])) {
            $condicionClimatica = $this->obtenerCondicionMeteorologica([
                'LuzSolar' => $simulacion['LuzSolar'],
                'Temperatura' => $simulacion['Temperatura'],
                'Humedad' => $simulacion['Humedad'],
                'Viento' => $simulacion['Viento'],
            ]);
        } else {
            // Manejar el caso cuando no están presentes
            // Esto podría ser un log de error o retornar un valor por defecto.
            $condicionClimatica = $this->obtenerCondicionMeteorologica([]);
        }

        $justificacion = '';

        if ($condicionClimatica === 'extrema') {
            if ($fundaPropuesta['TipoFunda'] === 'expansible') {
                $justificacion = 'La funda expansible se recomienda debido a las condiciones meteorológicas extremas (viento fuerte, temperaturas elevadas).';
            } else {
                $justificacion = 'La funda fija no es la opción más adecuada para condiciones extremas, pero es más resistente en condiciones normales.';
            }
        } else {
            if ($fundaPropuesta['TipoFunda'] === 'fija') {
                $justificacion = 'La funda fija es suficiente debido a que las condiciones meteorológicas son estables.';
            } else {
                $justificacion = 'La funda expansible podría ser innecesaria en condiciones estables, ya que las fundas fijas ofrecen mejor protección a largo plazo.';
            }
        }

        return $justificacion;
    }

    //Función para encontrar Fundas Similares
    public function getFundasSimilares($simulacionID)
    {
        try {
            // Primero obtenemos las condiciones meteorológicas de la simulación
            $simulacion = $this->db->table('Simulaciones')
                ->select('CondicionesMeteorologicasID')
                ->where('ID', $simulacionID)
                ->get()
                ->getRowArray();

            // Si no encontramos la simulación, devolver un array vacío
            if (!$simulacion) {
                return [];
            }

            // Obtener el CondicionesMeteorologicasID de la simulación
            $condicionesMeteorologicasID = $simulacion['CondicionesMeteorologicasID'];

            // Ahora obtener las fundas similares basadas en el mismo CondicionesMeteorologicasID
            $query = $this->db->table('ModelosFundas')
                ->select('ModelosFundas.*')
                ->join('Simulaciones', 'Simulaciones.FundaID = ModelosFundas.ID')
                ->where('Simulaciones.CondicionesMeteorologicasID', $condicionesMeteorologicasID)
                ->where('Simulaciones.ID !=', $simulacionID) // Excluir la misma simulación para evitar que se muestre a sí misma
                ->limit(4) // Límite de fundas similares
                ->get();

            // Retornar el resultado
            return $query->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error en getFundasSimilares: ' . $e->getMessage());
            return [];
        }
    }

    // Calcular energía generada según la condición de luz y el tiempo
    public function calcularEnergia($condicionLuz, $tiempo, $luzSolar, $temperatura, $humedad, $viento)
    {
        // Consulta para llamar a la función en la base de datos
        $query = $this->db->query("SELECT calcular_energia(?, ?, ?, ?, ?, ?) AS EnergiaGenerada", [
            $condicionLuz,   // Condición de luz, como 'Luz solar directa' o 'Luz artificial'
            $tiempo,         // Tiempo en minutos
            $luzSolar,       // Luz solar en lux
            $temperatura,    // Temperatura en °C
            $humedad,        // Humedad en porcentaje
            $viento          // Viento en km/h
        ]);

        // Obtener el resultado de la función
        return $query->getRow()->EnergiaGenerada;
    }

    // Método para obtener el total de registros (necesario para la paginación)
    public function obtenerTotalSimulaciones()
    {
        return $this->countAllResults();
    }
}
