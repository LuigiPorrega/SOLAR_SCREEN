import { HttpInterceptorFn } from '@angular/common/http';
import { HttpRequest, HttpHandlerFn } from '@angular/common/http';
import { inject } from '@angular/core';

export const authInterceptor: HttpInterceptorFn = (req: HttpRequest<any>, next: HttpHandlerFn) => {
  const rawToken = localStorage.getItem('token');
  const token = rawToken ? rawToken.trim() : null;

  // Rutas que no deben llevar token
  const isAuthRoute = req.url.includes('/usuarios/login') || req.url.includes('/usuarios/registrarse');

  if (token && !isAuthRoute) {
    const authReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(authReq);
  }

  return next(req);
};
