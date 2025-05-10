import { Component } from '@angular/core';
import { ApiClimaService } from '../../services/api-clima.service';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import {FormsModule} from '@angular/forms';

@Component({
  selector: 'app-clima-buscador',
  imports: [
    FormsModule
  ],
  standalone: true,
  templateUrl: './clima-buscador.component.html',
  styleUrl: './clima-buscador.component.css'
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
    this.climaService.getClima(this.ciudad).subscribe({
      next: data => {
        this.clima = data;
        this.mensaje = '';
      },
      error: err => {
        this.mensaje = 'No se pudo obtener el clima.';
        this.clima = null;
      }
    });
  }

  guardarComoCondicion(): void {
    if (!this.clima) return;

    const nuevaCondicion: CondicionMeteorologica = {
      Fecha: new Date().toISOString(),
      LuzSolar: 0, // OpenWeatherMap no da esto directamente
      Temperatura: this.clima.main.temp,
      Humedad: this.clima.main.humidity,
      Viento: this.clima.wind.speed,
      UsuarioID: null // O coloca el ID actual si tienes sesión
    };

    this.condicionesService.addCondicionMeteorologica(nuevaCondicion).subscribe({
      next: res => this.mensaje = 'Condición meteorológica guardada correctamente.',
      error: () => this.mensaje = 'Error al guardar la condición.'
    });
  }
}
