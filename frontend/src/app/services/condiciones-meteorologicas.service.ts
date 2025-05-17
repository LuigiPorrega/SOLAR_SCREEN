import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';
import {
  ApiResponseCondicionesMeteorologicas,
  ApiResponseCondicionMeteorologicaCreateUpdate,
  ApiResponseCondicionMeteorologicaDelete,
  CondicionMeteorologica
} from '../common/InterfaceCondicionesMeteorologicas';

@Injectable({
  providedIn: 'root'
})
export class CondicionesMeteorologicasService {

  private readonly http: HttpClient = inject(HttpClient);

  constructor() {
  }

  //función para listar todas las Condiciones Meteorologicas paginadas
  getCondicionesMeteorologicas(page?: number, pageLimit?: number): Observable<ApiResponseCondicionesMeteorologicas> {
    return this.http.get<ApiResponseCondicionesMeteorologicas>(environment.baseURL + '/condicionesMeteorologicas?page=' + page + '&limit=' + pageLimit);
  };

  //función para listar una sola condición Meteorologica
  getCondicionMeteorologica(id: number ): Observable<CondicionMeteorologica>{
    return this.http.get<CondicionMeteorologica>(environment.baseURL +'/condicionesMeteorologicas/' +id);
  };

  addCondicionMeteorologica(condicion: CondicionMeteorologica, headers: HttpHeaders): Observable<any> {
    return this.http.post(`${environment.baseURL}/condicionesMeteorologicas`, condicion, { headers });
  }


  //función para modificar una condición Meteorologica
  updateCondicionMeteorologica(condicionMeteorologica: CondicionMeteorologica): Observable<ApiResponseCondicionMeteorologicaCreateUpdate> {
    return this.http.put<ApiResponseCondicionMeteorologicaCreateUpdate>(environment.baseURL +'/condicionesMeteorologicas/'+ condicionMeteorologica.ID , condicionMeteorologica);
  };

  //función para eliminar una condición Meteorologica
  deleteCondicionMeteorologica(id: number ): Observable<ApiResponseCondicionMeteorologicaDelete> {
    return this.http.delete<ApiResponseCondicionMeteorologicaDelete>(environment.baseURL +'/condicionesMeteorologicas/' +id);
  };
}
