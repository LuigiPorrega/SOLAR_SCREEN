<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verifica si el usuario no está autenticado
        if (!session()->get('isLoggedIn')) {
            // Redirigir a login si no está autenticado
            return redirect()->to(base_url('login'));
        }

        // Verifica si el usuario tiene el rol de admin
        $role = session()->get('role');
        if ($role === 'admin') {
            // Los administradores tienen acceso a todo
            return;
        }

        // Los usuarios normales tienen restricciones
        // Definir las rutas restringidas para los usuarios normales
        $restrictedRoutes = [
            'admin/users',
            'admin/loginlog',
        ];

        $uri = current_url(); 

        // Verifica si la URL actual contiene alguna de las rutas restringidas
        foreach ($restrictedRoutes as $restrictedRoute) {
            if (strpos($uri, $restrictedRoute) !== false) {
                // Redirige al inicio del admin si un usuario normal intenta acceder a una ruta restringida
                return redirect()->to(base_url('admin/inicio'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
