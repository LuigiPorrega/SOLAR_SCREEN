<?php

namespace App\Models;

use CodeIgniter\Model;

class FundasProveedoresModel extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'Fundas_Proveedores';

    //Clave Primaria
    protected $primaryKey = 'ID';

    // Campos permitidos para insertar
    protected $allowedFields = ['FundaID', 'ProveedorID'];
    protected $useAutoIncrement = true;
    protected $insertID = 0;

    // Método para insertar la relación entre proveedor y funda
    public function insertRelation($proveedorID, $fundaID)
    {
        // Verificar si la relación ya existe
        $existingRelation = $this->where('ProveedorID', $proveedorID)
            ->where('FundaID', $fundaID)
            ->first();
        if (!$existingRelation) {
            // Insertar la relación entre el proveedor y la funda
            return $this->insert([
                'FundaID' => $fundaID,
                'ProveedorID' => $proveedorID,
            ]);
        }
        return true;
    }

    //Metodo para mostrar todas las fundas
    public function getFundas()
    {
        return $this->select('ModelosFundas.*')
            ->join('ModelosFundas', 'Fundas_Proveedores.FundaID = ModelosFundas.ID')
            ->findAll();
    }

    //Metodo para mostrar todos los proveedores
    public function getProveedores()
    {
        return $this->select('Proveedores.*')
            ->join('Proveedores', 'Fundas_Proveedores.ProveedorID = Proveedores.ID', 'left')
            ->groupBy('Proveedores.ID')  
            ->findAll();
    }

    // Método para obtener las fundas de un proveedor
    public function getFundasByProveedor($proveedorID)
    {

        return $this->select('ModelosFundas.*')
            ->join('ModelosFundas', 'Fundas_Proveedores.FundaID = ModelosFundas.ID')
            ->where('Fundas_Proveedores.ProveedorID', $proveedorID)
            ->findAll();
    }

    // Método para obtener los proveedores de una funda
    public function getProveedoresByFunda($fundaID)
    {

        return $this->select('Proveedores.*')
            ->join('Proveedores', 'Fundas_Proveedores.ProveedorID = Proveedores.ID')
            ->where('Fundas_Proveedores.FundaID', $fundaID)
            ->findAll();
    }

    public function deleteFundasByProveedor($proveedorID, $fundasIDs)
    {

        $query = $this->db->table('Fundas_Proveedores')
            ->where('ProveedorID', $proveedorID);
       // Si se pasan múltiples IDs de fundas, usamos orWhere para agregarlas
        foreach ($fundasIDs as $fundaID) {
            $query->orWhere('FundaID', $fundaID);
        }

        return $query->delete();
    }

    public function deleteProveedorByFunda($fundaID, $proveedoresIDs)
{
    $query = $this->db->table('Fundas_Proveedores')
        ->where('FundaID', $fundaID); 

    // Si se pasan múltiples IDs de proveedores, usamos orWhere para agregarlos
    foreach ($proveedoresIDs as $proveedorID) {
        $query->orWhere('ProveedorID', $proveedorID);
    }

    return $query->delete();
}
}
