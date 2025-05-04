import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';
import {tap} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private readonly http: HttpClient = inject (HttpClient);

  constructor() { }

  login(data: { username: string, password: string }) {
    return this.http.post<any>(environment.baseURL + '/usuarios/login', data).pipe(
      tap((res) => {
        if (res.status === 'success') {
          localStorage.setItem('token', res.token);
          localStorage.setItem('userData', JSON.stringify(res.data)); // Guarda rol, nombre, etc.
        }
      })
    );
  }

  getUserRole(): string | null {
    const userData = localStorage.getItem('userData');
    if (userData) {
      return JSON.parse(userData).rol;
    }
    return null;
  }

  getUserName(): string | null {
    const userData = localStorage.getItem('userData');
    if (userData) {
      return JSON.parse(userData).nombre;
    }
    return null;
  }

  logout() {
    return this.http.post(environment.baseURL + '/usuarios/logout', {}).pipe(
      tap(() => localStorage.removeItem('token'))
    );
  }

  isLoggedIn(): boolean {
    return !!localStorage.getItem('token');
  }

}
