<?php

namespace App\Models;

use CodeIgniter\Model;

class CarritoModel extends Model
{
    protected $table = 'Carrito';
    protected $primaryKey = 'ID';

    protected $allowedFields = [
        'UsuarioId',
        'ModelosFundasId',
        'Cantidad',
        'Precio',
        'Creado_en',
    ];

    protected $useTimestamps = false;
}
