<?php

namespace App\Controllers;

use App\Models\UsuariosModel;
use CodeIgniter\Controller;

class Home extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UsuariosModel();
        $this->session = session();
    }

    public function index()
    {
        // Mostrar la vista de bienvenida
        return view('welcome_message');
    }

    public function dashboard()
    {
        // Verificar si el usuario es administrador
        if (!$this->isAdmin()) {
            return redirect()->to('/')->with('error', 'No tienes permisos para acceder a esta página.');
        }

        $data = [
            'title' => 'Dashboard',
            'nombreUsuario' => $this->session->get('username') 
        ];

        return view('templates/header', $data)
            . view('dashboard/index', $data) 
            . view('templates/footer');
    }

    // Función para verificar si el usuario es administrador
    private function isAdmin()
    {
        return $this->session->has('user_id') && $this->userModel->hasRole($this->session->get('user_id'), 'admin');
    }
}
