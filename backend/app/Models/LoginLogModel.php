<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginLogModel extends Model
{
    protected $table = 'LoginLog';
    protected $primaryKey = 'ID';
    protected $allowedFields = ['UsuarioID', 'FechaHora', 'success', 'ip_address', 'user_agent', 'attempts', 'reason'];

    // Obtiene los registros de inicio de sesión
    public function getLoginLog($id = null, $perPage = null)
    {
        try {
            $this->select('LoginLog.*, Usuarios.Nombre, Usuarios.Username')
                ->join('Usuarios', 'LoginLog.UsuarioID = Usuarios.ID');

            // Si se proporciona un ID, buscar un registro específico
            if ($id !== null) {
                return $this->find($id);
            }

            // Si se especifica paginación, devolver registros paginados
            if ($perPage !== null) {
                return $this->paginate($perPage);
            }

            return $this->findAll();
        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return [];
        }
    }

    // Para insertar un nuevo registro con éxito o fallo
    public function logLoginAttempt($userId, $success, $ip, $userAgent, $attempts = 1, $reason = null)
    {
        // Si el login falla, ponemos UsuarioID a NULL
        $usuarioID = ($success == 1) ? $userId : null;

        return $this->insert([
            'UsuarioID' => $usuarioID,    // Usamos NULL si el login falla
            'FechaHora' => date('Y-m-d H:i:s'),
            'success' => $success,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'attempts' => $attempts,
            'reason' => $reason
        ]);
    }
}
