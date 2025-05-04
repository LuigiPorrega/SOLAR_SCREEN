<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginLogModel extends Model
{
    protected $table = 'LoginLog';
    protected $primaryKey = 'ID';
    protected $allowedFields = ['UsuarioID', 'FechaHora', 'Success', 'IpAddress', 'UserAgent', 'AttemptDate', 'Attempts', 'Reason'];

    // Obtener los intentos exitosos y fallidos agrupados
    public function getLoginAttemptsGrouped($perPage = null, $page = 1)
    {
        try {
            // Intentos exitosos
            $this->select('LoginLog.*, Usuarios.Nombre, Usuarios.Username')
                ->join('Usuarios', 'LoginLog.UsuarioID = Usuarios.ID')
                ->where('Success', 1)  // Solo intentos exitosos
                ->orderBy('FechaHora', 'DESC');
            $successfulLogs = ($perPage !== null) ? $this->paginate($perPage, 'default', $page) : $this->findAll();

            // Intentos fallidos agrupados por UsuarioID
            $this->select('UsuarioID, COUNT(*) AS numeros_intentos, MAX(FechaHora) AS ultima_fecha, IpAddress, UserAgent')
                ->where('Success', 0)  // Solo intentos fallidos
                ->groupBy('UsuarioID')  // Agrupar por UsuarioID
                ->orderBy('ultima_fecha', 'DESC');  // Ordenar por la última fecha de intento fallido
            $failedLogs = ($perPage !== null) ? $this->paginate($perPage, 'default', $page) : $this->findAll();

            // Devolver los dos conjuntos de datos
            return [
                'successfulLogs' => $successfulLogs,
                'failedLogs' => $failedLogs
            ];
        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return [];
        }
    }


    // Para insertar un nuevo registro con éxito o fallo
    public function logLoginAttempt($userId, $success, $ip, $userAgent, $attempts = 1, $reason = null)
    {
        // Si el login falla, incrementamos el número de intentos
        if ($success == 0) {
            $attempts++;
        }

        // Si el login falla, ponemos UsuarioID a NULL
        $usuarioID = ($success == 1) ? $userId : null;

        // Si el login es exitoso, establecemos un mensaje en reason
        if ($success == 1 && $reason === null) {
            $reason = 'Login exitoso';
        }

        $data = [
            'UsuarioID' => $usuarioID,
            'FechaHora' => date('Y-m-d H:i:s'),
            'Success' => $success,
            'IpAddress' => $ip,
            'UserAgent' => $userAgent,
            'AttemptDate' => date('Y-m-d'),
            'Attempts' => $attempts,
            'Reason' => $reason
        ];

        // Log para verificar los datos antes de insertar
        log_message('debug', 'Datos para insertar en LoginLog: ' . print_r($data, true));

        // Realizar la inserción y devolver el resultado
        return $this->insert($data);
    }
    

    // Función de agrupación de intentos fallidos
    public function getFailedLoginAttempts($perPage = null)
    {
        try {
            // Seleccionamos los campos que necesitamos, agrupamos por UsuarioID (puede ser NULL) y contamos los intentos fallidos
            $this->select('UsuarioID, COUNT(*) AS numeros_intentos, MAX(FechaHora) AS ultima_fecha, IpAddress, UserAgent')
                ->where('Success', 0)  // Solo queremos los intentos fallidos
                ->groupBy('UsuarioID')
                ->orderBy('ultima_fecha', 'DESC');  // Ordenamos por la última fecha de intento fallido

            // Si se especifica paginación, devolver registros paginados
            if ($perPage !== null) {
                return $this->paginate($perPage);
            }

            // Si no se da paginación, obtener todos los registros
            return $this->findAll();
        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return [];
        }
    }
}
