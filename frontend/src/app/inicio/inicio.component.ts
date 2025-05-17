import { Component, OnInit, inject } from '@angular/core';
import { NgbToast } from '@ng-bootstrap/ng-bootstrap';
import { RouterLink } from '@angular/router';
import { FaIconComponent } from '@fortawesome/angular-fontawesome';
import { faSun, faShieldAlt, faLightbulb, faCloud, faCalendarAlt } from '@fortawesome/free-solid-svg-icons';
import { SimulacionesService } from '../services/simulaciones.service';
import { Simulacion } from '../common/InterfaceSimulaciones';
import { CommonModule, NgForOf } from '@angular/common';

@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [
    CommonModule,
    NgbToast,
    RouterLink,
    FaIconComponent,
    NgForOf
  ],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent implements OnInit {
  ultimasSimulaciones: (Simulacion & { porcentajeCarga: number; color: string })[] = [];
  private readonly simulacionesService = inject(SimulacionesService);

  faSun = faSun;
  faShield = faShieldAlt;
  faIdea = faLightbulb;
  faCloud = faCloud;
  faCalendarAlt = faCalendarAlt;

  ngOnInit(): void {
    this.simulacionesService.getSimulaciones(1, 100).subscribe({
      next: res => {
        console.log('👉 Respuesta completa del backend:', res); // 👈 Añade esto

        const todas = Array.isArray(res) ? res : res.data ?? [];

        const ordenadas = todas
          .sort((a, b) => new Date(b.Fecha).getTime() - new Date(a.Fecha).getTime())
          .slice(0, 3);

        this.ultimasSimulaciones = ordenadas.map(sim => {
          const porcentajeCarga = Math.min(Math.round(sim.EnergiaGenerada), 100);
          let color = 'success';
          if (porcentajeCarga < 50) color = 'danger';
          else if (porcentajeCarga < 80) color = 'warning';

          return {...sim, porcentajeCarga, color};
        });
      },
      error: err => console.error('Error cargando simulaciones:', err)
    });
  }

  protected readonly faLightbulb = faLightbulb;
}
