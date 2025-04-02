<?php
namespace App\Models;

use CodeIgniter\Model;

class ProveedoresModel extends Model
{
    protected $table = 'Proveedores';
    protected $primaryKey = 'ID';
    protected $allowedFields = [
        'Nombre',
        'Pais',
        'ContactoNombre',
        'ContactoTelefono',
        'ContactoEmail',
        'SitioWeb',
        'Direccion',
        'Descripcion',
        'FechaCreacion',
        'Activo',
    ];

    // Función para obtener todos los proveedores
    public function getProveedores($id = null)
    {
        try {
            $query = $this->db->table('Proveedores')
                ->select('Proveedores.ID, Proveedores.Nombre, Proveedores.Pais, Proveedores.ContactoNombre, Proveedores.ContactoTelefono, Proveedores.ContactoEmail, Proveedores.SitioWeb, Proveedores.Direccion, Proveedores.Descripcion, Proveedores.FechaCreacion, Proveedores.Activo')
                ->orderBy('Proveedores.ID', 'DESC');

            if ($id !== null) {
                $query->where('Proveedores.ID', $id);
                return $query->get()->getRowArray();
            }

            return $query->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return [];
        }
    }
}
