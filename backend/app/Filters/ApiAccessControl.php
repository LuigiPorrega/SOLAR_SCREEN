<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiAccessControl implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Verifica si el usuario está logueado
        if (!$session->get('isLoggedIn')) {
            // Si no está logueado, el acceso a ciertos recursos está restringido
            if (!$this->isPublicRoute($request)) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['error' => 'No autenticado.']);
            }
        }

        $role = $session->get('role');
        $userId = $session->get('userId'); // Asegúrate de guardar este dato en sesión al loguear

        // Ruta actual
        $uri = service('uri')->getPath();
        $method = $request->getMethod();

        // Reglas generales por recurso

        // Condiciones Meteorológicas
        if (str_starts_with($uri, 'api/condicionesMeteorologicas')) {
            $this->handleCondicionesMeteorologicas($method, $role, $userId, $uri);
        }

        // Ideas
        if (str_starts_with($uri, 'api/ideas')) {
            $this->handleIdeas($method, $role, $userId, $uri);
        }

        // Modelos Fundas
        if (str_starts_with($uri, 'api/modelosFundas')) {
            $this->handleModelosFundas($method, $role, $userId, $uri);
        }

        // Proveedores
        if (str_starts_with($uri, 'api/proveedores')) {
            $this->handleProveedores($method, $role, $userId, $uri);
        }

        // Simulaciones
        if (str_starts_with($uri, 'api/simulaciones')) {
            $this->handleSimulaciones($method);
        }

        // Usuarios
        if (str_starts_with($uri, 'api/usuarios')) {
            $this->handleUsuarios($method, $role, $userId);
        }

        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    // Verifica si la ruta es pública (como login, checkAccess)
    private function isPublicRoute(RequestInterface $request)
    {
        $uri = service('uri')->getPath();
        $method = $request->getMethod();

        // Métodos públicos
        return in_array($uri, ['api/usuarios/login', 'api/usuarios/checkAccess']) || $method === 'GET';
    }

    // Condiciones Meteorológicas
    private function handleCondicionesMeteorologicas($method, $role, $userId, $uri)
    {
        if ($method === 'GET') {
            return; // Público puede ver la lista o detalle
        }

        if ($method === 'POST') {
            return; // Usuario/Admin puede crear condiciones meteorológicas
        }

        if ($method === 'PUT' || $method === 'DELETE') {
            if ($role === 'admin') return; // Admin puede modificar/eliminar todas
            $resourceId = $this->getResourceIdFromUri($uri);
            if (!$this->belongsToUser('condicionesMeteorologicas', $resourceId, $userId)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'No tienes permiso para esta acción.']);
            }
        }
    }

    // Ideas
    private function handleIdeas($method, $role, $userId, $uri)
    {
        if ($method === 'GET') {
            return; // Público puede ver la lista o detalle
        }

        if ($method === 'POST') {
            return; // Usuario/Admin puede crear ideas
        }

        if ($method === 'PUT' || $method === 'DELETE') {
            if ($role === 'admin') return; // Admin puede modificar/eliminar todas
            $resourceId = $this->getResourceIdFromUri($uri);
            if (!$this->belongsToUser('ideas', $resourceId, $userId)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'No tienes permiso para esta acción.']);
            }
        }
    }

    // Modelos Fundas
    private function handleModelosFundas($method, $role, $userId, $uri)
    {
        if ($method === 'GET') {
            return; // Público puede ver la lista o detalle
        }

        if ($method === 'POST') {
            return; // Usuario/Admin puede crear modelos fundas
        }

        if ($method === 'PUT' || $method === 'DELETE') {
            if ($role === 'admin') return; // Admin puede modificar/eliminar todas
            $resourceId = $this->getResourceIdFromUri($uri);
            if (!$this->belongsToUser('modelosFundas', $resourceId, $userId)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'No tienes permiso para esta acción.']);
            }
        }
    }

    // Proveedores
    private function handleProveedores($method, $role, $userId, $uri)
    {
        if ($method === 'GET') {
            return; // Público puede ver la lista o detalle
        }

        if ($method === 'POST') {
            return; // Usuario/Admin puede crear proveedores
        }

        if ($method === 'PUT' || $method === 'DELETE') {
            if ($role === 'admin') return; // Admin puede modificar/eliminar todos
            $resourceId = $this->getResourceIdFromUri($uri);
            if (!$this->belongsToUser('proveedores', $resourceId, $userId)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'No tienes permiso para esta acción.']);
            }
        }
    }

    // Simulaciones
    private function handleSimulaciones($method)
    {
        if ($method === 'POST') {
            return; // Público puede crear simulaciones sin guardarlas
        }

        if ($method === 'GET' || $method === 'DOWNLOAD') {
            return; // Usuario/Admin puede ver y descargar simulaciones
        }
    }

    // Usuarios
    private function handleUsuarios($method, $role, $userId)
    {
        if ($method === 'POST' && strpos(service('uri')->getPath(), 'login') !== false) {
            return; // Login es público
        }

        if ($method === 'POST' && strpos(service('uri')->getPath(), 'logout') !== false) {
            return; // Logout es accesible por Usuario/Admin
        }

        if ($method === 'GET' && strpos(service('uri')->getPath(), 'checkAccess') !== false) {
            return; // CheckAccess es accesible por Usuario/Admin
        }

        return service('response')
            ->setStatusCode(403)
            ->setJSON(['error' => 'Acción no permitida']);
    }

    // Función para obtener el ID del recurso de la URI
    private function getResourceIdFromUri($uri)
    {
        $segments = explode('/', $uri);
        return end($segments); // El último segmento de la URI es el ID del recurso
    }

    // Verifica si el recurso le pertenece al usuario
    private function belongsToUser(string $table, $id, $userId): bool
    {
        $db = db_connect();
        $builder = $db->table($table);
        $resource = $builder->where('id', $id)->get()->getRow();

        return $resource && $resource->UsuarioID == $userId;
    }
}
