import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { ApiClimaService } from '../../services/api-clima.service';
import { FundasService } from '../../services/fundas.service';
import { SimulacionesService } from '../../services/simulaciones.service';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import { ModeloFunda } from '../../common/InterfaceModelosFundas';
import { ActivatedRoute, Router } from '@angular/router';
import { Simulacion } from '../../common/InterfaceSimulaciones';
import { NgForOf, NgIf } from '@angular/common';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-simulacion-edit',
  templateUrl: './simulacion-edit.component.html',
  standalone: true,
  imports: [ReactiveFormsModule, NgIf, NgForOf],
  styleUrls: ['./simulacion-edit.component.css']
})
export class SimulacionEditComponent implements OnInit {
  private readonly authService = inject(AuthService);
  isLoggedIn: boolean = false;
  showLoginModal: boolean = false;

  form: FormGroup;
  condiciones: CondicionMeteorologica[] = [];
  fundas: ModeloFunda[] = [];
  usarApi: boolean = false;
  energiaGenerada: number | null = null;
  mensajeSimulacion = '';
  modoEdicion = false;
  simulacionId?: number;

  constructor(
    private fb: FormBuilder,
    private climaService: ApiClimaService,
    private condicionesService: CondicionesMeteorologicasService,
    private fundasService: FundasService,
    private simulacionesService: SimulacionesService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.form = this.fb.group({
      ciudad: [''],
      condicionID: [''],
      fundaID: ['', Validators.required],
      tiempo: [1, [Validators.required, Validators.min(1)]],
      fecha: [new Date().toISOString().substring(0, 10)]
    });
  }

  ngOnInit(): void {
    this.isLoggedIn = this.authService.isLoggedIn();
    this.loadCondiciones();
    this.loadFundas();

    this.simulacionId = Number(this.route.snapshot.paramMap.get('id'));
    if (this.simulacionId) {
      this.modoEdicion = true;
      this.simulacionesService.getSimulacion(this.simulacionId).subscribe(sim => {
        this.form.patchValue({
          condicionID: sim.CondicionesMeteorologicasID,
          fundaID: sim.FundaID,
          tiempo: sim.Tiempo,
          fecha: sim.Fecha
        });
        this.energiaGenerada = sim.EnergiaGenerada;
      });
    }
  }

  loadCondiciones() {
    this.condicionesService.getCondicionesMeteorologicas(1, 100).subscribe(resp => {
      this.condiciones = resp.data;
    });
  }

  loadFundas() {
    this.fundasService.getFundas(1, 100).subscribe(resp => {
      this.fundas = resp.data;
    });
  }

  toggleApiUso() {
    this.usarApi = !this.usarApi;
    if (!this.usarApi) {
      this.form.get('ciudad')?.setValue('');
    }
  }

  simular() {
    const tiempo = this.form.value.tiempo;
    if (this.usarApi) {
      const ciudad = this.form.value.ciudad;
      this.climaService.getClima(ciudad).subscribe(data => {
        const luzSolar = data.clouds.all;
        this.energiaGenerada = this.calcularEnergia(luzSolar, tiempo);
        this.mensajeSimulacion = `Simulación completada con datos de ${ciudad}`;
      });
    } else {
      const condicion = this.condiciones.find(c => c.ID === +this.form.value.condicionID);
      if (condicion) {
        const luz = condicion.LuzSolar;
        this.energiaGenerada = this.calcularEnergia(luz, tiempo);
        this.mensajeSimulacion = `Simulación completada usando condición ID ${condicion.ID}`;
      }
    }
  }

  calcularEnergia(luzSolar: number, tiempo: number): number {
    return Math.round((luzSolar / 100) * tiempo * 10);
  }

  guardarSimulacion() {
    if (!this.isLoggedIn) {
      this.showLoginModal = true;
      return;
    }

    const simulacion: Simulacion = {
      UsuarioID: 0,
      CondicionLuz: this.usarApi ? 'API' : 'BD',
      EnergiaGenerada: this.energiaGenerada || 0,
      Tiempo: this.form.value.tiempo,
      Fecha: this.form.value.fecha,
      CondicionesMeteorologicasID: +this.form.value.condicionID,
      FundaID: +this.form.value.fundaID
    };

    if (this.modoEdicion && this.simulacionId) {
      simulacion.ID = this.simulacionId;
      this.simulacionesService.updateSimulacion(simulacion).subscribe(() => {
        this.router.navigate(['/simulaciones/list']);
      });
    } else {
      this.simulacionesService.addSimulacion(simulacion).subscribe(() => {
        this.router.navigate(['/simulaciones/list']);
      });
    }
  }

  redirigirALogin() {
    this.showLoginModal = false;
    this.router.navigate(['/login']);
  }

  cerrarModal() {
    this.showLoginModal = false;
  }
}
