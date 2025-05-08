import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable } from 'rxjs';
import jwt_decode from 'jwt-decode';  // Importación corregida

export class AuthInterceptorService implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (!req.url.includes('/login') && !req.url.includes('/registrarse')) {
      const token = localStorage.getItem('token');
      console.log('Interceptor ejecutado. Token:', token);

      if (token) {
        try {
          const decodedToken: any = jwt_decode(token);  // Decodificar el token
          const expirationTime = decodedToken.exp;

          if (expirationTime * 1000 < Date.now()) {
            console.log('Token expirado');
            // Redirigir a login o manejar el token expirado
            return next.handle(req);
          } else {
            const authReq = req.clone({
              setHeaders: {
                Authorization: `Bearer ${token}`
              }
            });
            return next.handle(authReq);
          }
        } catch (error) {
          console.error('Error al decodificar el token', error);
          // Redirigir a login o manejar el token inválido
          return next.handle(req);
        }
      }
    }

    return next.handle(req);
  }
}
