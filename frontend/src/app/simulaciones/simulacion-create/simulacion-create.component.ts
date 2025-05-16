import { Component, OnInit, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { ApiClimaService } from '../../services/api-clima.service';
import { CommonModule } from '@angular/common';
import { Chart, registerables } from 'chart.js';
import any = jasmine.any;

Chart.register(...registerables);
chart: any;

@Component({
  selector: 'app-simulacion-create',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './simulacion-create.component.html',
  styleUrls: ['./simulacion-create.component.css']
})
export class SimulacionCreateComponent  {

}
