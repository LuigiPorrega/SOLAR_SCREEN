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
    const url = `${environment.meteoApiUrl}?q=${encodeURIComponent(ciudad)}&units=metric&appid=${environment.meteoApiKey}`;
    console.log('URL de la solicitud:', url); // Agrega esto para ver la URL en la consola
    return this.http.get(url);
  }
}
