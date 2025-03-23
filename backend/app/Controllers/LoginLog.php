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

    // Constructor: Inicializa los modelos necesarios para el controlador.
    public function __construct()
    {
        $this->loginLogModel = new LoginLogModel(); // Modelo para manejar los registros de logins
        $this->usuariosModel = new UsuariosModel(); // Modelo para manejar los usuarios
    }

    /**
     * Método de inicio de sesión
     *
     * Este método se llama cuando un usuario intenta iniciar sesión.
     * Verifica si el nombre de usuario y la contraseña son correctos.
     * Si el login es exitoso, registra el intento de login en el log de inicio de sesión.
     * Si el login falla (usuario no encontrado o contraseña incorrecta), también se registra el intento fallido.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function login()
    {
        // Obtener los datos del formulario (usuario y contraseña)
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
                // Registrar el intento de login exitoso en la base de datos
                $this->loginLogModel->logLoginAttempt($usuario['ID'], 1, $this->request->getIPAddress(), $this->request->getUserAgent(), 1, null);
                return redirect()->to('/dashboard'); // Redirigir al dashboard o página principal
            } else {
                // Contraseña incorrecta
                // Registrar el intento de login fallido (contraseña incorrecta)
                $this->loginLogModel->logLoginAttempt(null, 0, $this->request->getIPAddress(), $this->request->getUserAgent(), 1, 'Contraseña incorrecta');
                return redirect()->to('/login')->with('error', 'Contraseña incorrecta');
            }
        } else {
            // Usuario no encontrado
            // Registrar el intento de login fallido (usuario no encontrado)
            $this->loginLogModel->logLoginAttempt(null, 0, $this->request->getIPAddress(), $this->request->getUserAgent(), 1, 'Usuario no encontrado');
            return redirect()->to('/login')->with('error', 'Usuario no encontrado');
        }
    }

    /**
     * Método que muestra los registros de inicio de sesión
     *
     * Este método obtiene los registros de login, tanto exitosos como fallidos, agrupados y los pasa a la vista.
     * También se pueden paginar los registros (en este caso, se están mostrando 20 por página).
     *
     * @return string
     */
    public function index()
    {
        // Obtener los registros de login agrupados y con paginación
        $data = [
            'title' => 'Registros de Inicio de Sesión',  // Título de la página
            'logs' => $this->loginLogModel->getLoginAttemptsGrouped(null, 20) // Obtiene 20 registros por página
        ];

        // Cargar la vista con los registros de login
        return view('templates/header', $data)
            . view('loginlog/index') // Vista para los registros de login
            . view('templates/footer'); // Pie de página
    }

    /**
     * Método para exportar los registros de inicio de sesión a un archivo CSV
     *
     * Este método obtiene todos los registros de login de la base de datos,
     * los formatea como un archivo CSV y lo ofrece para ser descargado.
     * Los campos en el CSV están rodeados de comillas para evitar problemas con comas en los datos.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function export()
    {
        // Obtiene todos los logs de la base de datos
        $logs = $this->loginLogModel->findAll();

        // Definir el archivo como 'login_log.csv'
        $filename = 'login_log.csv';
        $filepath = FCPATH . 'export/' . $filename;

        // Abre el archivo en modo de escritura (si el archivo no existe, se creará; si existe, se sobrescribirá)
        $file = fopen($filepath, 'w');
        if ($file) {
            // Escribir los encabezados solo si el archivo está vacío (para evitar duplicación)
            fputcsv($file, ['ID', 'Usuario ID', 'Fecha y Hora', 'Exitoso', 'IP Address', 'User-Agent', 'Intentos', 'Razón'], ",");

            // Escribir los logs en el archivo con comillas alrededor de cada valor para asegurar que los datos sean fáciles de leer
            foreach ($logs as $log) {
                // Escribir los datos en formato CSV, asegurando que los campos estén entre comillas
                fputcsv($file, [
                    '"' . esc($log['ID']) . '"',                    // ID
                    '"' . esc($log['UsuarioID'] ?? '') . '"',        // Usuario ID
                    '"' . esc($log['FechaHora']) . '"',              // Fecha y Hora
                    '"' . (esc($log['Success']) == 1 ? 'Exitoso' : 'Fallido') . '"', // Exitoso
                    '"' . esc($log['IpAddress']) . '"',              // IP Address
                    '"' . esc($log['UserAgent']) . '"',              // User-Agent
                    '"' . esc($log['Attempts']) . '"',               // Intentos
                    '"' . esc($log['Reason'] ?? 'No disponible') . '"' // Razón
                ], ",");
            }

            // Cierra el archivo
            fclose($file);

            // Regresar el archivo para descargar
            return $this->response->download($filepath, null)->setFileName($filename);
        } else {
            // Si no se puede crear el archivo, redirige con un mensaje de error
            return redirect()->back()->with('error', 'No se pudo crear el archivo de exportación.');
        }
    }
}
