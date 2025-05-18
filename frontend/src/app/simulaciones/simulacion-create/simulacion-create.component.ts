import { Component, OnInit, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faSun, faLightbulb } from '@fortawesome/free-solid-svg-icons';
import { ToastrService } from 'ngx-toastr';
import { FundasService } from '../../services/fundas.service';
import { ApiClimaService } from '../../services/api-clima.service';
import { ModeloFunda } from '../../common/InterfaceModelosFundas';
import { SimulacionesService } from '../../services/simulaciones.service';
import { Router } from '@angular/router';
import { NgbModal, NgbModalModule } from '@ng-bootstrap/ng-bootstrap';
import { LoginComponent } from '../../login/login.component';
import { HttpHeaders } from '@angular/common/http';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { NuevaSimulacionDTO } from '../../common/InterfaceSimulaciones';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-simulacion-create',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, FontAwesomeModule, FormsModule, NgbModalModule],
  templateUrl: './simulacion-create.component.html',
  styleUrl: './simulacion-create.component.css'
})
export class SimulacionCreateComponent implements OnInit {
  form!: FormGroup;
  fundas: ModeloFunda[] = [];
  ciudad = '';
  temperatura: number | null = null;
  humedad: number | null = null;
  viento: number | null = null;

  faSun = faSun;
  faLightbulb = faLightbulb;

  isLoggedIn: boolean = false;
  public usuarioID!: number;

  private simulacionesService = inject(SimulacionesService);
  private router = inject(Router);
  private fundasService = inject(FundasService);
  private climaService = inject(ApiClimaService);
  private toastr = inject(ToastrService);
  private fb = inject(FormBuilder);
  private modalService = inject(NgbModal);
  private condicionesService = inject(CondicionesMeteorologicasService);
  private authService = inject(AuthService);

  ngOnInit(): void {
    this.isLoggedIn = !!localStorage.getItem('user');

    // 👇 nos aseguramos de tener siempre el ID actualizado si cambia el login
    this.authService.loginStatus$.subscribe(isLogged => {
      this.isLoggedIn = isLogged;
      this.usuarioID = this.authService.getUserIDFromToken(); // ✅ usamos tu método
    });

    this.usuarioID = this.authService.getUserIDFromToken(); // ✅ por si ya estaba logueado

    this.form = this.fb.group({
      tipoLuz: ['Luz solar directa', Validators.required],
      tipoFunda: ['Fija', Validators.required],
      tiempoMin: [null, [Validators.required, Validators.min(1)]],
      modeloFundaID: [null, Validators.required]
    });

    this.cargarFundasPorTipo('Fija');
  }

  buscarClima(): void {
    if (!this.ciudad.trim()) return;

    this.climaService.getClima(this.ciudad).subscribe({
      next: (data) => {
        this.temperatura = data.main.temp;
        this.humedad = data.main.humidity;
        this.viento = data.wind.speed;
        this.toastr.success('Datos del clima cargados correctamente 🌤️');
      },
      error: () => {
        this.toastr.error('No se pudo obtener el clima de la ciudad.');
      }
    });
  }

  cargarFundasPorTipo(tipoRaw: string): void {
    const tipo = tipoRaw.charAt(0).toUpperCase() + tipoRaw.slice(1).toLowerCase();

    const call = tipo === 'Fija'
      ? this.fundasService.getFundasFijas()
      : this.fundasService.getFundasExpandibles();

    call.subscribe({
      next: res => {
        const modeloActual = this.form.value.modeloFundaID;

        this.fundas = res.data;

        const sigueExistiendo = this.fundas.some(f => f.ID === modeloActual);
        if (!sigueExistiendo) {
          this.form.patchValue({ modeloFundaID: null });
        }

        this.toastr.info(`Fundas ${tipo.toLowerCase()} cargadas correctamente.`);
      },
      error: () => {
        this.toastr.error(`Error al cargar fundas ${tipo.toLowerCase()}`);
      }
    });
  }

  simulacionCalculada: any = null;

