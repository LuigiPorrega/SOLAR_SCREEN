import {Component, inject, OnInit,
  NgZone, ChangeDetectorRef} from '@angular/core';
import {Router, RouterLink, RouterLinkActive} from '@angular/router';
import {CartService} from '../../../services/cart.service';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import { CommonModule } from '@angular/common';
import {
  faAddressCard,
  faCartShopping, faCloudSunRain, faGear, faLightbulb,
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

  protected modalService: NgbModal = inject(NgbModal);
  protected modalConfig: NgbModalConfig = inject(NgbModalConfig);
  //Variable para el spinning
  public isLoggingOut: boolean = false;
  private ngZone: NgZone = inject(NgZone);
  constructor(private cdr: ChangeDetectorRef) {
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
    this.authService.loginStatus$.subscribe(isLogged => {
      this.isLoggedIn = isLogged;
      this.userName = this.authService.getUserName();
      this.userRole = this.authService.getUserRole();
    });
  }

  abrirModalLogin(): void {
    this.modalService.open(LoginComponent, { centered: true, size: 'sx' });
  }

  logout() {
    this.isLoggingOut = true;

    // Realiza el logout en el servicio
    this.authService.logout();


    // Usa setTimeout para manejar el spinner y el cambio de estado
    setTimeout(() => {
      // Cambia el estado de isLoggingOut y actualiza la vista
      this.isLoggingOut = false;

      // Aquí forzamos la detección de cambios para que la vista se actualice inmediatamente
      this.cdr.detectChanges();

      // Después de logout, redirige a la página de login
      this.ngZone.run(() => {
        this.router.navigate(['/inicio']);
      });
    }, 2500); // Duración del spinner
    this.showToast('Hasta luego', 'bg-success text-light', 2000);
  }




  goToBackend() {
    window.open('http://localhost:8000/admin/inicio', '_blank');
  }

  protected readonly faGear = faGear;
  protected readonly faScrewdriverWrench = faScrewdriverWrench;
  protected readonly faCloudSunRain = faCloudSunRain;
}

