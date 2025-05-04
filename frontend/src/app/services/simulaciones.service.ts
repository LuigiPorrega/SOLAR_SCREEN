import {inject, Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {
  ApiResponseSimulaciones,
  ApiResponseSimulacionesCreateUpdate, ApiResponseSimulacionesDelete,
  Simulacion
} from '../common/InterfaceSimulaciones';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class SimulacionesService {
  private readonly http: HttpClient = inject(HttpClient);

  constructor() {
  }

  //función para listar todas las Simulaciones paginadas
  getSimulaciones(page: number, pageLimit: number): Observable<ApiResponseSimulaciones> {
    return this.http.get<ApiResponseSimulaciones>(environment.baseURL + '/simulaciones?page=' + page + '&limit=' + pageLimit);
  };

  //función para listar una sola Simulación
  getSimulacion(id: number ): Observable<Simulacion>{
    return this.http.get<Simulacion>(environment.baseURL +'/simulaciones/' +id);
  };

 //función para crear una nueva simulación
  addSimulacion(simulacion: Simulacion): Observable<ApiResponseSimulacionesCreateUpdate> {
    return this.http.post<ApiResponseSimulacionesCreateUpdate>(environment.baseURL +'/simulaciones' , simulacion);
  };

  //función para modificar una simulación
  updateSimulacion(simulacion: Simulacion): Observable<ApiResponseSimulacionesCreateUpdate> {
    return this.http.put<ApiResponseSimulacionesCreateUpdate>(environment.baseURL +'/simulaciones/'+ simulacion.ID , simulacion);
  };

  //función para eliminar una simulación
  deleteSimulacion(id: number ): Observable<ApiResponseSimulacionesDelete> {
    return this.http.delete<ApiResponseSimulacionesDelete>(environment.baseURL +'/simulaciones/' +id);
  };
}
