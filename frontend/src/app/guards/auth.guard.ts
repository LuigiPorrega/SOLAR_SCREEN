import { inject } from '@angular/core';
import { CanActivateFn } from '@angular/router';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const authService : AuthService= inject(AuthService);
  const router : Router= inject(Router);

  if (authService.isLoggedIn()) {
    return true;
  } else {
    router.navigate(['/login']);
    return false;
  }
};

export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const isLoggedIn = authService.isLoggedIn();
  const role = authService.getUserRole();

  if (isLoggedIn && role === 'admin') {
    return true;
  } else {
    router.navigate(['/unauthorized']); // o cualquier página de error/acceso denegado
    return false;
  }
};

export const usuarioGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const isLoggedIn = authService.isLoggedIn();
  const role = authService.getUserRole();
  if (isLoggedIn && role === 'usuario') {
    return true;
  } else {
    router.navigate(['/unauthorized']); // o cualquier página de error/acceso denegado
    return false;
  }
};

