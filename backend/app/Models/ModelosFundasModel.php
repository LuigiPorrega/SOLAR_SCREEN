<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelosFundasModel extends Model
{
    // Definir el nombre de la tabla
    protected $table = 'ModelosFundas';

    // Definir la clave primaria
    protected $primaryKey = 'ID';

    // Definir los campos permitidos para inserción y actualización
    protected $allowedFields = [
        'Nombre',
        'Tamaño',
        'CapacidadCarga',
        'Expansible',
        'ImagenURL',
        'TipoFunda',
        'FechaCreacion',
    ];

    // Función para obtener todos los modelos de fundas
    public function getModelosFundas($id = null)
    {
        try {
            // Consulta básica para obtener los datos
            $query = $this->db->table('ModelosFundas')
                ->select('ModelosFundas.ID, ModelosFundas.Nombre, ModelosFundas.Tamaño, ModelosFundas.CapacidadCarga, ModelosFundas.Expansible, ModelosFundas.ImagenURL, ModelosFundas.TipoFunda, ModelosFundas.FechaCreacion')
                ->orderBy('ModelosFundas.ID', 'DESC');

            // Si se proporciona un ID, filtrar por ese ID específico
            if ($id !== null) {
                $query->where('ModelosFundas.ID', $id);
                return $query->get()->getRowArray();
            }

            // Devolver todos los registros
            return $query->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return [];
        }
    }


    // Función para obtener los modelos de fundas activos 
    public function getModelosFundasActivos()
    {
        try {
            $query = $this->db->table('ModelosFundas')
                ->select('ModelosFundas.ID, ModelosFundas.Nombre, ModelosFundas.Tamaño, ModelosFundas.CapacidadCarga, ModelosFundas.Expansible, ModelosFundas.ImagenURL, ModelosFundas.TipoFunda, ModelosFundas.FechaCreacion')
                ->where('ModelosFundas.Activo', 1) 
                ->orderBy('ModelosFundas.ID', 'DESC');

            return $query->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return [];
        }
    }

     // Función para obtener una funda según la condición de luz
     public function getFundaPorCondicionLuz($condicionLuz)
     {
         if ($condicionLuz == 'Luz solar directa') {
             // Si la condición es luz solar directa, buscamos una funda expandible
             return $this->where('Expansible', 1)->first(); // Devuelve la primera funda expandible
         } else {
             // Si no es luz solar directa, buscamos una funda fija
             return $this->where('Expansible', 0)->first(); // Devuelve la primera funda fija
         }
     }
}
