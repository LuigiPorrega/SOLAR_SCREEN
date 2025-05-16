import {inject, Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ApiClimaService {
private readonly http: HttpClient = inject(HttpClient);

  constructor() {}

  getClima(ciudad: string): Observable<any> {
    const url = `/api-clima/data/2.5/weather?q=${encodeURIComponent(ciudad)}&units=metric&appid=${environment.meteoApiKey}`;
    console.log('URL de la solicitud:', url);
    return this.http.get(url);
  }
}
