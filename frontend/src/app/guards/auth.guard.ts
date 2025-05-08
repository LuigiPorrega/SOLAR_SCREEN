import { inject } from '@angular/core';
import { CanActivateFn } from '@angular/router';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const authService: AuthService = inject(AuthService);
  const router: Router = inject(Router);

  if (authService.isLoggedIn()) {
    return true; // El usuario está logueado, puede acceder a la ruta
  } else {
    router.navigate(['/login']); // Si no está logueado, redirige al login
    return false;
  }
};

export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const isLoggedIn = authService.isLoggedIn();
  const role = authService.getUserRole();

  if (isLoggedIn && role === 'admin') {
    return true; // El usuario es admin, puede acceder
  } else {
    router.navigate(['/unauthorized']); // Redirige si no tiene acceso
    return false;
  }
};

export const usuarioGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const isLoggedIn = authService.isLoggedIn();
  const role = authService.getUserRole();

  if (isLoggedIn && role === 'usuario') {
    return true; // El usuario tiene el rol adecuado, puede acceder
  } else {
    router.navigate(['/unauthorized']); // Redirige si no tiene acceso
    return false;
  }
};
