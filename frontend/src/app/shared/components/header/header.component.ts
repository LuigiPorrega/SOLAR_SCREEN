import {Component, inject} from '@angular/core';
import {Router, RouterLink, RouterLinkActive} from '@angular/router';
import {CartService} from '../../../services/cart.service';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import {
  faAddressCard,
  faCartShopping, faLightbulb,
  faMobile,
  faPhone,
  faSolarPanel,
  faSun,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import {FormsModule} from '@angular/forms';
import {AuthService} from '../../../services/auth.service';
import {DecimalPipe} from '@angular/common';

@Component({
  selector: 'app-header',
  imports: [
    RouterLink,
    RouterLinkActive,
    FaIconComponent,
    FormsModule,
    DecimalPipe,
  ],
  standalone: true,
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent {
  //llamo al controllador de la autenticación del login
  private readonly authService: AuthService = inject(AuthService);
  //llamo al router
  private readonly router: Router = inject(Router);
  //llamo al servicio del cart
  private readonly cartService: CartService = inject(CartService);


  //creo la funcion cantidadCarrito y la inicializo a 0
  cantidadCarrito: number = 0;
  precioCarrito: number = 0;

  //Toast
  toast = {
    body: '',
    color: 'bg-success',
    duration: 1500,
  }
  toastShow = false;

  private showToast(message: string, color: string, duration: number) {
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => {
      this.toastShow = false;
    }, duration);
  }

  //Fin del Toast

  protected readonly faPhone = faPhone;
  protected readonly faAddressCard = faAddressCard;

  constructor() {
    //Cantidad Carrito
    this.cartService.cantidadCarrito.subscribe({
      next: value => {
        this.cantidadCarrito = value;
      },
      error: err => {
        console.error(err);
        this.showToast(err.message(), 'bg-danger', 2000);
      }
    });
    //Precio Carrito
    this.cartService.precioCarrito.subscribe({
      next: value => {
        this.precioCarrito = value;
      },
      error: err => {
        console.error(err);
        this.showToast(err.message(), 'bg-danger', 2000);
      }
    });
  }

  protected readonly faUser = faUser;
  protected readonly faCartShopping = faCartShopping;
  protected readonly faSun = faSun;
  protected readonly faSolarPanel = faSolarPanel;
  protected readonly faMobile = faMobile;
  protected readonly faLightbulb = faLightbulb;


}
