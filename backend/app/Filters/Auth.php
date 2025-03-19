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
            return redirect()->to('login');
        }

        // Verifica si el usuario es un admin y quiere acceder a una página restringida
        $role = session()->get('role');
        if ($role === 'admin') {
            // Los administradores tienen acceso a todo
            return;
        }

        // Los usuarios normales tienen restricciones
        // Si el usuario intenta acceder a páginas restringidas como loginlog, users, etc.
        $uri = current_url(); // Obtén la URL actual
        if (strpos($uri, 'admin/users') !== false || strpos($uri, 'admin/loginlog') !== false) {
            // Si el usuario normal intenta acceder a estas páginas, redirigirlo
            return redirect()->to('admin/inicio');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
