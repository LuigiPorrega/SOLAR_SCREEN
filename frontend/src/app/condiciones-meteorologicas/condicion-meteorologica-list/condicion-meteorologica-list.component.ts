import { Component, OnInit, inject } from '@angular/core';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';
import {DatePipe, NgClass, NgForOf, NgIf} from '@angular/common';
import { RouterLink } from '@angular/router';
import {FaIconComponent, FontAwesomeModule, IconDefinition} from '@fortawesome/angular-fontawesome';
import { UnauthorizedComponent } from '../../core/unauthorized/unauthorized/unauthorized.component';
import {
  faEdit,
  faPlay,
  faPlus,
  faSun,
  faThermometerHalf,
  faTint,
  faTrash,
  faWind
} from '@fortawesome/free-solid-svg-icons';
import {
  faCloudRain,
  faCloud,
  faSmog,
  faCloudSun,
  faCloudSunRain
} from '@fortawesome/free-solid-svg-icons';
import {NgbModal} from '@ng-bootstrap/ng-bootstrap';
import {LoginComponent} from '../../login/login.component';


@Component({
  selector: 'app-condicion-meteorologica-list',
  standalone: true,
  imports: [
    NgForOf,
    NgIf,
    RouterLink,
    FaIconComponent,
    FontAwesomeModule,
    UnauthorizedComponent,
    DatePipe,
    NgClass
  ],
  templateUrl: './condicion-meteorologica-list.component.html',
  styleUrl: './condicion-meteorologica-list.component.css'
})
export class CondicionMeteorologicaListComponent implements OnInit {
  condiciones: CondicionMeteorologica[] = [];
  currentPage = 1;
  perPage = 5;
  totalPages = 0;
  mostrarModal = false;
  isLoggedIn = false;
  isLoading = false;


  faEdit = faEdit;
  faTrash = faTrash;

  private readonly service = inject(CondicionesMeteorologicasService);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly modalService = inject(NgbModal);

  ngOnInit(): void {
    this.isLoggedIn = this.authService.isLoggedIn();
    this.loadCondiciones();
  }

  loadCondiciones(): void {
    this.isLoading = true;
    this.service.getCondicionesMeteorologicas(this.currentPage, this.perPage).subscribe({
      next: res => {
        this.condiciones = res.data;
        this.totalPages = res.totalPages;
        this.isLoading = false;
      },
      error: err => {
        console.error(err);
        this.isLoading = false;
      }
    });
  }




  cambiarPagina(direccion: 'anterior' | 'siguiente'): void {
    if (direccion === 'anterior' && this.currentPage > 1) this.currentPage--;
    if (direccion === 'siguiente' && this.currentPage < this.totalPages) this.currentPage++;
    this.loadCondiciones();
  }

  abrirModalSimulacion(): void {
    if (!this.isLoggedIn) {
      this.mostrarModal = true;
    } else {
      console.log('Usuario autenticado, puedes abrir el formulario');
    }
  }

  usarCondicion(c: CondicionMeteorologica): void {
    if (!this.isLoggedIn) {
      this.mostrarModal = true;
      return;
    }

    // Podés usar localStorage, un servicio compartido o queryParams para pasar la condición
    localStorage.setItem('condicionSeleccionada', JSON.stringify(c));
    this.router.navigate(['/simulacion/create']);
  }

  determinarEstadoDelClima(c: CondicionMeteorologica): string {
    if (c.Humedad > 85 && c.LuzSolar < 3000) {
      return 'Lluvioso';
    } else if (c.LuzSolar > 7000 && c.Humedad < 60) {
      return 'Soleado';
    } else if (c.LuzSolar > 5000 && c.Humedad >= 60) {
      return 'Parcialmente nublado';
    } else if (c.LuzSolar < 4000 && c.Humedad < 80) {
      return 'Nublado';
    } else if (c.Humedad > 90 && c.Viento < 5) {
      return 'Niebla';
    } else if (c.Viento > 40) {
      return 'Ventoso';
    } else {
      return 'Variable';
    }
  }

  getIconoClima(c: CondicionMeteorologica): IconDefinition {
    const estado = this.determinarEstadoDelClima(c);

    if (estado.includes('Soleado')) return faSun;
    if (estado.includes('Lluvioso')) return faCloudRain;
    if (estado.includes('Parcialmente')) return faCloudSun;
    if (estado.includes('Nublado')) return faCloud;
    if (estado.includes('Niebla')) return faSmog;
    if (estado.includes('Ventoso')) return faWind;
    return faCloudSunRain; // Por defecto: variable
  }

  getColorClima(c: CondicionMeteorologica): string {
    const estado = this.determinarEstadoDelClima(c);

    if (estado.includes('Soleado')) return 'text-warning';       // Amarillo
    if (estado.includes('Lluvioso')) return 'text-primary';      // Azul
    if (estado.includes('Parcialmente')) return 'text-info';     // Celeste
    if (estado.includes('Nublado')) return 'text-secondary';     // Gris claro
    if (estado.includes('Niebla')) return 'text-muted';          // Gris oscuro
    if (estado.includes('Ventoso')) return 'text-light';         // Blanco suave
    return 'text-success'; // Verde para "variable"
  }

  protected readonly faWind = faWind;
  protected readonly faTint = faTint;
  protected readonly faThermometerHalf = faThermometerHalf;
  protected readonly faSun = faSun;
  protected readonly faPlay = faPlay;
  protected readonly faPlus = faPlus;

  crearCondicion() {
    const token = localStorage.getItem('token');

    if (token) {
      // ✅ Usuario logueado, redirigimos
      this.router.navigate(['/condiciones/nueva']);
    } else {
      // 🚫 Usuario NO logueado → abrimos el modal de login
      const modalRef = this.modalService.open(LoginComponent, { backdrop: 'static' });
      modalRef.componentInstance.routeToRedirect = '/condiciones/nueva';
    }
  }
}
