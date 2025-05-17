import { Component, OnInit } from '@angular/core';
import { SimulacionesService } from '../../services/simulaciones.service';
import { Simulacion } from '../../common/InterfaceSimulaciones';
import { ToastrService } from 'ngx-toastr';
import {DatePipe, NgForOf, NgIf} from '@angular/common';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import {NgChartsModule} from 'ng2-charts';

@Component({
  selector: 'app-simulacion-list',
  standalone: true,
  templateUrl: './simulacion-list.component.html',
  imports: [
    DatePipe,
    FaIconComponent,
    NgIf,
    NgForOf,
    NgChartsModule
  ],
  styleUrls: ['./simulacion-list.component.css']
})
export class SimulacionListComponent implements OnInit {

  simulaciones: Simulacion[] = [];
  currentPage = 1;
  totalPages = 0;
  perPage = 6;
  isLoggedIn = false;

  constructor(
    private simulacionesService: SimulacionesService,
    private toastr: ToastrService
  ) {
  }

  ngOnInit(): void {
    this.checkLoginStatus();
    this.loadSimulaciones();
  }

  checkLoginStatus(): void {
    const user = localStorage.getItem('user');
    this.isLoggedIn = !!user;
  }

  loadSimulaciones(): void {
    this.simulacionesService.getSimulaciones(this.currentPage, this.perPage).subscribe({
      next: (response) => {
        this.simulaciones = response.data;
        this.totalPages = response.totalPages;
        this.perPage = response.perPage;
      },
      error: () => {
        this.toastr.error('Error al cargar las simulaciones');
      }
    });
  }

  cambiarPagina(pagina: number): void {
    this.currentPage = pagina;
    this.loadSimulaciones();
  }

  // Datos para el grafico
  getChartData(sim: Simulacion) {
    const tiempo = sim.Tiempo;
    const energiaTotal = sim.EnergiaGenerada;

    const data = Array.from({length: tiempo + 1}, (_, i) =>
      Number(((energiaTotal / tiempo) * i).toFixed(2))
    );

    return {data, label: 'Energía acumulada (W)'};
  }

  getChartLabels(sim: Simulacion) {
    return Array.from({length: sim.Tiempo + 1}, (_, i) => `${i}h`);
  }

  chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    elements: {
      line: {
        tension: 0.3
      }
    }
  };


//Descargar Simulación con Grafico
  descargarSimulacionPDF(sim: Simulacion): void {
    if (!this.isLoggedIn) {
      this.toastr.warning('Debes iniciar sesión para exportar simulaciones');
      return;
    }

    const idGrafico = `grafico-${sim.ID}`;
    const data = document.getElementById(idGrafico);

    if (!data) {
      this.toastr.error('No se encontró el gráfico para exportar.');
      return;
    }

    html2canvas(data).then(canvas => {
      const imgWidth = 180;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      const contentDataURL = canvas.toDataURL('image/png');

      const pdf = new jsPDF();
      pdf.setFontSize(12);
      pdf.text(`Simulación #${sim.ID}`, 10, 10);
      pdf.text(`Fecha: ${sim.Fecha}`, 10, 20);
      pdf.text(`Condición de luz: ${sim.CondicionLuz}`, 10, 30);
      pdf.text(`Energía Generada: ${sim.EnergiaGenerada}W`, 10, 40);
      pdf.text(`Tiempo: ${sim.Tiempo}h`, 10, 50);
      pdf.text(`Funda Recomendada: ${sim.fundaRecomendada}`, 10, 60);

      // Imagen debajo del texto
      pdf.addImage(contentDataURL, 'PNG', 10, 70, imgWidth, imgHeight);

      pdf.save(`simulacion-${sim.ID}.pdf`);
    });
  }
}
