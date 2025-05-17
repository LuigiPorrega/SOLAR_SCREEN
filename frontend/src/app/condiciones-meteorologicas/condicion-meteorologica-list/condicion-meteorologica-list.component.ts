import { Component, OnInit, inject } from '@angular/core';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { AuthService } from '../../services/auth.service';
import { NgForOf, NgIf } from '@angular/common';
import { RouterLink } from '@angular/router';
import {FaIconComponent, FontAwesomeModule} from '@fortawesome/angular-fontawesome';
import {UnauthorizedComponent} from '../../core/unauthorized/unauthorized/unauthorized.component';
import { faEdit, faTrash } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-condicion-meteorologica-list',
  standalone: true,
  imports: [
    NgForOf,
    NgIf,
    RouterLink,
    FaIconComponent,
    FontAwesomeModule,
    UnauthorizedComponent
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

  faEdit = faEdit;
  faTrash = faTrash;


  private readonly service = inject(CondicionesMeteorologicasService);
  private readonly authService = inject(AuthService);

  ngOnInit(): void {
    this.isLoggedIn = this.authService.isLoggedIn();
    this.loadCondiciones();
  }

  loadCondiciones(): void {
    this.service.getCondicionesMeteorologicas(this.currentPage, this.perPage).subscribe({
      next: res => {
        this.condiciones = res.data;
        this.totalPages = res.totalPages;
      },
      error: err => console.error(err)
    });
  }

  eliminar(id: number): void {
    if (!confirm('¿Seguro que quieres eliminar esta condición meteorológica?')) return;
    this.service.deleteCondicionMeteorologica(id).subscribe({
      next: () => this.loadCondiciones(),
      error: err => console.error(err)
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
      // Aquí podrías redirigir a /simulaciones/nueva o abrir un modal real si quieres
      console.log('Usuario autenticado, puedes abrir el formulario');
    }
  }
}
