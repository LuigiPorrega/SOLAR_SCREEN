import { HttpInterceptorFn } from '@angular/common/http';
import jwt_decode from 'jwt-decode';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  if (!req.url.includes('/login') && !req.url.includes('/registrarse')) {
    const token = localStorage.getItem('token');
    console.log('Interceptor ejecutado. Token:', token);

    if (token) {
      try {
        const decodedToken: any = jwt_decode(token);
        const expirationTime = decodedToken.exp;

        if (expirationTime * 1000 < Date.now()) {
          console.log('Token expirado');
          return next(req);
        } else {
          const authReq = req.clone({
            setHeaders: {
              Authorization: `Bearer ${token}`
            }
          });
          return next(authReq);
        }
      } catch (error) {
        console.error('Error al decodificar el token', error);
        return next(req);
      }
    }
  }

  return next(req);
};
