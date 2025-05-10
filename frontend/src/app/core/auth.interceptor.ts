import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const userDataStr = localStorage.getItem('userData');
    let token = null;

    try {
      const userData = userDataStr ? JSON.parse(userDataStr) : {};
      token = userData.token;
    } catch (e) {
      console.error('Error parsing userData', e);
    }

    if (token) {
      const authReq = req.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      });
      return next.handle(authReq);
    }

    return next.handle(req);
  }
}
