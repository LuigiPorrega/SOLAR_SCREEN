import {inject, Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable} from 'rxjs';
import {environment} from '../../environments/environment';
import {
  ApiResponseDelete,
  ApiResponseModeloFundaCreate,
  ApiResponseModeloFundaOne, ApiResponseModeloFundaUpdate,
  ApiResponseModelosFundas,
  ModeloFunda
} from '../common/InterfaceModelosFundas';

@Injectable({
  providedIn: 'root'
})
export class FundasService {

  private readonly http: HttpClient = inject(HttpClient);

  constructor() {
  }

  //función para listar todas las Fundas paginadas
  getFundas(page: number, limit: number, tipoFunda?: string) {
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());

    if (tipoFunda) {
      params = params.set('tipoFunda', tipoFunda);
    }

    return this.http.get<ApiResponseModelosFundas>(environment.baseURL + '/modelosFundas', { params });
  }

  getFundasFijas(): Observable<ApiResponseModelosFundas> {
    return this.http.get<ApiResponseModelosFundas>(
      `${environment.baseURL}/modelosFundas?tipo=Fija`
    );
  }

  getFundasExpandibles(): Observable<ApiResponseModelosFundas> {
    return this.http.get<ApiResponseModelosFundas>(
      `${environment.baseURL}/modelosFundas?tipo=Expandible`
    );
  }


  //función para listar una sola Funda con Proveedor
  getFunda(id: number ): Observable<ApiResponseModeloFundaOne>{
    return this.http.get<ApiResponseModeloFundaOne>(environment.baseURL +'/modelosFundas/' +id);
  };

  //función para crear una nueva funda de móvil
  addFunda(modeloFunda: ModeloFunda): Observable<ApiResponseModeloFundaCreate> {
    return this.http.post<ApiResponseModeloFundaCreate>(environment.baseURL +'/modelosFundas' , modeloFunda);
  };

  //función para modificar una funda con proveedores
  updateFunda(modeloFunda: ModeloFunda): Observable<ApiResponseModeloFundaUpdate> {
    return this.http.put<ApiResponseModeloFundaUpdate>(environment.baseURL +'/modelosFundas/'+ modeloFunda.ID , modeloFunda);
  };

  //función para eliminar una funda
  deleteFunda(id: number ): Observable<ApiResponseDelete> {
    return this.http.delete<ApiResponseDelete>(environment.baseURL +'/modelosFundas/' +id);
  };
}
