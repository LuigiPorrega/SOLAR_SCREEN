<?php

namespace App\Models;

use CodeIgniter\Model;

class IdeasModel extends Model
{
    protected $table = 'Ideas';
    protected $primaryKey = 'ID';
    protected $allowedFields = ['UsuarioID', 'Titulo', 'Descripcion', 'FechaCreacion'];

    public function getIdeas($id = null, $perPage = null)
    {
        try {
            $this->select('Ideas.*, Usuarios.Nombre, Usuarios.Username')
                 ->join('Usuarios', 'Ideas.UsuarioID = Usuarios.ID')
                 ->orderBy('Ideas.ID', 'DESC');

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
