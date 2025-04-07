<?php

namespace App\Models;

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
        'Tiempo',
        'Fecha',
        'CondicionesMeteorologicasID',
        'FundaID',
    ];

    // Método para obtener las simulaciones con JOIN hacia Usuarios
    public function getSimulaciones($id = null)
    {
        // Si no se pasa un ID, obtenemos las simulaciones con paginación
        if ($id === null) {
            return $this->select('Simulaciones.ID, Simulaciones.UsuarioID, Simulaciones.CondicionLuz, Simulaciones.EnergiaGenerada, Simulaciones.Tiempo, Simulaciones.Fecha, Simulaciones.CondicionesMeteorologicasID, Simulaciones.FundaID, Usuarios.Nombre AS UsuarioNombre')
                ->join('Usuarios', 'Usuarios.ID = Simulaciones.UsuarioID')
                ->join('CondicionesMeteorologicas', 'CondicionesMeteorologicas.ID = Simulaciones.CondicionesMeteorologicasID')
                ->paginate(10);
        }

        // Si se pasa un ID, obtenemos una simulación específica
        return $this->select('Simulaciones.ID, Simulaciones.UsuarioID, Simulaciones.CondicionLuz, Simulaciones.EnergiaGenerada, Simulaciones.Tiempo, Simulaciones.Fecha, Simulaciones.CondicionesMeteorologicasID, Simulaciones.FundaID, CondicionesMeteorologicas.LuzSolar, CondicionesMeteorologicas.Temperatura, CondicionesMeteorologicas.Humedad, CondicionesMeteorologicas.Viento, Usuarios.Nombre AS UsuarioNombre')
            ->join('Usuarios', 'Usuarios.ID = Simulaciones.UsuarioID')
            ->join('CondicionesMeteorologicas', 'CondicionesMeteorologicas.ID = Simulaciones.CondicionesMeteorologicasID')
            ->find($id);
    }


    // Función para calcular la energía generada según la condición de luz y el tiempo
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

    // Obtener fundas relacionadas (según condiciones, tipo, o capacidad)
    public function obtenerFundasSimilares($condicionLuz, $capacidadCarga, $fundaPropuestaID)
    {
        return $this->db->table('ModelosFundas')
            ->select('ModelosFundas.*')
            ->distinct()
            ->where('ModelosFundas.CapacidadCarga >=', $capacidadCarga)
            ->orWhere('ModelosFundas.TipoFunda', $condicionLuz)
            ->whereNotIn('ModelosFundas.ID', [$fundaPropuestaID])
            ->limit(4)
            ->get()
            ->getResultArray();
    }

    // Método para determinar la funda recomendada tomando en cuenta la energía generada y la capacidad de carga
    public function obtenerFundaRecomendada($energiaGenerada, $capacidadCarga)
    {
        // Definimos los umbrales en base a la energía generada (en kWh) y la capacidad de carga (en kg).
        $umbralEnergia = 1.5;  // Energía mínima en kWh para considerar una funda expansible
        $umbralCapacidadCarga = 20;  // Capacidad mínima en kg para considerar una funda expansible

        // Calculamos la relación entre la energía generada y la capacidad de carga
        // Usamos un valor de proporción para determinar si la energía es suficiente para justificar una funda expansible
        $relacionEnergiaCarga = $energiaGenerada / max($capacidadCarga, 1); // Evitamos división por 0

        // Si la relación de energía generada por la carga es baja, se recomienda una funda fija
        if ($relacionEnergiaCarga < $umbralEnergia) {
            return 'fija';
        }

        // Si la energía generada es suficiente y la capacidad de carga es adecuada, se recomienda una funda expansible
        if ($energiaGenerada >= $umbralEnergia && $capacidadCarga >= $umbralCapacidadCarga) {
            return 'expansible';
        }

        // Caso en el que se tenga poca capacidad de carga pero energía alta (por ejemplo, puede que se recomiende una funda fija en este caso)
        return 'fija';
    }

    // Método para obtener la justificación de la funda recomendada de forma dinámica
    public function generarJustificacionFunda($energiaGenerada, $capacidadCarga)
    {
        // Definir umbrales dinámicos de energía y capacidad de carga
        $umbralEnergia = 1.5;  // Energía mínima en kWh para considerar una funda expansible
        $umbralCapacidadCarga = 20;  // Capacidad mínima en kg para considerar una funda expansible

        // Calculamos la relación entre la energía generada y la capacidad de carga
        $relacionEnergiaCarga = $energiaGenerada / max($capacidadCarga, 1); // Evitamos división por 0

        // Caso cuando la relación de energía generada por la carga es baja
        if ($relacionEnergiaCarga < $umbralEnergia) {
            return "La funda recomendada es una funda fija, ya que la relación entre la energía generada y la capacidad de carga es baja. Esto indica que no se necesita una funda expansible, y se requiere una funda de soporte más estable y constante para cargas pequeñas o moderadas.";
        }

        // Caso cuando la energía generada es suficientemente alta y la capacidad de carga es adecuada
        if ($energiaGenerada >= $umbralEnergia && $capacidadCarga >= $umbralCapacidadCarga) {
            return "La funda recomendada es una funda expansible, ya que la energía generada es suficientemente alta para justificar una funda flexible y adaptable, y la capacidad de carga también es suficiente para soportar cargas mayores de manera eficiente. Una funda expansible proporciona mayor flexibilidad en la adaptación a diferentes cargas.";
        }

        // Caso cuando la energía generada es alta pero la capacidad de carga es insuficiente
        if ($energiaGenerada >= $umbralEnergia && $capacidadCarga < $umbralCapacidadCarga) {
            return "La funda recomendada es una funda fija, ya que aunque la energía generada es alta, la capacidad de carga no es suficiente para soportar cargas mayores de manera segura. Una funda fija será más adecuada para cargas menores, ofreciendo un soporte constante y estable.";
        }

        // Caso cuando la energía generada es baja pero la capacidad de carga es adecuada
        if ($energiaGenerada < $umbralEnergia && $capacidadCarga >= $umbralCapacidadCarga) {
            return "La funda recomendada es una funda fija, ya que aunque la capacidad de carga es adecuada para cargas mayores, la energía generada es baja. En este caso, no se justifica el uso de una funda expansible, y una funda fija es suficiente para cargas pequeñas o moderadas.";
        }

        // Caso por defecto si no se cumple ninguna condición
        return "La funda recomendada es una funda estándar, adecuada para la mayoría de las condiciones generadas. La energía y la capacidad de carga no requieren una funda expansible, por lo que una funda estándar puede ser suficiente.";
    }


    // Método para obtener el total de registros (necesario para la paginación)
    public function obtenerTotalSimulaciones()
    {
        return $this->countAllResults();
    }
}
