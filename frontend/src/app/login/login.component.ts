import { Component, ElementRef, inject, OnInit, ViewChild } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';
import {AbstractControl, FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators} from '@angular/forms';
import { CommonModule } from '@angular/common';
import { FormValidators } from '../validators/formValidators';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, FormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  authService = inject(AuthService);
  router = inject(Router);
  formBuilder = inject(FormBuilder);
  route = inject(ActivatedRoute);
  modalService = inject(NgbModal);
  activeModal = inject(NgbActiveModal, { optional: true });

  @ViewChild('passwordInput') passwordInput!: ElementRef;

  loginForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required, FormValidators.notOnlyWhiteSpace]],
    password: ['', [Validators.required]]
  });

  toast = {
    body: '',
    color: 'bg-success',
    duration: 1500,
  };
  toastShow = false;

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      if (params['autoLogin'] === 'true' && params['username']) {
        if (this.passwordInput && this.passwordInput.nativeElement) {
          this.passwordInput.nativeElement.focus();
        }

        setTimeout(() => {
          this.passwordInput?.nativeElement?.focus();
        }, 500);
      }
    });
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const credentials = this.loginForm.value;

    this.authService.login(credentials).subscribe({
      next: value => {
        const role = value.role || 'usuario';
        const nombre = credentials.username;

        this.showToast(`Bienvenido ${nombre}`, 'bg-success text-light', 1500);

        setTimeout(() => {
          this.cerrarModal();
          if (role === 'admin') {
            this.router.navigateByUrl('/admin/inicio');
          } else if (role === 'usuario') {
            this.router.navigateByUrl('/inicio');
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

  showToast(message: string, color: string, duration: number): void {
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => this.toastShow = false, duration);
  }

  cerrarModal(): void {
    this.activeModal?.close(); // si fue abierto como modal
  }

  redirigirARegistro(): void {
    this.cerrarModal();
    this.router.navigate(['/registrarse']);
  }

  public get username(): AbstractControl | null {
    return this.loginForm.get('username');
  }

  get password(): AbstractControl | null {
    return this.loginForm.get('password');
  }


  isPasswordInvalid(): boolean {
    const control = this.loginForm.get('password');
    return !!(control && control.invalid && (control.dirty || control.touched));
  }
}
