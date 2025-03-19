<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\LoginLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class LoginLog extends Controller
{
    protected $loginLogModel;

    public function __construct()
    {
        $this->loginLogModel = new LoginLogModel();
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
        $logs = $this->loginLogModel->findAll();

        $filename = 'login_log_' . date('Y-m-d') . 'Login_log.txt';
        $filepath = FCPATH . 'exports/' . $filename;

        $file = fopen($filepath, 'w');
        if ($file) {
            // Escribir encabezados
            fputcsv($file, ['ID', 'Usuario ID', 'Fecha y Hora', 'Exitoso', 'IP Address', 'User-Agent', 'Intentos', 'Razón'], "\t");

            // Escribir datos
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

            fclose($file);

            // Descargar el archivo
            return $this->response->download($filepath, null)->setFileName($filename);
        } else {
            return redirect()->back()->with('error', 'No se pudo crear el archivo de exportación.');
        }
    }
}
