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
            . view('admin/usuarios/index')
            . view('templates/footer');
    }

    /**
     * Muestra los detalles de un usuario específico.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id ID del usuario a mostrar
     * @return string Vista renderizada con los detalles del usuario
     * @throws PageNotFoundException Si no se encuentra el usuario
     */
    public function view($id = null)
    {
        $this->checkAdminAccess();

        $user = $this->usuariosModel->find($id);

        if (!$user) {
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        $data = [
            'user' => $user,
            'title' => 'Detalle del Usuario',
        ];

        return view('templates/header', $data)
            . view('admin/usuarios/view')
            . view('templates/footer');
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     * Esta función es accesible solo para administradores.
     *
     * @return string Vista renderizada con el formulario de creación
     */
    public function new()
    {
        $this->checkAdminAccess();

        helper('form');
        return view('templates/header', ['title' => 'Crear Usuario'])
            . view('admin/usuarios/create')
            . view('templates/footer');
    }

    /**
     * Procesa la creación de un nuevo usuario.
     * Esta función es accesible solo para administradores.
     *
     * @return RedirectResponse Redirección a la lista de usuarios o al formulario si hay errores
     */
    public function create()
    {
        $this->checkAdminAccess();

        helper('form');

        $data = $this->request->getPost([
            'Nombre',
            'Correo',
            'FechaNacimiento',
            'Username',
            'Password',
            'Rol'
        ]);

        if (!$this->validateData($data, [
            'Nombre' => 'required|string|max_length[255]',
            'Correo' => 'required|valid_email|is_unique[Usuarios.Correo]',
            'FechaNacimiento' => 'required|valid_date',
            'Username' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[Usuarios.Username]',
            'Password' => 'required|min_length[6]',
            'Rol' => 'required|in_list[user,admin]',
        ])) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $data['PasswordHash'] = password_hash($data['Password'], PASSWORD_DEFAULT);
        unset($data['Password']);
        $data['Fecha_Registro'] = date('Y-m-d H:i:s');

        $this->usuariosModel->save($data);

        return redirect()->to(base_url('admin/usuarios'))->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id ID del usuario a editar
     * @return string Vista renderizada con el formulario de edición
     * @throws PageNotFoundException Si no se encuentra el usuario
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
            . view('usuarios/edit')
            . view('templates/footer');
    }

    /**
     * Procesa la actualización de un usuario existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id ID del usuario a actualizar
     * @return RedirectResponse Redirección a la lista de usuarios o al formulario si hay errores
     */
    public function updatedItem($id)
    {
        $this->checkAdminAccess();

        helper('form');

        $data = $this->request->getPost([
            'Nombre',
            'Correo',
            'FechaNacimiento',
            'Username',
            'Rol'
        ]);

        $validationRules = [
            'Nombre' => 'required|string|max_length[255]',
            'Correo' => 'required|valid_email',
            'FechaNacimiento' => 'required|valid_date',
            'Username' => 'required|alpha_numeric|min_length[3]|max_length[30]',
            'Rol' => 'required|in_list[user,admin]',
        ];

        $user = $this->usuariosModel->find($id);

        if (!$user) {
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        if (is_array($user)) {
            $correo = $user['Correo'];
            $username = $user['Username'];
        } else {
            $correo = $user->Correo;
            $username = $user->Username;
        }

        if ($correo !== $data['Correo']) {
            $validationRules['Correo'] .= '|is_unique[Usuarios.Correo]';
        }
        if ($username !== $data['Username']) {
            $validationRules['Username'] .= '|is_unique[Usuarios.Username]';
        }


        if (!$this->validateData($data, $validationRules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $data['ID'] = $id;
        $this->usuariosModel->save($data);

        return redirect()->to(base_url('admin/usuarios'))->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario existente.
     * Esta función es accesible solo para administradores.
     *
     * @param int|null $id ID del usuario a eliminar
     * @return RedirectResponse Redirección a la lista de usuarios
     * @throws PageNotFoundException Si no se encuentra el usuario
     */
    public function delete($id)
    {
        $this->checkAdminAccess();

        if (!$this->usuariosModel->delete($id)) {
            throw new PageNotFoundException("No se encontró el usuario con ID: $id");
        }

        return redirect()->back()->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Muestra el formulario de inicio de sesión.
     *
     * @param string|null $error Mensaje de error para mostrar en el formulario
     * @return string Vista renderizada con el formulario de inicio de sesión
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
     * @return RedirectResponse Redirección al dashboard si el inicio de sesión es exitoso
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

        return redirect()->to(base_url('admin/simulaciones'));
    }
}
