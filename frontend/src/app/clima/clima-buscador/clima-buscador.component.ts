import { Component } from '@angular/core';
import { ApiClimaService } from '../../services/api-clima.service';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import {HttpHeaders} from '@angular/common/http';

@Component({
  selector: 'app-clima-buscador',
  imports: [
    FormsModule,
    CommonModule
  ],
  standalone: true,
  templateUrl: './clima-buscador.component.html',
  styleUrls: ['./clima-buscador.component.css'] // Asegúrate que es styleUrls en vez de styleUrl
})
export class ClimaBuscadorComponent {
  ciudad = '';
  clima: any = null;
  mensaje = '';

  constructor(
    private climaService: ApiClimaService,
    private condicionesService: CondicionesMeteorologicasService
  ) {}

  buscarClima(): void {
    // Limpiar cualquier mensaje previo
    this.mensaje = '';
    this.clima = null;

    // Validar que la ciudad no esté vacía
    if (!this.ciudad.trim()) {
      this.mensaje = 'Por favor, ingresa el nombre de una ciudad.';
      return;
    }

    // Llamar al servicio para obtener el clima
    this.climaService.getClima(this.ciudad).subscribe({
      next: data => {
        this.clima = data;
        this.mensaje = ''; // Limpiar mensaje si la respuesta es exitosa
      },
      error: err => {
        console.error('Error al obtener el clima:', err);

        // Manejo de errores
        if (err.status === 404) {
          this.mensaje = 'Ciudad no encontrada. Por favor, verifica el nombre.';
        } else if (err.status === 401) {
          this.mensaje = 'API Key inválida. Verifica tu clave de API.';
        } else {
          this.mensaje = 'No se pudo obtener el clima. Intenta más tarde.';
        }
        this.clima = null;
      }
    });
  }

  guardarComoCondicion(): void {
    if (!this.clima) return;

    const nuevaCondicion: CondicionMeteorologica = {
      Fecha: new Date().toISOString(),
      LuzSolar: 0,
      Temperatura: this.clima.main.temp,
      Humedad: this.clima.main.humidity,
      Viento: this.clima.wind.speed,
      UsuarioID: null // se ignora, lo pone el backend desde el token
    };

    const token = localStorage.getItem('token'); // o como lo tengas guardado

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    this.condicionesService.addCondicionMeteorologica(nuevaCondicion, headers).subscribe({
      next: res => this.mensaje = 'Condición meteorológica guardada correctamente.',
      error: () => this.mensaje = 'Error al guardar la condición.'
    });
  }
}
