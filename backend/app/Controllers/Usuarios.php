<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\UsuariosModel;
use App\Models\LoginLogModel;
use CodeIgniter\HTTP\RedirectResponse;

class Usuarios extends BaseController
{
    protected $usuariosModel;

    /**
     * Constructor del controlador.
     * Inicializa el modelo de usuarios.
     */
    public function __construct()
    {
        $this->usuariosModel = new UsuariosModel();
    }

    /**
     * Verifica si el usuario actual tiene acceso de administrador.
     * Lanza una excepción si el usuario no tiene los permisos necesarios.
     *
     * @throws PageNotFoundException
     */
    private function checkAdminAccess()
    {
        $session = session();
        if (!$session->get('user_id') || !$this->usuariosModel->canAccessBackend($session->get('user_id'))) {
            throw new PageNotFoundException('Acceso no autorizado');
        }
    }

    /**
     * Muestra la lista de todos los usuarios.
     * Esta función es accesible solo para administradores.
     *
     * @return string Vista renderizada con la lista de usuarios
     */
    public function index()
    {
        $this->checkAdminAccess();

        $data = [
            'users' => $this->usuariosModel->findAll(),
            'title' => 'Lista de Usuarios',
        ];

        return view('templates/header', $data)
            . view('usuarios/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de un usuario específico.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id 
     * @return string 
     * @throws PageNotFoundException 
     */
    public function view($id = null)
    {
        // Verificar si el usuario tiene acceso como administrador
        $this->checkAdminAccess();

        // Obtener el modelo de UsuariosModel
        $usuariosModel = model('App\Models\UsuariosModel');

        // Obtener el usuario con la edad calculada con función de la base de datos
        $user = $usuariosModel->obtenerUsuarioConEdad($id);

        // Verificar si el usuario existe
        if (!$user) {
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        // Pasar los datos a la vista
        $data = [
            'user' => $user,
            'title' => 'Detalle del Usuario',
        ];

        // Renderizar la vista
        return view('templates/header', $data)
            . view('usuarios/view', $data)
            . view('templates/footer');
    }


    /**
     * Muestra el formulario para crear un nuevo usuario.
     * Esta función es accesible solo para administradores.
     *
     * @return string 
     */
    public function new()
    {
        $this->checkAdminAccess();

        helper('form');

        // Inicializamos los datos del formulario con valores vacíos
        $data = [
            'Nombre' => '',
            'Correo' => '',
            'FechaNacimiento' => '',
            'Username' => '',
            'Password' => '',
            'Rol' => 'usuario',  
            'GoogleID' => NULL
        ];

        // Si el usuario ha iniciado sesión con Google, llenar los campos automáticamente
        if (session()->has('google_user_id')) {
            // Se asume que tienes datos de Google almacenados en la sesión
            $data['GoogleID'] = session()->get('google_user_id');
            $data['Nombre'] = session()->get('google_name');
            $data['Correo'] = session()->get('google_email');
        }


        return view('templates/header', ['title' => 'Crear Usuario'])
            . view('usuarios/create', $data)
            . view('templates/footer');
    }

    
    /**
     * Procesa la creación de un nuevo usuario.
     * Esta función es accesible solo para administradores.
     *
     * @return RedirectResponse 
     */
    public function create()
    {
        $this->checkAdminAccess();

        helper('form');

        // Recoger los datos del formulario
        $data = $this->request->getPost([
            'Nombre',
            'Correo',
            'FechaNacimiento',
            'Username',
            'Password',
            'Rol' => $this->request->getPost('Rol') ?? 'usuario',
            'GoogleID'
        ]);

        // Si el Rol no es especificado, se asigna 'usuario' por defecto
        if (empty($data['Rol'])) {
            $data['Rol'] = 'usuario';
        }

        // Validación de datos
        if (!$this->validateData($data, [
            'Nombre' => 'required|string|max_length[255]',
            'Correo' => 'required|valid_email', 
            'GoogleID' => 'permit_empty', 
            'Username' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[Usuarios.Username]', 
            'FechaNacimiento' => 'required|valid_date',
            'Password' => 'required|min_length[6]',
            'Rol' => 'required|in_list[usuario,admin]',
        ])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Encriptar la contraseña con SHA256 
        $data['PasswordHash'] = hash('sha256', $data['Password']);
        unset($data['Password']);  

        // Asignar fecha de registro
        $data['Fecha_Registro'] = date('Y-m-d H:i:s');

        // Si no se ha recibido un GoogleID, lo dejamos como NULL
        if (empty($data['GoogleID'])) {
            $data['GoogleID'] = NULL;
        }

        // Guardar el nuevo usuario
        if ($this->usuariosModel->save($data)) {
            return redirect()->to(base_url('admin/users'))->with('success', 'Usuario creado exitosamente.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Hubo un error al crear el usuario.');
        }
    }




    /**
     * Muestra el formulario para editar un usuario existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id 
     * @return string 
     * @throws PageNotFoundException 
     */
    public function update($id)
    {
        $this->checkAdminAccess();

        $user = $this->usuariosModel->find($id);

        if (!$user) {
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        $data = [
            'user' => $user,
            'title' => 'Editar Usuario',
        ];

        helper('form');
        return view('templates/header', $data)
            . view('usuarios/update')
            . view('templates/footer');
    }

    /**
     * Procesa la actualización de un usuario existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id 
     * @return RedirectResponse 
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        helper('form');

        // Obtener los datos del formulario
        $data = $this->request->getPost([
            'Nombre',
            'Correo',
            'FechaNacimiento',
            'Username',
            'Rol',
            'Password'
        ]);

        // Validación de los datos
        $validationRules = [
            'Nombre' => 'required|string|max_length[255]',
            'Correo' => 'required|valid_email',
            'FechaNacimiento' => 'required|valid_date',
            'Username' => 'required|alpha_numeric|min_length[3]|max_length[30]',
        ];

        // Recuperar el usuario con ID
        $user = $this->usuariosModel->find($id);
        if (!$user) {
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        // Si el rol ha cambiado, aplicar la validación 'in_list'
        if ($user['Rol'] !== $data['Rol']) {
            $validationRules['Rol'] = 'required|in_list[user,admin]';
        } else {
            // Si no ha cambiado, no lo incluimos en las reglas de validación
            unset($data['Rol']);
        }

        // Verificar si el correo o el nombre de usuario han cambiado, y aplicar las validaciones correspondientes
        if ($user['Correo'] !== $data['Correo']) {
            $validationRules['Correo'] .= '|is_unique[Usuarios.Correo]';
        }

        if ($user['Username'] !== $data['Username']) {
            $validationRules['Username'] .= '|is_unique[Usuarios.Username]';
        }

        // Validar los datos
        if (!$this->validateData($data, $validationRules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Si hay una nueva contraseña, 
        if (!empty($data['Password'])) {
            //  hash con SHA-256 de la contraseña
            $data['PasswordHash'] = hash('sha256', $data['Password']);
            unset($data['Password']);
        } else {
            // Si no hay nueva contraseña, no modificar la contraseña en la base de datos
            unset($data['Password']);
        }

        // Actualizar los datos del usuario
        $data['ID'] = $id;
        $this->usuariosModel->save($data);


        return redirect()->to(base_url('admin/users'))->with('success', 'Usuario actualizado exitosamente.');
    }


    /**
     * Elimina un usuario existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id 
     * @return RedirectResponse 
     * @throws PageNotFoundException 
     */
    public function delete($id)
    {
        $this->checkAdminAccess(); 

        // Verificamos si el usuario existe en la base de datos
        $usuario = $this->usuariosModel->find($id);
        if (!$usuario) {
            // Si el usuario no existe, lanzamos una excepción
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        // Intentamos eliminar al usuario
        if ($this->usuariosModel->delete($id)) {
            // Si la eliminación es exitosa, redirigimos con un mensaje de éxito
            return redirect()->to(base_url('admin/users'))->with('success', 'Usuario eliminado exitosamente.');
        } else {
            // Si la eliminación falla, redirigimos con un mensaje de error
            return redirect()->to(base_url('admin/users'))->with('error', 'Hubo un problema al eliminar el usuario.');
        }
    }


    /**
     * Muestra el formulario de inicio de sesión.
     *
     * @param string|null $error 
     * @return string 
     */
    public function loginForm($error = null)
    {
        helper('form');

        return view('templates/header', ['title' => 'Acceso privado'])
            . view('usuarios/login', ['error' => $error])
            . view('templates/footer');
    }


    /**
     * Procesa el inicio de sesión del usuario.
     *
     * @return RedirectResponse 
     */
    public function checkUser()
    {
        helper('form');

        // Recoger los datos del formulario
        $validatedData = $this->request->getPost(['username', 'password']);
        log_message('debug', 'Datos del formulario: ' . print_r($validatedData, true));

        // Validar los datos de entrada
        if (!$this->validate([
            'username' => 'required',
            'password' => 'required',
        ])) {
            // Si la validación falla, redirige con los errores y los valores previos
            return redirect()->to(base_url('login'))->withInput()->with('validation', $this->validator);
        }

        // Consultamos al modelo para verificar si el usuario existe
        $user = $this->usuariosModel->checkUser($validatedData['username'], $validatedData['password']);

        // Crear un nuevo modelo de loginLog
        $loginLogModel = new LoginLogModel();
        $ipAddress = $this->request->getIPAddress();
        $userAgent = $this->request->getUserAgent();

        // Si el usuario es encontrado y las credenciales son correctas
        if ($user) {
            // Verificar la contraseña usando hash('sha256', $password)
            $hashedPassword = hash('sha256', $validatedData['password']);

            // Si la contraseña es correcta
            if ($hashedPassword === $user['PasswordHash']) {
                // Crear sesión de usuario
                $session = session();
                $session->set([
                    'user_id' => $user['ID'],
                    'username' => $user['Username'],
                    'role' => $user['Rol'],
                    'isLoggedIn' => true,
                ]);

                // Registra el inicio de sesión exitoso en la tabla LoginLog
                $loginLogModel->logLoginAttempt($user['ID'], true, $ipAddress, $userAgent);

                // Redirige al panel de administración
                return redirect()->to(base_url('admin/inicio'));
            } else {
                // Si la contraseña no coincide, registrar el intento fallido
                $loginLogModel->logLoginAttempt(null, false, $ipAddress, $userAgent, 1, 'Contraseña incorrecta');

                // Redirige con un mensaje de error y valores de entrada previos
                return redirect()->to(base_url('login'))->withInput()->with('error', 'Credenciales incorrectas');
            }
        } else {
            // Si el usuario no se encuentra, registrar el intento fallido
            $loginLogModel->logLoginAttempt(null, false, $ipAddress, $userAgent, 1, 'Usuario no encontrado');

            // Redirige con un mensaje de error y valores de entrada previos
            return redirect()->to(base_url('login'))->withInput()->with('error', 'Credenciales incorrectas');
        }
    }


    // Registrar los intentos de inicio de sesión
    private function logLogin($userId, $success)
    {
        $loginLogModel = new LoginLogModel();
        $ip = $this->request->getIPAddress();
        $userAgent = $this->request->getUserAgent()->getAgentString();
        $reason = $success ? null : 'Credenciales incorrectas';

        // Si el inicio de sesión es exitoso, usamos el ID del usuario
        if ($success) {
            $loginLogModel->logLoginAttempt($userId, true, $ip, $userAgent, 1, $reason);
        } else {
            // Si el login falló, usamos un ID especial de usuario (por ejemplo, ID = 0)
            $loginLogModel->logLoginAttempt(0, false, $ip, $userAgent, 1, $reason);
        }
    }

    /**
     * Cierra la sesión del usuario actual.
     *
     * @return RedirectResponse Redirección a la página de inicio
     */
    public function closeSession()
    {
        $session = session();
        $session->remove('user_id');
        $session->remove('username');
        $session->remove('role');
        $session->destroy();

        return redirect()->to(base_url('login'));
    }
}
