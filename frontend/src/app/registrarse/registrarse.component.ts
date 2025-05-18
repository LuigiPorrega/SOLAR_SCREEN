import { Component, inject } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl } from '@angular/forms';
import { RegistroUsuario } from '../common/InterfeceRegistroUsuario';
import { NgIf } from '@angular/common';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { LoginComponent } from '../login/login.component';

@Component({
  selector: 'app-registrarse',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    NgIf
  ],
  templateUrl: './registrarse.component.html',
  styleUrl: './registrarse.component.css'
})
export class RegistrarseComponent {

  public authService = inject(AuthService);
  public router: Router = inject(Router);
  public formBuilder: FormBuilder = inject(FormBuilder);
  public modalService = inject(NgbModal);

  registrarseForm: FormGroup = this.formBuilder.group({
    nombre: ['', Validators.required],
    correo: ['', [Validators.required, Validators.email]],
    fechaNacimiento: ['', Validators.required],
    googleID: [''],
    username: ['', Validators.required],
    password: ['', [Validators.required, Validators.minLength(6)]]
  });

  // Toast
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

  // Getters (ya no se queja el compilador)
  public get nombre(): AbstractControl {
    return this.registrarseForm.get('nombre')!;
  }
  public get correo(): AbstractControl {
    return this.registrarseForm.get('correo')!;
  }
  public get fechaNacimiento(): AbstractControl {
    return this.registrarseForm.get('fechaNacimiento')!;
  }
  public get googleID(): AbstractControl {
    return this.registrarseForm.get('googleID')!;
  }
  public get username(): AbstractControl {
    return this.registrarseForm.get('username')!;
  }
  public get password(): AbstractControl {
    return this.registrarseForm.get('password')!;
  }

  // Generar ID ficticio de Google
  private generateGoogleID(): string {
    return 'GID' + Math.random().toString(36).substring(2, 10).toUpperCase();
  }

  onSubmit(): void {
    if (this.registrarseForm.invalid) {
      this.registrarseForm.markAllAsTouched();
      return;
    }

    const usuario = this.registrarseForm.value as RegistroUsuario;
    usuario.googleID = this.generateGoogleID();

    this.authService.registrarse(usuario).subscribe({
      next: () => {
        this.showToast('Registrado correctamente!', 'bg-success text-light', 1500);
        setTimeout(() => {
          this.abrirModalLogin(usuario.username);
        }, 1500);
      },
      error: error => {
        const errorMsg = error.error?.message || 'Error desconocido';
        console.log(errorMsg);
        this.showToast('Credenciales incorrectas: ' + errorMsg, 'bg-danger text-light', 2000);
      }
    });
  }

  abrirModalLogin(username: string): void {
    const modalRef = this.modalService.open(LoginComponent, {
      backdrop: 'static',
      size: 'md',
      centered: true
    });

    modalRef.componentInstance.autoLoginData = { username };
  }
}
