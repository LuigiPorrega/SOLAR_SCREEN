import {Component, inject, OnInit} from '@angular/core';
import {CommonModule, CurrencyPipe, DecimalPipe, NgForOf, NgIf} from '@angular/common';
import {CartService} from '../services/cart.service';
import {ModeloFunda} from '../common/InterfaceModelosFundas';
import {Router, RouterLink} from '@angular/router';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import * as bootstrap from 'bootstrap';
import { AuthService } from '../services/auth.service';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {faPlus, faMinus, faTrashAlt, faArrowLeft, faLock, faCheckCircle} from '@fortawesome/free-solid-svg-icons';
import {LoginComponent} from '../login/login.component';

@Component({
  selector: 'app-cart',
  imports: [
    CurrencyPipe,
    DecimalPipe,
    NgIf,
    RouterLink,
    NgForOf,
    FontAwesomeModule,
    CommonModule
  ],
  standalone: true,
  templateUrl: './cart.component.html',
  styleUrl: './cart.component.css'
})
export class CartComponent implements OnInit {
  private readonly cartService: CartService = inject(CartService);
  private authService = inject(AuthService);
  private modalService = inject(NgbModal);
  private router = inject(Router);

  fundas: ModeloFunda[] = [];
  precioTotal: number = 0;
  selectedFunda: ModeloFunda | null = null;


  constructor() {
  }

  ngOnInit(): void {
    this.loadFundas();
    this.loadPrecio();
  }


  private loadFundas() {
    this.cartService.carrito.subscribe({
      next: (value) => {
        this.fundas = value;
      },
      error: (err) => {
        console.error(err);
      }
    });
  }

  private loadPrecio() {
    this.cartService.precioCarrito.subscribe({
      next: (value) => {
        this.precioTotal = value;
      },
      error: (err) => {
        console.error(err);
      }
    });
  }

  incrementarCantidad(product: ModeloFunda) {
    this.cartService.addToCart(product);
  }

  decrementarCantidad(product: ModeloFunda) {
    this.cartService.decrementarCantidad(product);
  }

  eliminarFunda(product: ModeloFunda) {
    this.cartService.removeFromCart(product);
  }

  openModal(funda: ModeloFunda): void {
    this.selectedFunda = funda;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal')!);
    modal.show();
  }

  confirmDelete(): void {
    if (this.selectedFunda) {
      this.eliminarFunda(this.selectedFunda);
      this.selectedFunda = null;
    }
  }

  faPlus = faPlus;
  faMinus = faMinus;
  faTrashAlt = faTrashAlt;
  faArrowLeft = faArrowLeft;
  faLock = faLock;

  finalizarCompra() {
    const isLogged = this.authService.isLoggedIn();

    if (!isLogged) {
      this.modalService.open(LoginComponent, { centered: true, backdrop: 'static' });
      return;
    }

    const productos = this.fundas.map(f => ({
      ModelosFundasId: f.ID,
      Cantidad: f.Cantidad,
      Precio: f.Precio
    }));

    let pendientes = productos.length;
    let errores = 0;

    productos.forEach(prod => {
      this.cartService.guardarItemEnBackend(prod).subscribe({
        next: () => {
          pendientes--;
          if (pendientes === 0 && errores === 0) {
            // ✅ Mostrar modal de éxito y redirigir
            this.cartService.vaciarCarrito();
            const modal = new bootstrap.Modal(document.getElementById('successModal')!);
            modal.show();

            setTimeout(() => {
              modal.hide();
              this.router.navigate(['/inicio']);
            }, 3000);
          }
        },
        error: () => {
          errores++;
          pendientes--;
          if (pendientes === 0) {
            alert('❌ Ocurrió un error al finalizar la compra.');
          }
        }
      });
    });
  }

  protected readonly faCheckCircle = faCheckCircle;
}
