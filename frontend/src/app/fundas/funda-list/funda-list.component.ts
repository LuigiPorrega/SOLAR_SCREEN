import { Component, inject, OnInit } from '@angular/core';
import { FundasService } from '../../services/fundas.service';
import { ModeloFunda } from '../../common/InterfaceModelosFundas';
import { faCartPlus } from '@fortawesome/free-solid-svg-icons';
import { NgbPagination } from '@ng-bootstrap/ng-bootstrap';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { FaIconComponent } from '@fortawesome/angular-fontawesome';
import { CurrencyPipe, NgClass, NgForOf, NgIf } from '@angular/common';
import { CartService } from '../../services/cart.service';
import { FormsModule } from '@angular/forms';
import {query} from '@angular/animations';

@Component({
  selector: 'app-funda-list',
  standalone: true,
  templateUrl: './funda-list.component.html',
  styleUrl: './funda-list.component.css',
  imports: [
    NgbPagination,
    RouterLink,
    RouterLinkActive,
    FaIconComponent,
    CurrencyPipe,
    NgClass,
    NgIf,
    NgForOf,
    FormsModule
  ]
})
export class FundaListComponent implements OnInit {
  private readonly fundaService = inject(FundasService);
  private readonly cartService = inject(CartService);
  private readonly router = inject(Router);

  // Datos
  fundas: ModeloFunda[] = [];
  fundasFiltradas: ModeloFunda[] = [];
  fundasPaginadas: ModeloFunda[] = [];

  // Estado de búsqueda y filtro
  nombreABuscar: string = '';
  tipoSeleccionado: 'Fija' | 'Expansible' | '' = '';

  // Paginación
  currentPage: number = 1;
  perPage: number = 9;
  totalItems: number = 0;
  totalPages: number = 1;

  // Iconos
  faCartPlus = faCartPlus;

  // Toast
  toast = {
    body: '',
    color: 'bg-success',
    duration: 1500
  };
  toastShow = false;

  ngOnInit(): void {
    this.obtenerFundas();
  }

  obtenerFundas(): void {
    this.fundaService.getFundas(1, 1000).subscribe({
      next: (res) => {
        this.fundas = res.data;
        this.filtrarFundas();
      },
      complete: () => {
        this.showToast('Fundas cargadas correctamente', 'bg-success text-light', 1500);
      },
      error: (error) => {
        console.error('Error al obtener fundas:', error);
        this.showToast(error.message, 'bg-danger text-light', 2000);
      }
    });
  }

  onTipoChange(tipo: 'Fija' | 'Expansible'): void {
    this.tipoSeleccionado = tipo;
    this.currentPage = 1;
    this.filtrarFundas();
  }

  onBuscar(): void {
    this.currentPage = 1;
    this.filtrarFundas();
  }

  filtrarFundas(): void {
    const normalizar = (texto: string) =>
      texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

    this.fundasFiltradas = this.fundas.filter(f => {
      const coincideTipo = !this.tipoSeleccionado ||
        normalizar(f.TipoFunda) === normalizar(this.tipoSeleccionado);

      const coincideNombre = !this.nombreABuscar ||
        normalizar(f.Nombre).includes(normalizar(this.nombreABuscar));

      return coincideTipo && coincideNombre;
    });

    this.totalItems = this.fundasFiltradas.length;
    this.totalPages = Math.ceil(this.totalItems / this.perPage);
    this.actualizarFundasPaginadas();
  }

  actualizarFundasPaginadas(): void {
    const start = (this.currentPage - 1) * this.perPage;
    const end = start + this.perPage;
    this.fundasPaginadas = this.fundasFiltradas.slice(start, end);
  }

  cambiarPagina(nuevaPagina: number): void {
    this.currentPage = nuevaPagina;
    this.actualizarFundasPaginadas();
  }

  irADetalle(funda: ModeloFunda): void {
    this.router.navigate(['/fundas', funda.ID]);
  }

  agregarAlCarrito(funda: ModeloFunda): void {
    const yaExiste = this.cartService.carrito.value.some(item => item.ID === funda.ID);
    if (yaExiste) {
      this.showToast(`"${funda.Nombre}" ya está en el carrito`, 'bg-warning text-dark', 1500);
    } else {
      this.cartService.addToCart(funda);
      this.showToast(`"${funda.Nombre}" añadida al carrito`, 'bg-primary text-white', 1500);
    }
  }

  protected showToast(message: string, color: string, duration: number): void {
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => {
      this.toastShow = false;
    }, duration);
  }
}
