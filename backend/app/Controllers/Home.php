<?php

namespace App\Controllers;

use App\Models\UsuariosModel;
use CodeIgniter\Controller;

class Home extends Controller
{
    protected $userModel;
    protected $session;

    /**
     * Constructor de la clase Home.
     * Inicializa el modelo de usuarios y la sesión.
     */
    public function __construct()
    {
        // Instancia del modelo UsuariosModel
        $this->userModel = new UsuariosModel();
        // Inicializa la sesión para acceder a datos del usuario logueado
        $this->session = session();
    }

    /**
     * Muestra la vista de bienvenida.
     * Esta función es la que se ejecuta cuando un usuario accede a la página principal.
     *
     * @return string Vista de bienvenida
     */
    public function index()
    {
        // Retorna la vista de bienvenida
        return view('welcome_message');
    }

    /**
     * Muestra el dashboard del usuario.
     * Esta función se muestra al usuario una vez que se haya autenticado.
     * Incluye el nombre del usuario en los datos enviados a la vista.
     *
     * @return string Vista del dashboard
     */
    public function dashboard()
    {
        // Se prepara la data con el título y el nombre del usuario logueado
        $data = [
            'title' => 'Dashboard',
            'nombreUsuario' => $this->session->get('username') // Obtiene el nombre del usuario desde la sesión
        ];

        // Renderiza las vistas del header, dashboard y footer
        return view('templates/header', $data)
            . view('dashboard/index', $data) // Vista principal del dashboard
            . view('templates/footer');
    }

    /**
     * Verifica si el usuario tiene el rol de administrador.
     * Esta función ayuda a verificar si el usuario tiene permisos de administrador para acceder a ciertas áreas.
     *
     * @return bool True si el usuario es administrador, false de lo contrario
     */
    private function isAdmin()
    {
        // Verifica si el usuario tiene una sesión activa y si su rol es de administrador
        return $this->session->has('user_id') && $this->userModel->hasRole($this->session->get('user_id'), 'admin');
    }
}
