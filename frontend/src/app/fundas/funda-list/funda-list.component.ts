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

  fundas: ModeloFunda[] = [];
  fundasPaginadas: ModeloFunda[] = [];

  // Filtros
  nombreABuscar: string = '';
  tipoSeleccionado: 'Fija' | 'Expandible' | '' = '';

  // Paginación
  currentPage: number = 1;
  perPage: number = 9;
  totalItems: number = 0;
  totalPage: number = 0;
  isLoading = false;

  // Icono
  faCartPlus = faCartPlus;

  // Toast
  toast = {
    body: '',
    color: 'bg-success',
    duration: 1500,
  };
  toastShow = false;

  ngOnInit(): void {
    this.loadFundas(); // por defecto: carga todas paginadas
  }

  loadFundas(): void {
    this.isLoading = true;
    this.fundaService.getFundas(this.currentPage, this.perPage).subscribe({
      next: (res) => {
        const data = res.data.map(f => ({
          ...f,
          CapacidadCarga: Number(f.CapacidadCarga),
          Precio: Number(f.Precio),
          Cantidad: Number(f.Cantidad),
          Expansible: Number(f.Expansible),
        }));

        this.fundas = data;
        this.fundasPaginadas = data;
        this.totalItems = res.totalItems;
        this.totalPage = res.totalPages;
        this.tipoSeleccionado = '';
      },
      complete: () => {
        this.isLoading = false;
        this.showToast('Modelos de fundas cargados exitosamente.', 'bg-success text-light', 2000);
      },
      error: (error) => {
        this.isLoading = false;
        this.showToast('Error al cargar los modelos de fundas: ' + error.message, 'bg-danger text-light', 2000);
      }
    });
  }


  onTipoChange(tipo: 'Fija' | 'Expandible'): void {
    this.tipoSeleccionado = tipo;
    this.isLoading = true;

    this.fundaService.getFundas(this.currentPage, this.perPage, tipo.toLowerCase()).subscribe({
      next: (res) => {
        const data = res.data.map(f => ({
          ...f,
          CapacidadCarga: Number(f.CapacidadCarga),
          Precio: Number(f.Precio),
          Cantidad: Number(f.Cantidad),
          Expansible: Number(f.Expansible),
        }));

        this.fundas = data;
        this.fundasPaginadas = data;
        this.totalItems = res.totalItems;
        this.totalPage = res.totalPages;
      },
      complete: () => {
        this.isLoading = false;
        this.showToast(`Fundas ${tipo.toLowerCase()} cargadas correctamente`, 'bg-success text-light', 1500);
      },
      error: (err) => {
        this.isLoading = false;
        console.error(err);
        this.showToast('Error al cargar fundas', 'bg-danger text-light', 2000);
      }
    });
  }



  actualizarFundasPaginadas(): void {
    const start = (this.currentPage - 1) * this.perPage;
    const end = start + this.perPage;
    this.fundasPaginadas = this.fundas.slice(start, end);
  }

  cambiarPagina(nuevaPagina: number): void {
    this.currentPage = nuevaPagina;

    if (this.tipoSeleccionado) {
      this.onTipoChange(this.tipoSeleccionado);
    } else {
      this.loadFundas();
    }
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

  onBuscar(): void {
    const normalizar = (texto: string) =>
      texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

    const nombre = normalizar(this.nombreABuscar);

    const fundasFiltradas = this.fundas.filter(f =>
      normalizar(f.Nombre).includes(nombre)
    );

    this.totalItems = fundasFiltradas.length;
    this.fundasPaginadas = fundasFiltradas.slice(0, this.perPage);
    this.currentPage = 1;
  }

}
