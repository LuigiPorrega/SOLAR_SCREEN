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

  //funciones:
  addToCart(product:ModeloFunda){
    var carritoAux = this.carrito.value;
    var precioCarrito = this.precioCarrito.value;
    carritoAux.push(product);
    this.carrito.next(carritoAux);
    this.cantidadCarrito.next(carritoAux.length);
    precioCarrito += product.Precio;
    this.precioCarrito.next(precioCarrito);
  }

  removeFromCart(product: ModeloFunda) {
    const carritoAux = this.carrito.value;
    const index = carritoAux.findIndex(item => item.ID === product.ID);

    if (index !== -1) {
      carritoAux.splice(index, 1);
      this.carrito.next(carritoAux);
      this.cantidadCarrito.next(carritoAux.length);

      const precioCarrito = this.precioCarrito.value - product.Precio;
      this.precioCarrito.next(precioCarrito);
    }
  }


  constructor() { }
}
