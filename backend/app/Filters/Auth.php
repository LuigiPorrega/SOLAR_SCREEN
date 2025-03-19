<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verifica si el usuario no está autenticado
        if (!session()->get('isLoggedIn'))
        {
            return redirect()->to('/login');
        }

        // Verifica si el usuario autenticado no es administrador
        if (session()->get('role') !== 'admin')
        {
            return redirect()->to('/'); // Redirige a una página no autorizada o al dashboard
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
