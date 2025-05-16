import {Component, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router, RouterLink} from '@angular/router';
import {CondicionesMeteorologicasService} from '../../services/condiciones-meteorologicas.service';
import {HttpHeaders} from '@angular/common/http';
import {CondicionMeteorologica} from '../../common/InterfaceCondicionesMeteorologicas';
import {NgIf} from '@angular/common';
import {AuthService} from '../../services/auth.service';

@Component({
  selector: 'app-condicion-meteorologica-edit',
  imports: [
    RouterLink,
    ReactiveFormsModule,
    NgIf
  ],
  standalone: true,
  templateUrl: './condicion-meteorologica-edit.component.html',
  styleUrl: './condicion-meteorologica-edit.component.css'
})
export class CondicionMeteorologicaEditComponent implements OnInit {
  form!: FormGroup;
  isEditMode = false;
  isLoggedIn = false;
  userRole: string | null = null;

  constructor(private fb: FormBuilder, private authService: AuthService) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      Fecha: ['', Validators.required],
      LuzSolar: ['', Validators.required],
      Temperatura: ['', Validators.required],
      Humedad: ['', Validators.required],
      Viento: ['', Validators.required]
    });

    // Reactividad del login
    this.authService.loginStatus$.subscribe(userData => {
      this.isLoggedIn = !!userData;
      this.userRole = userData?.role ?? null;
    });

    // Inicialización por si ya está logueado
    const currentUser = this.authService.getUserData();
    this.isLoggedIn = !!currentUser;
    this.userRole = currentUser?.role ?? null;
  }

  puedeCrear(): boolean {
    return this.isLoggedIn && ['admin', 'usuario'].includes(this.userRole || '');
  }

  guardar(): void {
    if (this.form.valid) {
      const valores = this.form.value;
      console.log('Guardando condición:', valores);
      // Aquí tu lógica de crear o actualizar
    }
  }

}
