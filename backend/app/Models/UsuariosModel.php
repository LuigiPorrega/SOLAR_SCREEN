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

    // Verifica las credenciales del usuario usando SHA256
    public function checkUser($username, $password)
    {
        // Buscar al usuario en la base de datos por su nombre de usuario
        $user = $this->where('Username', $username)->first();

        // Si el usuario existe y la contraseña es válida (SHA256)
        if ($user && hash('sha256', $password) === $user['PasswordHash']) {
            return $user;
        }

        return false;
    }

    // Obtiene un usuario por ID, incluyendo la edad calculada
    public function obtenerUsuarioConEdad($id)
    {
        $builder = $this->builder();

        // Realiza la consulta SQL para obtener el usuario y calcular su edad
        $builder->select('Usuarios.*, 
                          FLOOR(DATEDIFF(CURRENT_DATE, FechaNacimiento) / 365) AS Edad')
            ->where('ID', $id);

        $query = $builder->get();
        return $query->getRowArray(); // Devuelve el primer usuario con edad calculada
    }

    // Verifica si un usuario tiene un rol específico
    public function hasRole($userId, $role)
    {
        $user = $this->find($userId);

        return $user && isset($user['Rol']) && $user['Rol'] === $role;
    }

    // Verifica si un usuario tiene permiso para acceder al backend
    public function canAccessBackend($userId)
    {
        return $this->hasRole($userId, 'admin');
    }
}
