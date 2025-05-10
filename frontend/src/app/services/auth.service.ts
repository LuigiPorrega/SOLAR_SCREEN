import { inject, Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../environments/environment';
import {BehaviorSubject, catchError, Subject, tap, throwError} from 'rxjs';
import { RegistroUsuario } from '../common/InterfeceRegistroUsuario';
import { Router } from '@angular/router';
import { JwtHelperService } from '@auth0/angular-jwt';
import jwtDecode from 'jwt-decode';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly router: Router = inject(Router);
  private jwtHelper = new JwtHelperService();
  private loginStatusSubject = new BehaviorSubject<boolean>(this.isLoggedIn());
  public loginStatus$ = this.loginStatusSubject.asObservable();

  constructor() {}

  login(data: { username: string, password: string }) {
    return this.http.post<any>(environment.baseURL + '/usuarios/login', data).pipe(
      tap((res) => {
        if (res && res.status === 'success') {
          this.loginStatusSubject.next(true);
          console.log('Respuesta del backend:', res);
          const token = res.data.token;
          const userData = res.data;

          // Guardar token y datos de usuario
          console.log("Token guardado:", token);
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify({
            username: userData.username,
            role: userData.role,
            //Notifica el estado del login

          }));

          // 🔐 Si el usuario es admin, hacer login adicional en backend PHP
          if (userData.role === 'admin') {
            this.loginBackendPHP(data.username, data.password)
              .then(() => {
                console.log('Login backend exitoso');
              })
              .catch((err) => console.error('Error login backend:', err));
          }
        } else {
          console.error('Respuesta del backend no válida:', res);
        }

        if (localStorage != null) {
          this.router.navigate(['/inicio']);
        }
      }),

      catchError((error) => {
        console.error('Login failed', error);  // Log the error for debugging
        return throwError(() => new Error('Login failed'));
      })
    );

  }

  // Función auxiliar para login silencioso en backend PHP
  private async loginBackendPHP(username: string, password: string): Promise<string> {
    try {
      const response = await fetch('http://localhost:8000/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'include', // necesario para cookies/sesión
        body: new URLSearchParams({ username, password }).toString(),
      });

      if (!response.ok) {
        throw new Error('Error HTTP: ' + response.status);
      }

      const responseText = await response.text();
      console.log('Respuesta del backend:', responseText);

      if (responseText.includes('Credenciales incorrectas')) {
        throw new Error('Credenciales incorrectas');
      }

      return 'Login exitoso';
    } catch (error) {
      console.error('Error al iniciar sesión:', error);
      throw error;
    }
  }

  getUserRole(): string | null {
    const userData = localStorage.getItem('user');
    if (!userData) return null;

    try {
      const user = JSON.parse(userData);
      return user?.role ?? null;
    } catch {
      return null;
    }
  }

  getUserName(): string | null {
    const userData = localStorage.getItem('user');
    if (userData) {
      return JSON.parse(userData).username || null;
    }
    return null;
  }

  logout(callBackend: boolean = true): void {
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const token = userData.token;

    if (callBackend && token) {
      const logoutUrl = 'http://localhost:8000/api/usuarios/logout';
      this.http.post(logoutUrl, {}, {
        headers: {
          Authorization: `Bearer ${token?.trim()}`
        }
      }).subscribe({
        next: () => console.log('Logout OK'),
        error: (err) => console.warn('Error al cerrar sesión:', err),
        complete: () => this.clearUserData()
      });
    } else {
      this.clearUserData();
    }
  }

  clearUserData(): void {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    this.loginStatusSubject.next(false);
    this.router.navigate(['/login']);
  }

  public async logoutBackendPHP(): Promise<void> {
    try {
      const response = await fetch('http://localhost:8000/logout', {
        method: 'POST',
        credentials: 'include', // MUY IMPORTANTE para enviar la cookie
      });

      if (!response.ok) {
        throw new Error('Logout en backend fallido');
      }

      console.log('Logout backend exitoso');
    } catch (error: unknown) {
      console.error('Error en logout backend:', error);
    }
  }

  isLoggedIn(): boolean {
    const userDataStr = localStorage.getItem('user');
    if (!userDataStr) return false;

    try {
      const userData = JSON.parse(userDataStr);
      const token = userData.token;

      if (!token || typeof token !== 'string') return false;

      const decoded: any = jwtDecode(token);
      const now = Math.floor(Date.now() / 1000);

      if (decoded.exp && decoded.exp < now) {
        console.warn('Token expirado');
        this.logout(false);  // sin llamar a backend
        return false;
      }

      return true;

    } catch (err) {
      console.error('Error al decodificar token:', err);
      this.logout(false);  // limpia sin llamar a backend
      return false;
    }
  }

  isTokenValid(token: string): boolean {
    try {
      const decodedToken = JSON.parse(atob(token.split('.')[1])); // Decodificando el JWT
      const expiration = decodedToken.exp;
      const now = Math.floor(Date.now() / 1000);
      return expiration > now; // El token sigue siendo válido
    } catch (error) {
      return false;
    }
  }

  registrarse(data: RegistroUsuario) {
    return this.http.post<any>(`${environment.baseURL}/usuarios/registrarse`, data);
  }
}