  continuarSimulacion(): void {
    if (this.form.invalid) {
      this.toastr.warning('Completa todos los campos para continuar.');
      return;
    }

    const energiaGenerada = this.calcularEnergiaGenerada();

    this.simulacionCalculada = {
      ...this.form.value,
      temperatura: this.temperatura,
      humedad: this.humedad,
      viento: this.viento,
      energiaGenerada,
      porcentajeCarga: Math.min(100, Math.round((energiaGenerada / 30000) * 100)),
      color:
        energiaGenerada >= 24000 ? 'success' :
          energiaGenerada >= 15000 ? 'warning' :
            'danger',
      modelo: this.fundas.find(f => f.ID == this.form.value.modeloFundaID)?.Nombre ?? 'Desconocido'
    };

    this.toastr.success(`⚡ ${energiaGenerada}W generados. Revisa la simulación abajo.`);
  }

  calcularEnergiaGenerada(): number {
    const tiempoMin = this.form.value.tiempoMin;
    const tipoLuz = this.form.value.tipoLuz;
    const modeloID = this.form.value.modeloFundaID;

    const modelo = this.fundas.find(f => f.ID == modeloID);
    if (!modelo) return 0;

    const tiempoHoras = tiempoMin / 60;
    const luzFactor =
      tipoLuz === 'Luz solar directa' ? 1 :
        tipoLuz === 'Luz solar indirecta' ? 0.6 :
          tipoLuz === 'Luz artificial' ? 0.3 : 0.5;

    const eficienciaClimatica = 1 -
      ((this.humedad ?? 0) / 100) * 0.1 -
      ((this.viento ?? 0) / 100) * 0.05 +
      ((this.temperatura ?? 25) - 20) * 0.005;

    const energia = modelo.CapacidadCarga * luzFactor * eficienciaClimatica * tiempoHoras;
    return Math.max(0, Number(energia.toFixed(2)));
  }

  guardarSimulacion(): void {
    console.log('🔍 userID actual:', this.usuarioID);
    console.log(this.isLoggedIn);

    if (!this.usuarioID) {
      this.toastr.error('No se pudo obtener tu ID de usuario para guardar la simulación.');
      return;
    }

    if (
      this.temperatura === null ||
      this.humedad === null ||
      this.viento === null
    ) {
      alert('⚠️ Debes seleccionar una ciudad para obtener datos climáticos antes de guardar la simulación.');
      return;
    }

    if (!this.simulacionCalculada) return;

    if (!this.isLoggedIn) {
      this.toastr.warning('Debes iniciar sesión para guardar la simulación');
      this.abrirModalLogin();
      return;
    }

    const condicion = {
      Fecha: new Date().toISOString().split('T')[0],
      LuzSolar: this.simulacionCalculada.tipoLuz === 'Luz solar directa' ? 1 :
        this.simulacionCalculada.tipoLuz === 'Luz solar indirecta' ? 0.6 : 0.3,
      Temperatura: this.simulacionCalculada.temperatura,
      Humedad: this.simulacionCalculada.humedad,
      Viento: this.simulacionCalculada.viento
    };

    const headers = new HttpHeaders({
      Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('user') || '{}').token,
      'Content-Type': 'application/json'
    });

    console.log('🌤️ Payload condición meteorológica:', condicion);

    this.condicionesService.addCondicionMeteorologica(condicion, headers).subscribe({
      next: (respuesta) => {
        const idCondicion = respuesta.data?.ID ?? respuesta.ID ?? null;

        if (!idCondicion) {
          this.toastr.error('No se pudo obtener el ID de la condición meteorológica');
          return;
        }

        const simulacionPayload: NuevaSimulacionDTO = {
          CondicionLuz: this.simulacionCalculada.tipoLuz,
          EnergiaGenerada: this.simulacionCalculada.energiaGenerada,
          Tiempo: this.simulacionCalculada.tiempoMin,
          Fecha: new Date().toISOString().split('T')[0],
          CondicionesMeteorologicasID: idCondicion,
          FundaID: this.simulacionCalculada.modeloFundaID,
          UsuarioID: this.usuarioID, // ✅ aquí se usa el ID extraído del token
        };

        console.log('⚡ Payload simulación:', simulacionPayload);

        this.simulacionesService.addSimulacion(simulacionPayload).subscribe({
          next: () => {
            console.log('✅ Payload final simulación:', simulacionPayload);
            this.toastr.success('Simulación guardada exitosamente.');
            this.router.navigate(['/simulaciones']);
          },
          error: () => {
            this.toastr.error('Error al guardar la simulación.');
          }
        });
      },
      error: () => {
        this.toastr.error('Error al guardar la condición meteorológica.');
      }
    });
  }

  abrirModalLogin(): void {
    this.modalService.open(LoginComponent, { centered: true });
  }
}
