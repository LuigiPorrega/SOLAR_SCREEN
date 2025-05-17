import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {
  ApiResponseSimulaciones,
  ApiResponseSimulacionesCreateUpdate, ApiResponseSimulacionesDelete,
  Simulacion
} from '../common/InterfaceSimulaciones';
import {environment} from '../../environments/environment';
import jwtDecode from 'jwt-decode';

@Injectable({
  providedIn: 'root'
})
export class SimulacionesService {
  private readonly http: HttpClient = inject(HttpClient);

  constructor() {
  }

  //función para listar todas las Simulaciones paginadas
  getSimulaciones(page?: number, pageLimit?: number): Observable<ApiResponseSimulaciones> {
    return this.http.get<ApiResponseSimulaciones>(environment.baseURL + '/simulaciones?page=' + page + '&limit=' + pageLimit);
  };

  //función para listar una sola Simulación
  getSimulacion(id: number ): Observable<Simulacion>{
    return this.http.get<Simulacion>(environment.baseURL +'/simulaciones/' +id);
  };

 //función para crear una nueva simulación
  addSimulacion(simulacion: Simulacion): Observable<ApiResponseSimulacionesCreateUpdate> {
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const token = userData.token;

    // Si el token es válido, obtenemos el UsuarioID
    const decodedToken: any = jwtDecode(token);
    const usuarioID = decodedToken.data.id;

    // Añadir UsuarioID a la simulación
    const simulacionConUsuario = { ...simulacion, UsuarioID: usuarioID };

    return this.http.post<ApiResponseSimulacionesCreateUpdate>(`${environment.baseURL}/simulaciones`, simulacionConUsuario);
  }

  //función para modificar una simulación
  updateSimulacion(simulacion: Simulacion): Observable<ApiResponseSimulacionesCreateUpdate> {
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const token = userData.token;

    const decodedToken: any = jwtDecode(token);
    const usuarioID = decodedToken.data.id;

    const simulacionConUsuario = { ...simulacion, UsuarioID: usuarioID };

    return this.http.put<ApiResponseSimulacionesCreateUpdate>(`${environment.baseURL}/simulaciones/${simulacion.ID}`, simulacionConUsuario);
  }

  //función para eliminar una simulación
  deleteSimulacion(id: number ): Observable<ApiResponseSimulacionesDelete> {
    return this.http.delete<ApiResponseSimulacionesDelete>(environment.baseURL +'/simulaciones/' +id);
  };
}
