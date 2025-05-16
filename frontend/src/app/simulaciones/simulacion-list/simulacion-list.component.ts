import {Component, inject, OnInit} from '@angular/core';
import {SimulacionesService} from '../../services/simulaciones.service';
import jwtDecode from 'jwt-decode';
import {Simulacion} from '../../common/InterfaceSimulaciones';

@Component({
  selector: 'app-simulacion-list',
  imports: [],
  standalone: true,
  templateUrl: './simulacion-list.component.html',
  styleUrl: './simulacion-list.component.css'
})
export class SimulacionListComponent implements OnInit{
  private readonly simulacionesService : SimulacionesService = inject (SimulacionesService);
  simulaciones: Simulacion[] = [];

  constructor(){}

  ngOnInit(){
    const userData = JSON.parse(localStorage.getItem('user') || '{}');
    const token = userData.token;

    const decodedToken: any = jwtDecode(token);
    const usuarioID = decodedToken.data.id;

    // Obtener simulaciones del usuario logueado
    this.simulacionesService.getSimulaciones(1, 10).subscribe((response) => {
      this.simulaciones = response.data.filter(simulacion => simulacion.UsuarioID === usuarioID);
    });
  }

}
