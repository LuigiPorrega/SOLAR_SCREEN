import { Component, OnInit } from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import { Router } from '@angular/router';
import { SimulacionesService } from '../../services/simulaciones.service';
import { CondicionesMeteorologicasService } from '../../services/condiciones-meteorologicas.service';
import { FundasService } from '../../services/fundas.service';

import { ToastrService } from 'ngx-toastr';
import jwtDecode from 'jwt-decode';
import { CondicionMeteorologica } from '../../common/InterfaceCondicionesMeteorologicas';
import { ModeloFunda } from '../../common/InterfaceModelosFundas';
import { Simulacion } from '../../common/InterfaceSimulaciones';
import {NgClass} from '@angular/common';

@Component({
  selector: 'app-simulacion-create',
  templateUrl: './simulacion-create.component.html',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    NgClass
  ],
  styleUrls: ['./simulacion-create.component.css']
})
export class SimulacionCreateComponent implements OnInit {
  simulacionForm!: FormGroup;
  condiciones: CondicionMeteorologica[] = [];
  fundas: ModeloFunda[] = [];
  condicionSeleccionada: CondicionMeteorologica | null = null;

  constructor(
    private fb: FormBuilder,
    private simulacionesService: SimulacionesService,
    private condicionesService: CondicionesMeteorologicasService,
    private fundasService: FundasService,
    private router: Router,
    private toastr: ToastrService
  ) {}

  ngOnInit(): void {
    this.simulacionForm = this.fb.group({
      nombre: ['', Validators.required],
      condicion: ['', Validators.required],
      fundaRecomendada: [''],
      energiaGenerada: [0],
      tiempoExposicion: [1, [Validators.required, Validators.min(1)]],
      CondicionLuz: ['Luz solar directa', Validators.required],
      fundaID: ['', Validators.required]
    });

    this.cargarCondiciones();
    this.cargarFundas();
  }

  cargarCondiciones(): void {
    this.condicionesService.getCondicionesMeteorologicas().subscribe({
      next: (res) => {
        this.condiciones = res.data;
      },
      error: () => {
        this.toastr.error('Error al cargar condiciones meteorológicas');
      }
    });
  }

  cargarFundas(): void {
    this.fundasService.getFundas(1, 300).subscribe({
      next: (res) => {
        this.fundas = res.data;
      },
      error: () => {
        this.toastr.error('Error al cargar fundas');
      }
    });
  }

  seleccionarCondicion(id: number): void {
    const condicion = this.condiciones.find(c => c.ID === id) || null;
    this.condicionSeleccionada = condicion;

    if (condicion) {
      this.simulacionForm.patchValue({
        condicion: `Luz: ${condicion.LuzSolar}, Temp: ${condicion.Temperatura}°C, Humedad: ${condicion.Humedad}%`
      });

      this.actualizarEnergiaGenerada();
    }
  }

  actualizarEnergiaGenerada(): void {
    const luzSolar = this.condicionSeleccionada?.LuzSolar || 0;
    const tiempo = this.simulacionForm.value.tiempoExposicion || 0;
    const energia = luzSolar * tiempo;

    this.simulacionForm.patchValue({ energiaGenerada: energia });
  }

  calcularFundaRecomendada(): string {
    const energia = this.simulacionForm.value.energiaGenerada;
    const tiempo = this.simulacionForm.value.tiempoExposicion;

    if (energia > 500 && tiempo >= 3) {
      return 'Funda de alta capacidad';
    } else if (energia > 200) {
      return 'Funda estándar';
    } else {
      return 'Funda compacta';
    }
  }

  crearSimulacion(): void {
    if (!this.simulacionForm.valid || !this.condicionSeleccionada) {
      this.toastr.warning('Completa todos los campos antes de continuar');
      return;
    }

    const token = localStorage.getItem('token');
    let usuarioID: number | undefined;

    if (token) {
      try {
        const decoded: any = jwtDecode(token);
        usuarioID = decoded.id;
      } catch (e) {
        this.toastr.error('Token inválido');
        return;
      }
    }

    const simulacion: Simulacion = {
      ID: undefined,
      nombre: this.simulacionForm.value.nombre,
      condicion: this.simulacionForm.value.condicion,
      fundaRecomendada: this.calcularFundaRecomendada(),
      EnergiaGenerada: this.simulacionForm.value.energiaGenerada,
      Tiempo: this.simulacionForm.value.tiempoExposicion,
      Fecha: new Date().toISOString(),
      CondicionesMeteorologicasID: this.condicionSeleccionada.ID!,
      CondicionLuz: this.simulacionForm.value.CondicionLuz,
      FundaID: this.simulacionForm.value.fundaID,
      UsuarioID: usuarioID!
    };

    this.simulacionesService.addSimulacion(simulacion).subscribe({
      next: () => {
        this.toastr.success('Simulación creada con éxito');
        this.router.navigate(['/simulaciones']);
      },
      error: () => {
        this.toastr.error('Error al crear simulación');
      }
    });
  }
}
