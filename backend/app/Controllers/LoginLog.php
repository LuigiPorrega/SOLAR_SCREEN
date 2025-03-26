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
        $this->loginLogModel = new LoginLogModel(); // Modelo para manejar los registros de logins
        $this->usuariosModel = new UsuariosModel(); // Modelo para manejar los usuarios
    }

    /**
     * Verifica si el usuario tiene acceso de administrador
     * Lanza una excepción si no tiene permisos.
     */
    private function checkAdminAccess()
    {
        $session = session();
        if (!$session->get('user_id') || !$this->usuariosModel->canAccessBackend($session->get('user_id'))) {
            // Si no tiene permisos de administrador, redirige al inicio
            return redirect()->to('/'); // Redirige a la página de inicio
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
        // Verifica si el usuario tiene acceso de administrador
        $this->checkAdminAccess();

        $page = $this->request->getVar('page') ?: 1;
        $perPage = 100; 

        // Obtener los registros de login agrupados y con paginación
        $data = [
            'title' => 'Registros de Inicio de Sesión',
            'logs' => $this->loginLogModel->getLoginAttemptsGrouped($perPage, $page)
        ];

        // Cargar la vista con los registros de login
        return view('templates/header', $data)
            . view('loginlog/index')
            . view('templates/footer');
    }

    /**
     * Método para exportar los registros de inicio de sesión a un archivo CSV
     *
     * Este método obtiene todos los registros de inicio de sesión desde la base de datos,
     * los formatea como un archivo CSV y lo ofrece para su descarga.
     * El archivo CSV es generado en una carpeta externa llamada `private_exports`, 
     * la cual está ubicada fuera del directorio `public`, garantizando que solo los administradores 
     * puedan acceder a estos archivos de exportación.
     * 
     * Los campos en el CSV estarán rodeados de comillas dobles para evitar problemas con comas u otros
     * caracteres especiales que puedan aparecer en los datos. Además, los encabezados y registros se 
     * organizan con un formato específico para que los datos sean fáciles de leer y procesar.
     * 
     * El archivo generado tiene el nombre `login_log.csv` y está organizado de manera que se pueda 
     * realizar la paginación de los registros, de modo que se procesan y exportan en bloques de 100 registros.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function export()
    {
        // Verifica si el usuario tiene acceso de administrador
        $this->checkAdminAccess();

        // Paginación: paginar por 100 registros por página
        $page = $this->request->getVar('page') ?: 1;
        $perPage = 100;

        // Obtiene todos los logs de la base de datos
        $logs = $this->loginLogModel->findAll();
        // Organiza todos los logs con paginación
        $logs = $this->loginLogModel->paginate($perPage, 'default', $page);

        // Definir el archivo como 'login_log.csv'
        $filename = 'login_log.csv';

        // Ruta para guardar fuera del directorio 'public'
        $filepath = FCPATH . '../private_exports/' . $filename;

        // Asegúrate de que el directorio 'private_exports' exista fuera de public
        $privateExportsDir = FCPATH . '../private_exports';
        if (!is_dir($privateExportsDir)) {
            mkdir($privateExportsDir, 0755, true); // Crea la carpeta si no existe
        }

        // Abre el archivo en modo de append para agregar los nuevos logs al final del archivo
        $file = fopen($filepath, 'a');
        if ($file) {
            // Obtener la fecha actual para separarla en el archivo 
            $currentDate = date('d-m-Y');

            // Escribir la fecha como un separador antes de los registros de hoy
            fputcsv($file, ["========== REGISTROS DEL " . $currentDate . " =========="]);

            // Escribir los encabezados solo si el archivo está vacío (para evitar duplicación)
            fputcsv($file, [
                '"' . str_pad('ID', max(strlen('ID'), 1), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('Usuario ID', max(strlen('Usuario ID'), 4), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('Fecha y Hora', max(strlen('Fecha y Hora'), 19), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('Exitoso', max(strlen('Exitoso'), 1), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('IP Address', max(strlen('IP Address'), 10), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('User-Agent', max(strlen('User-Agent'), 111), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('Intentos', max(strlen('Intentos'), 1), ' ', STR_PAD_BOTH) . '"',
                '"' . str_pad('Razón', max(strlen('Razón'), 22), ' ', STR_PAD_BOTH) . '"'
            ], ",");

            // Escribir los logs en el archivo con comillas alrededor de cada valor para asegurar que los datos sean fáciles de leer
            foreach ($logs as $log) {
                fputcsv($file, [
                    '"' . str_pad(esc($log['ID']), max(strlen('ID'), strlen(esc($log['ID']))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['UsuarioID'] ?? ''), max(strlen('Usuario ID'), strlen(esc($log['UsuarioID'] ?? ''))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['FechaHora']), max(strlen('Fecha y Hora'), strlen(esc($log['FechaHora']))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['Success'] == 1 ? 'Exitoso' : 'Fallido'), max(strlen('Exitoso'), strlen(esc($log['Success'] == 1 ? 'Exitoso' : 'Fallido'))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['IpAddress']), max(strlen('IP Address'), strlen(esc($log['IpAddress']))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['UserAgent']), max(strlen('User-Agent'), strlen(esc($log['UserAgent']))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['Attempts']), max(strlen('Intentos'), strlen(esc($log['Attempts']))), ' ', STR_PAD_BOTH) . '"',
                    '"' . str_pad(esc($log['Reason'] ?? 'No disponible'), max(strlen('Razón'), strlen(esc($log['Reason'] ?? 'No disponible'))), ' ', STR_PAD_BOTH) . '"'
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
