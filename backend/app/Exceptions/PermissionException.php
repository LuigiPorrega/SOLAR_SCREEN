<?php

namespace App\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class PermissionException extends FrameworkException
{
    public static function forUnauthorizedAccess()
    {
        return new static('Acceso denegado. No tienes los permisos necesarios para realizar esta acción.');
    }
}
