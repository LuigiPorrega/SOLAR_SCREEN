<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UsuariosModel;
use App\Models\LoginLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class LoginLog extends Controller
{
    protected $loginLogModel;
    protected $usuariosModel;

    public function __construct()
    {
        $this->loginLogModel = new LoginLogModel();
        $this->usuariosModel = new UsuariosModel();
    }

    // Método de inicio de sesión
    public function login()
    {
        // Obtener los datos del formulario
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Buscar al usuario por su nombre de usuario
        $usuario = $this->usuariosModel->where('Username', $username)->first();

        if ($usuario) {
            // Verificar la contraseña con SHA2
            // Usamos SHA2 (256) para comparar la contraseña
            $hashedPassword = hash('sha256', $password);

            if ($hashedPassword === $usuario['PasswordHash']) {
                // Login exitoso
                $this->loginLogModel->logLoginAttempt($usuario['ID'], 1, $this->request->getIPAddress(), $this->request->getUserAgent(), 1, null);
                return redirect()->to('/dashboard'); // Redirigir al dashboard o página principal
            } else {
                // Contraseña incorrecta
                $this->loginLogModel->logLoginAttempt(null, 0, $this->request->getIPAddress(), $this->request->getUserAgent(), 1, 'Contraseña incorrecta');
                return redirect()->to('/login')->with('error', 'Contraseña incorrecta');
            }
        } else {
            // Usuario no encontrado
            $this->loginLogModel->logLoginAttempt(null, 0, $this->request->getIPAddress(), $this->request->getUserAgent(), 1, 'Usuario no encontrado');
            return redirect()->to('/login')->with('error', 'Usuario no encontrado');
        }
    }

    public function index()
    {
        $data = [
            'title' => 'Registros de Inicio de Sesión',
            'logs' => $this->loginLogModel->getLoginLog(null, 20) // Obtiene 20 registros por página
        ];

        return view('templates/header', $data)
            . view('loginlog/index')
            . view('templates/footer');
    }

    public function view($id = null)
    {
        $log = $this->loginLogModel->getLoginLog($id);

        if (!$log) {
            throw new PageNotFoundException('No se encontró el registro de inicio de sesión especificado.');
        }

        $data = [
            'title' => 'Detalles del Registro de Inicio de Sesión',
            'log' => $log
        ];

        return view('templates/header', $data)
            . view('loginlog/view')
            . view('templates/footer');
    }

    public function export()
{
    // Obtiene todos los logs de la base de datos
    $logs = $this->loginLogModel->findAll();

    // Definir el archivo como 'login_log.txt'
    $filename = 'login_log.txt';
    $filepath = FCPATH . 'export/' . $filename;

    // Abre el archivo en modo append (agregar al final)
    $file = fopen($filepath, 'a');
    if ($file) {
        // Escribir los encabezados solo si el archivo está vacío (para evitar duplicación)
        if (filesize($filepath) == 0) {
            fputcsv($file, ['ID', 'Usuario ID', 'Fecha y Hora', 'Exitoso', 'IP Address', 'User-Agent', 'Intentos', 'Razón'], "\t");
        }

        // Escribir los logs en el archivo
        foreach ($logs as $log) {
            fputcsv($file, [
                $log['ID'],
                $log['UsuarioID'],
                $log['FechaHora'],
                $log['success'],
                $log['ip_address'],
                $log['user_agent'],
                $log['attempts'],
                $log['reason']
            ], "\t");
        }

        // Cierra el archivo
        fclose($file);

        // Regresar el archivo para descargar
        return $this->response->download($filepath, null)->setFileName($filename);
    } else {
        return redirect()->back()->with('error', 'No se pudo crear el archivo de exportación.');
    }
}
}
