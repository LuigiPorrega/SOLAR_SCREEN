import {Component, inject, OnInit} from '@angular/core';
import {CurrencyPipe, DecimalPipe, NgForOf, NgIf} from '@angular/common';
import {CartService} from '../services/cart.service';
import {ModeloFunda} from '../common/InterfaceModelosFundas';
import {RouterLink} from '@angular/router';
import * as bootstrap from 'bootstrap';

@Component({
  selector: 'app-cart',
  imports: [
    CurrencyPipe,
    DecimalPipe,
    NgIf,
    RouterLink,
    NgForOf
  ],
  standalone: true,
  templateUrl: './cart.component.html',
  styleUrl: './cart.component.css'
})
export class CartComponent implements OnInit {
  private readonly cartService: CartService = inject(CartService);

  fundas: ModeloFunda[] = [];
  precioTotal: number = 0;
  selectedFunda: ModeloFunda | null = null;

  constructor() {}

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
}
