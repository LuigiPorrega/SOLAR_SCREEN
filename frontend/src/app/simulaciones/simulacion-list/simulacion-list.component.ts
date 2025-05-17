import { Component, OnInit, inject } from '@angular/core';
import { CommonModule, NgForOf, DatePipe } from '@angular/common';
import { FaIconComponent } from '@fortawesome/angular-fontawesome';
import { faSun, faLightbulb, faCalendarAlt } from '@fortawesome/free-solid-svg-icons';
import jsPDF from 'jspdf';
import {Simulacion} from '../../common/InterfaceSimulaciones';
import {SimulacionesService} from '../../services/simulaciones.service';
import {RouterLink} from '@angular/router';

@Component({
  selector: 'app-simulacion-list',
  standalone: true,
  imports: [
    CommonModule,
    NgForOf,
    FaIconComponent,
    DatePipe,
    RouterLink
  ],
  templateUrl: './simulacion-list.component.html',
  styleUrl: './simulacion-list.component.css'
})
export class SimulacionListComponent implements OnInit {
  simulaciones: (Simulacion & { porcentajeCarga: number; color: string })[] = [];
  isLoggedIn = !!localStorage.getItem('token'); // O usa AuthService si lo tienes
  private readonly simulacionesService = inject(SimulacionesService);
  isLoading = true;
  faSun = faSun;
  faLightbulb = faLightbulb;
  faCalendarAlt = faCalendarAlt;

  ngOnInit(): void {
    this.simulacionesService.getSimulaciones(1, 100).subscribe({
      next: (res) => {
        const todas = Array.isArray(res) ? res : res.data ?? [];
        this.isLoading = false;

        this.simulaciones = todas.map(sim => {
          const porcentajeCarga = Math.min(Math.round(sim.EnergiaGenerada), 100);
          this.isLoading = false;

          // Lógica de color corregida
          let color: string;
          if (porcentajeCarga < 50) color = 'danger';
          else if (porcentajeCarga < 80) color = 'warning';
          else color = 'success';

          return { ...sim, porcentajeCarga, color };
        });
      },
      error: (error) => {
        console.error('Error al cargar simulaciones:', error);
        this.isLoading = false;
      }
    });
  }

  descargarSimulacionPDF(sim: Simulacion) {
    const doc = new jsPDF();
    doc.text(`Simulación: ${sim.CondicionLuz ?? 'N/A'}`, 10, 10);
    doc.text(`Fecha: ${new Date(sim.Fecha).toLocaleDateString()}`, 10, 20);
    doc.text(`Tiempo: ${sim.Tiempo}h`, 10, 30);
    doc.text(`Energía generada: ${sim.EnergiaGenerada}`, 10, 40);
    doc.save('simulacion.pdf');
  }
}
