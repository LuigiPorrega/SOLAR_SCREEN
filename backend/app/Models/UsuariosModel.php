<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuariosModel extends Model
{
    protected $table = 'Usuarios';
    protected $primaryKey = 'ID';
    protected $allowedFields = [
        'Nombre',
        'Correo',
        'FechaNacimiento',
        'GoogleID',
        'Username',
        'PasswordHash',
        'Fecha_Registro',
        'Rol'
    ];

    //Verifica las credenciales del usuario
    // Model: UsuariosModel.php

    public function checkUser($username, $password)
    {
        $user = $this->where('Username', $username)->first();

        // Verificamos si el usuario existe y comparamos las contraseñas
        if ($user && hash('sha256', $password) === $user['PasswordHash']) {
            return $user;
        }

        return false;
    }


    //Verifica si un usuario tiene un rol específico
    public function hasRole($userId, $role)
    {
        $user = $this->find($userId);

        return $user && isset($user['Rol']) && $user['Rol'] === $role;
    }


    //Verifica si un usuario tiene permiso para acceder al backend
    public function canAccessBackend($userId)
    {
        return $this->hasRole($userId, 'admin');
    }
}
