import { Component } from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {ClimaVisualComponent} from '../clima-visual/clima-visual.component';
@Component({
  selector: 'app-clima-simulador',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ClimaVisualComponent
  ],
  templateUrl: './clima-simulador.component.html',
  styleUrls: ['./clima-simulador.component.css']
})
export class ClimaSimuladorComponent {

}
