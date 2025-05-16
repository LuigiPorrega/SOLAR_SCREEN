import { Injectable } from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {ModeloFunda} from '../common/InterfaceModelosFundas';

@Injectable({
  providedIn: 'root'
})
export class CartService {
  carrito: BehaviorSubject<ModeloFunda[]> = new BehaviorSubject<ModeloFunda[]>([]);
  cantidadCarrito: BehaviorSubject<number> = new BehaviorSubject<number>(0);
  precioCarrito: BehaviorSubject<number> = new BehaviorSubject<number>(0);

  constructor() {}

  addToCart(product: ModeloFunda) {
    const carritoAux = this.carrito.value;
    const existingProduct = carritoAux.find((item) => item.ID === product.ID);

    if (existingProduct) {
      existingProduct.Cantidad += 1;
    } else {
      product.Cantidad = 1;
      carritoAux.push(product);
    }

    this.updateCart(carritoAux);
  }

  decrementarCantidad(product: ModeloFunda) {
    const carritoAux = this.carrito.value;
    const existingProduct = carritoAux.find((item) => item.ID === product.ID);

    if (existingProduct && existingProduct.Cantidad > 1) {
      existingProduct.Cantidad -= 1;
    } else if (existingProduct) {
      // Si la cantidad llega a 0, eliminamos el producto
      this.removeFromCart(product);
      return;
    }

    this.updateCart(carritoAux);
  }

  removeFromCart(product: ModeloFunda) {
    const carritoAux = this.carrito.value.filter((item) => item.ID !== product.ID);
    this.updateCart(carritoAux);
  }

  private updateCart(carritoAux: ModeloFunda[]) {
    const totalPrecio = carritoAux.reduce((acc, item) => acc + item.Precio * item.Cantidad, 0);

    this.carrito.next(carritoAux);
    this.cantidadCarrito.next(carritoAux.length);
    this.precioCarrito.next(totalPrecio);
  }
}

