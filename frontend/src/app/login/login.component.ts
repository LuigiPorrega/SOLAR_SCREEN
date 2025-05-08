import { Component, ElementRef, inject, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Router, RouterLink } from '@angular/router';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { FormValidators } from '../validators/formValidators';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, FormsModule, RouterLink],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  // Servicios e inyección
  public authService = inject(AuthService);
  public router: Router = inject(Router);
  public formBuilder: FormBuilder = inject(FormBuilder);
  public route: ActivatedRoute = inject(ActivatedRoute);
  public modalService: NgbModal = inject(NgbModal);
  public activeModal: NgbActiveModal = inject (NgbActiveModal);
  @ViewChild('passwordInput') passwordInput!: ElementRef;

  // Formulario reactivo
  loginForm: FormGroup;

  constructor() {
    this.loginForm = this.formBuilder.group({
      username: ['', [Validators.required, FormValidators.notOnlyWhiteSpace]],
      password: ['', [Validators.required]]
    });
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      if (params['autoLogin'] === 'true' && params['username']) {
        this.loginForm.patchValue({
          username: params['username']
        });

        setTimeout(() => {
          this.passwordInput?.nativeElement?.focus();
        }, 500);
      }
    });
  }

  onSubmit(): void {
    if (this.loginForm.invalid) {
      // Marcar todos los controles como tocados para mostrar los errores
      this.loginForm.markAllAsTouched();
      return;
    }

    // Obtener las credenciales del formulario
    const credentials = this.loginForm.value;

    // Llamada al servicio de autenticación
    this.authService.login(credentials).subscribe({
      next: value => {
        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
        const role = userData.role;
        const nombre = userData.username;

        // Mostrar mensaje de bienvenida
        this.showToast(`Bienvenido ${nombre}`, 'bg-success text-light', 1500);
        this.cerrarModal();

        // Redirigir según el rol
        if (role === 'admin') {
          this.router.navigateByUrl('/admin/inicio');
        } else if (role === 'usuario') {
          this.router.navigateByUrl('/funda-list');
        } else {
          this.router.navigateByUrl('/');
        }
      },
      error: error => {
        const errorMsg = error.error?.message || 'Error desconocido';
        this.showToast('Credenciales incorrectas: ' + errorMsg, 'bg-danger text-light', 2000);
      }
    });
  }

  // Mostrar el toast de mensajes
  toast ={
    body: '',
    color: 'bg-success',
    duration: 1500,
  }
  toastShow = false;
  private showToast(message: string, color: string, duration: number){
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() =>{
      this.toastShow = false;
    },duration);
  }
  //Fin del Toast

  // Cerrar el modal
  cerrarModal(): void {
    this.activeModal.close();
  }

  // Accesores para el formulario

  public get username(): any {
    return this.loginForm.get('username');
  }

  public get password(): any {
    return this.loginForm.get('password');
  }
}
