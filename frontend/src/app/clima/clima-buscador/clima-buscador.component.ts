import {Component, OnInit} from '@angular/core';
import { ApiClimaService } from '../../services/api-clima.service';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import {FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators} from '@angular/forms';
import { CommonModule } from '@angular/common';
import {HttpHeaders} from '@angular/common/http';
import { jsPDF } from 'jspdf';
import { ngxCsv } from 'ngx-csv/ngx-csv';
import html2canvas from 'html2canvas';
import {Chart} from 'chart.js';
import * as bootstrap from 'bootstrap';
import {RouterLink} from '@angular/router';
import * as AOS from 'aos';

@Component({
  selector: 'app-clima-buscador',
  imports: [
    FormsModule,
    CommonModule,
    ReactiveFormsModule,
    RouterLink
  ],
  standalone: true,
  templateUrl: './clima-buscador.component.html',
  styleUrls: ['./clima-buscador.component.css'] // Asegúrate que es styleUrls en vez de styleUrl
})
export class ClimaBuscadorComponent implements OnInit{
  ciudad: string = '';
  clima: any = null;
  energiaGenerada: number = 0;
  porcentajeBateria: number = 0;
  chart: any;
  simulacionForm: FormGroup;
  mensaje: string = '';

  constructor(
    private climaService: ApiClimaService,
    private condicionesService: CondicionesMeteorologicasService,
    private formBuilder: FormBuilder
  ) {
    this.simulacionForm = this.formBuilder.group({
      tiempo: ['', [Validators.required, Validators.min(1)]],
      luz: ['', Validators.required]
    });
  }

  ngOnInit() {
    AOS.init();
  }

  buscarClima(): void {
    this.mensaje = '';
    this.clima = null;

    if (!this.ciudad.trim()) {
      this.mensaje = 'Por favor, ingresa el nombre de una ciudad.';
      return;
    }

    this.climaService.getClima(this.ciudad).subscribe({
      next: data => {
        this.clima = data;
        this.mensaje = '';
      },
      error: err => {
        console.error('Error al obtener el clima:', err);
        this.mensaje = 'No se pudo obtener el clima. Intenta más tarde.';

        if (err.status === 404) {
          this.mensaje = 'Ciudad no encontrada. Verifica el nombre.';
        } else if (err.status === 401) {
          this.mensaje = 'API Key inválida o sesión caducada.';
        } else if (err.status === 429) {
          this.mensaje = 'Límite de peticiones alcanzado. Intenta luego.';
        }
      }
    });
  }

  calcularEnergia(clima: any, tiempo: number): void {
    const irradiancia = clima.main.temp * 0.8;
    this.energiaGenerada = irradiancia * tiempo;
    this.porcentajeBateria = Math.min((this.energiaGenerada / 100) * 100, 100);
  }

  iniciarGrafico(tiempo: number): void {
    const ctx = document.getElementById('grafico') as HTMLCanvasElement;

    if (this.chart) {
      this.chart.destroy();
    }

    this.chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: Array.from({ length: tiempo + 1 }, (_, i) => `${i}h`),
        datasets: [
          {
            label: 'Producción Energética (Wh)',
            data: Array.from({ length: tiempo + 1 }, (_, i) => (this.energiaGenerada / tiempo) * i),
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: false,
            yAxisID: 'y',
          },
          {
            label: '% de Batería',
            data: Array.from({ length: tiempo + 1 }, (_, i) =>
              Math.min(((this.energiaGenerada / tiempo) * i) / 100 * 100, 100)
            ),
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: false,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            type: 'linear',
            position: 'left',
            title: {
              display: true,
              text: 'Energía (Wh)'
            }
          },
          y1: {
            type: 'linear',
            position: 'right',
            title: {
              display: true,
              text: '% Batería'
            },
            grid: {
              drawOnChartArea: false
            }
          }
        }
      }
    });
  }



  generarSimulacion(): void {
    if (this.simulacionForm.invalid || !this.clima) {
      this.mensaje = 'Completa el formulario y busca el clima primero.';
      return;
    }

    const tiempoSimulacion = this.simulacionForm.value.tiempo;

    const nuevaCondicion: CondicionMeteorologica = {
      Fecha: new Date().toISOString(),
      LuzSolar: this.simulacionForm.value.luz === 'natural' ? 1 : 0,
      Temperatura: this.clima.main.temp,
      Humedad: this.clima.main.humidity,
      Viento: this.clima.wind.speed,
      UsuarioID: null
    };

    const token = localStorage.getItem('token');
    if (!token) {
      const modal = new bootstrap.Modal(document.getElementById('modalLoginRequerido')!);
      modal.show();
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });

    // Calcular energía y graficar según el tiempo ingresado
    this.calcularEnergia(this.clima, tiempoSimulacion);
    this.iniciarGrafico(tiempoSimulacion);

    this.condicionesService.addCondicionMeteorologica(nuevaCondicion, headers).subscribe({
      next: () => {
        this.mensaje = 'Simulación generada y guardada.';
      },
      error: () => {
        this.mensaje = 'Error al guardar la simulación.';
      }
    });
  }

  //exportar datos pdf
  exportarPDF(): void {
    const data = document.getElementById('grafico');
    html2canvas(data!).then(canvas => {
      const imgWidth = 208;
      const imgHeight = canvas.height * imgWidth / canvas.width;
      const contentDataURL = canvas.toDataURL('image/png');
      const pdf = new jsPDF();
      pdf.addImage(contentDataURL, 'PNG', 0, 0, imgWidth, imgHeight);
      pdf.save('simulacion.pdf');
    });
  }

  //exportar datos en csv
  exportarCSV(): void {
    const tiempo = this.simulacionForm.value.tiempo;
    const data = Array.from({ length: tiempo + 1 }, (_, i) => ({
      hora: `${i}h`,
      energia: ((this.energiaGenerada / tiempo) * i).toFixed(2)
    }));
    new ngxCsv(data, 'simulacion');
  }
}



