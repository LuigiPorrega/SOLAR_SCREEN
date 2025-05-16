import { HttpInterceptorFn } from '@angular/common/http';
import { HttpRequest, HttpHandlerFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req: HttpRequest<any>, next: HttpHandlerFn) => {
  const rawToken = localStorage.getItem('token');
  const token = rawToken ? rawToken.trim() : null;

  const isAuthRoute =
    req.url.includes('/usuarios/login') ||
    req.url.includes('/usuarios/registrarse');

  const isClimaApi = req.url.startsWith('/api-clima');

  if (token && !isAuthRoute && !isClimaApi) {
    const authReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(authReq);
  }

  return next(req);
};
