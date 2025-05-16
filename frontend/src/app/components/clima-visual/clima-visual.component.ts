import {Component, ViewChild} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {BaseChartDirective, NgChartsModule} from 'ng2-charts';
import { ApiClimaService } from '../../services/api-clima.service';
import { ChartConfiguration, ChartType } from 'chart.js';


@Component({
  selector: 'app-clima-visual',
  standalone: true,
  imports: [CommonModule, FormsModule, NgChartsModule],
  templateUrl: './clima-visual.component.html',
  styleUrls: ['./clima-visual.component.css']
})
export class ClimaVisualComponent {
  ciudad: string = '';
  resultado: any;
  cargando = false;
  error = '';
  chartType: ChartType = 'bar';
  @ViewChild(BaseChartDirective) chart?: BaseChartDirective;

  chartData: ChartConfiguration['data'] = {
    labels: ['Temperatura', 'Sensación Térmica', 'Máxima', 'Mínima'],
    datasets: [
      {
        label: '°C',
        data: [],
        backgroundColor: ['#4dc9f6', '#f67019', '#f53794', '#537bc4']
      }
    ]
  };


  constructor(private climaService: ApiClimaService) {}

  buscarClima() {
    this.error = '';
    this.resultado = null;
    this.cargando = true;

    this.climaService.getClima(this.ciudad).subscribe({
      next: (data) => {
        console.log("✅ Datos recibidos", data);
        this.resultado = data;
        this.chartData.datasets[0].data = [
          data.main.temp,
          data.main.feels_like,
          data.main.temp_max,
          data.main.temp_min
        ];

        this.chart?.update();
        this.cargando = false;
      },
      error: (err) => {
        console.error("❌ Error al obtener clima:", err); // 👈 Mostramos el error real
        this.error = 'No se pudo obtener el clima. ¿Ciudad inexistente o fallo de red?';
        this.cargando = false;
      }
    });
  }

  chartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
      },
      title: {
        display: true,
        text: 'Visualización de datos climáticos'
      }
    }
  };


}
