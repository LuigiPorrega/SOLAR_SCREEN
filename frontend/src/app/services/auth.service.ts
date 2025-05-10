import { inject, Injectable } from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../environments/environment';
import {Subject, tap} from 'rxjs';
import { RegistroUsuario } from '../common/InterfeceRegistroUsuario';
import {Router} from '@angular/router';
import { JwtHelperService } from "@auth0/angular-jwt";
import jwtDecode from 'jwt-decode';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly router: Router = inject(Router);
  private jwtHelper = new JwtHelperService();
  private logoutSubject = new Subject<void>();

  constructor() {
  }

  login(data: { username: string, password: string }) {
    return this.http.post<any>(environment.baseURL + '/usuarios/login', data).pipe(
      tap((res) => {
        if (res.status === 'success') {
          console.log('Respuesta del backend:', res);
          const token = res.data.token;
          const userData = res.data;

          // Guardar token y datos de usuario
          console.log("Token guardado:", token);
          localStorage.setItem('token', token);
          localStorage.setItem('userData', JSON.stringify(userData));
          localStorage.setItem('user', JSON.stringify({
            role: userData.role,
            username: userData.username,
            token: token
          }));

          // 🔐 Si el usuario es admin, hacer login adicional en backend PHP
          if (userData.role === 'admin') {
            this.loginBackendPHP(data.username, data.password)
              .then(() => console.log('Login backend exitoso'))
              .catch((err) => console.error('Error login backend:', err));
          }
        }
      })
    );
  }

// Función auxiliar para login silencioso en backend PHP
  private async loginBackendPHP(username: string, password: string): Promise<string> {
    try {
      const response = await fetch('http://localhost:8000/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'include',
        body: new URLSearchParams({
          username,
          password
        })
      });

      if (!response.ok) {
        throw new Error('Login en backend fallido');
      }

      return await response.text();
    } catch (error: unknown) {
      if (error instanceof Error) {
        throw new Error(error.message);
      } else {
        throw new Error('Error desconocido en el login backend');
      }
    }
  }

  getUserRole(): string | null {
    const userData = localStorage.getItem('userData');
    if (userData) {
      return JSON.parse(userData).role || null;
    }
    return null;
  }

  getUserName(): string | null {
    const userData = localStorage.getItem('userData');
    if (userData) {
      return JSON.parse(userData).username || null;
    }
    return null;
  }

  logout(callBackend: boolean = true): void {
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    const token = userData.token;

    if (callBackend && token) {
      this.http.post('http://localhost:8000/api/usuarios/logout', {}, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).subscribe({
        next: () => console.log('Logout OK'),
        error: err => console.warn('Error al cerrar sesión:', err),
        complete: () => this.clearUserData()
      });
    } else {
      this.clearUserData();
    }
  }

  private clearUserData(): void {
    localStorage.removeItem('userData');
    location.reload(); // o router.navigate
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
    const userDataStr = localStorage.getItem('userData');
    if (!userDataStr) return false;

    try {
      const userData = JSON.parse(userDataStr);
      const token = userData.token;

      if (!token || typeof token !== 'string') return false;

      // Valida el token
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

  private isTokenValid(token: string): boolean {
    // Aquí podrías agregar una validación de expiración del token
    // por ejemplo, si el token es un JWT y tiene una fecha de expiración
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
    return this.http.post<any>(environment.baseURL + '/usuarios/registrarse', data);
  }
}
