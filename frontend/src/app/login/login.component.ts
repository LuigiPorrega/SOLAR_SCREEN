import {Component, inject} from '@angular/core';
import { AuthService } from '../services/auth.service';
import {Router, RouterLink} from '@angular/router';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { FormValidators } from '../validators/formValidators';
import {NgbModal, NgbToast} from '@ng-bootstrap/ng-bootstrap';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import { Modal } from 'bootstrap';
import * as bootstrap from 'bootstrap';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, FormsModule, NgbToast, FaIconComponent, RouterLink],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  public authService = inject(AuthService);
  public router: Router = inject(Router);
  public formBuilder: FormBuilder = inject(FormBuilder);
  public modalService: NgbModal = inject(NgbModal);

  loginForm: FormGroup = this.formBuilder.group(
    {
      username: ['', [Validators.required, FormValidators.notOnlyWhiteSpace]],
      password: ['', [Validators.required]]
    }
  );

  public get username(): any {
    return this.loginForm.get('username');
  }

  public get password(): any {
    return this.loginForm.get('password');
  }

  public toast = {
    body: '',
    color: 'bg-success',
    duration: 1500,
  };
  public toastShow = false;

  public showToast(message: string, color: string, duration: number) {
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => {
      this.toastShow = false;
    }, duration);
  }

  private cerrarModal(): void {
    const modalElement = document.getElementById('loginModal');
    if (modalElement) {
      const modalInstance = bootstrap.Modal.getInstance(modalElement) || new Modal(modalElement);
      modalInstance.hide();
    }
  }

  // Función de envío del formulario
  onSubmit() {
    console.log('Formulario válido:', this.loginForm.valid);
    console.log('Errores de username:', this.username.errors);
    console.log('Errores de password:', this.password.errors);

    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const credentials = this.loginForm.value;

    // Llamada al servicio de autenticación
    this.authService.login(credentials).subscribe({
      next: value => {
        const role = value.data.role;
        const nombre = value.data.username;

        // Guarda en localStorage
        localStorage.setItem('user', JSON.stringify({
          role,
          username: nombre,
          token: value.data.token
        }));

        const mensaje = role === 'admin'
          ? `Bienvenido, administrador ${nombre}`
          : role === 'usuario'
            ? `Bienvenido, ${nombre}`
            : 'Bienvenido';

        this.showToast(mensaje, 'bg-success text-light', 1500);

        // Cerrar el modal después de login exitoso
        this.cerrarModal();

        setTimeout(() => {
          if (role === 'admin') {
            window.location.href = 'http://localhost:8000/admin/inicio';
          } else if (role === 'usuario') {
            this.router.navigateByUrl('/api/simulaciones');
          } else {
            this.router.navigateByUrl('/');
          }
        }, 1500);
      },
      error: error => {
        const errorMsg = error.error?.message || 'Error desconocido';
        this.showToast('Credenciales incorrectas: ' + errorMsg, 'bg-danger text-light', 2000);
      }
    });
  }
}
