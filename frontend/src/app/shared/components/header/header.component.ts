import {Component, inject, OnInit, ViewChild, TemplateRef, NgZone} from '@angular/core';
import {Router, RouterLink, RouterLinkActive} from '@angular/router';
import {CartService} from '../../../services/cart.service';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import { CommonModule } from '@angular/common';
import {
  faAddressCard,
  faCartShopping, faGear, faLightbulb,
  faMobile,
  faPhone, faScrewdriverWrench,
  faSolarPanel,
  faSun,
  faUser
} from '@fortawesome/free-solid-svg-icons';
import {FormsModule} from '@angular/forms';
import {AuthService} from '../../../services/auth.service';
import {DecimalPipe} from '@angular/common';
import {NgbModal, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import {LoginComponent} from '../../../login/login.component';


@Component({
  selector: 'app-header',
  imports: [
    RouterLink,
    RouterLinkActive,
    FaIconComponent,
    FormsModule,
    DecimalPipe,
    CommonModule,
    LoginComponent,
  ],
  standalone: true,
  providers: [NgbModalConfig, NgbModal],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent implements OnInit {
  //llamo al controllador de la autenticación del login
  protected readonly authService: AuthService = inject(AuthService);
  //llamo al router
  protected readonly router: Router = inject(Router);
  //llamo al servicio del cart
  protected readonly cartService: CartService = inject(CartService);
  protected readonly ngZone: NgZone = inject(NgZone);

  protected modalService: NgbModal = inject(NgbModal);
  protected modalConfig: NgbModalConfig = inject(NgbModalConfig);

  constructor() {
    this.modalConfig.backdrop = 'static'; // No permitir cerrar el modal al hacer clic fuera
    this.modalConfig.keyboard = false; // No permitir cerrarlo con el teclado (Esc)

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

  protected showToast(message: string, color: string, duration: number) {
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
  protected readonly faUser = faUser;
  protected readonly faCartShopping = faCartShopping;
  protected readonly faSun = faSun;
  protected readonly faSolarPanel = faSolarPanel;
  protected readonly faMobile = faMobile;
  protected readonly faLightbulb = faLightbulb;

// Para cambiar el botón del Login a Logout
  public isLoggedIn: boolean = false;
  public userName: string | null = null;
  public userRole: string | null = null;

  ngOnInit(): void {
    this.actualizarEstadoLogin();
    // Suscribirse a cambios del localStorage si los controlas desde otros componentes (opcional)
    window.addEventListener('storage', () => {
      this.actualizarEstadoLogin();
    });
  }

  protected actualizarEstadoLogin(): void {
    this.isLoggedIn = this.authService.isLoggedIn();
    this.userName = this.authService.getUserName();
    this.userRole = this.authService.getUserRole();
  }

  abrirModalLogin(): void {
    this.modalService.open(LoginComponent, { centered: true, size: 'lg' });
  }

  logout() {
    this.authService.logout();
  }



  goToBackend() {
    window.open('http://localhost:8000/admin/inicio', '_blank');
  }

  protected readonly faGear = faGear;
  protected readonly faScrewdriverWrench = faScrewdriverWrench;


}

