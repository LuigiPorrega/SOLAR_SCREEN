<?php

namespace App\Models;

use CodeIgniter\Model;

class SimulacionesModel extends Model
{
    protected $table = 'Simulaciones';
    protected $primaryKey = 'ID';  
    protected $allowedFields = ['UsuarioID', 'CondicionLuz', 'EnergiaGenerada', 'Fecha'];

    public function getSimulaciones($id = null, $perPage = null)
    {
        try {
            $this->select('Simulaciones.*, Usuarios.Nombre, Usuarios.Username')
                 ->join('Usuarios', 'Simulaciones.UsuarioID = Usuarios.ID');

            if ($id !== null) {
                return $this->find($id);
            }

            if ($perPage !== null) {
                return $this->paginate($perPage);
            }

            return $this->findAll();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return [];
        }
    }
}
