import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiClimaService {
  private apiKey = 'TU_API_KEY'; // Coloca aquí tu API key de OpenWeatherMap
  private apiUrl = 'https://api.openweathermap.org/data/2.5/weather';

  constructor(private http: HttpClient) {}

  getClima(ciudad: string): Observable<any> {
    return this.http.get(`${this.apiUrl}?q=${ciudad}&units=metric&appid=${this.apiKey}`);
  }
}
