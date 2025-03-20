<?php
namespace App\Models;

use CodeIgniter\Model;

class SimulacionesModel extends Model
{
    protected $table = 'Simulaciones';
    protected $primaryKey = 'ID';  
    protected $allowedFields = ['UsuarioID', 'CondicionLuz', 'EnergiaGenerada', 'Fecha'];
    protected $db;


    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();  // Conectamos a la base de datos
    }

    public function calcularEnergia($condicionLuz, $tiempo)
    {
        // Ejecuta la consulta que llama a la función calcular_energia
        $query = $this->db->query("SELECT calcular_energia(?, ?) AS EnergiaGenerada", [$condicionLuz, $tiempo]);
        
        // Retorna el valor calculado
        return $query->getRow()->EnergiaGenerada;
    }

    /**
     * Obtiene las simulaciones. Si el parámetro $id es proporcionado, devuelve una simulación específica.
     * Si no, devuelve todas las simulaciones o las paginadas si $perPage es proporcionado.
     *
     * @param int|null $id 
     * @param int|null $perPage 
     * @return array|object 
     */
    public function getSimulaciones($id = null, $perPage = null)
    {
        // Empezamos la consulta seleccionando las simulaciones y los datos del usuario asociado
        $this->select('Simulaciones.*, Usuarios.Nombre, Usuarios.Username')
             ->join('Usuarios', 'Simulaciones.UsuarioID = Usuarios.ID');

        try {
            if ($id !== null) {
                // Si se pasa un ID, devolvemos una sola simulación
                return $this->where('Simulaciones.ID', $id)->first();
            }

            // Si se pasa el parámetro de paginación
            if ($perPage !== null) {
                return $this->paginate($perPage); 
            }

            // Si no se pasa ID ni perPage, devolvemos todas las simulaciones
            return $this->findAll();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la paginación de simulaciones.
     * 
     * @param int $perPage 
     * @return string 
     */
    public function getSimulacionesPager($perPage)
    {
        return $this->pager->links();
    }
}
