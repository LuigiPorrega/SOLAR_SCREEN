<?php

namespace App\Models;

use CodeIgniter\Model;

class CondicionesMeteorologicasModel extends Model
{
    protected $table = 'CondicionesMeteorologicas';
    protected $primaryKey = 'ID';
    protected $allowedFields = ['Fecha', 'LuzSolar', 'Temperatura', 'Humedad', 'Viento'];

    // Función para obtener las condiciones meteorológicas, con soporte para filtros y paginación
    public function getCondiciones($id = null, $perPage = null)
    {
        try {
            // Ordenar por la fecha más reciente
            $this->orderBy('Fecha', 'DESC'); 

            // Si se pasa un ID, devolveremos una sola condición meteorológica
            if ($id !== null) {
                return $this->find($id);
            }

            // Si se pasa un parámetro de paginación, lo usamos
            if ($perPage !== null) {
                return $this->paginate($perPage);
            }

            // Si no se pasa ningún filtro, devolvemos todos los registros
            return $this->findAll();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return [];
        }
    }

    // Función para obtener el total de registros para paginación
    public function getCondicionesCount()
    {
        return $this->countAll();
    }
}