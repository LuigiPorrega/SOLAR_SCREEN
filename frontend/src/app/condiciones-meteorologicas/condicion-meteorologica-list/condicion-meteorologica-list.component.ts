import {Component, OnInit} from '@angular/core';
import {CondicionMeteorologica} from '../../common/InterfaceCondicionesMeteorologicas';
import {CondicionesMeteorologicasService} from '../../services/condiciones-meteorologicas.service';
import {NgForOf} from '@angular/common';
import {RouterLink} from '@angular/router';

@Component({
  selector: 'app-condicion-meteorologica-list',
  imports: [
    NgForOf,
    RouterLink
  ],
  standalone: true,
  templateUrl: './condicion-meteorologica-list.component.html',
  styleUrl: './condicion-meteorologica-list.component.css'
})
export class CondicionMeteorologicaListComponent implements OnInit {
  condiciones: CondicionMeteorologica[] = [];
  currentPage = 1;
  perPage = 5;
  totalPages = 0;

  constructor(private service: CondicionesMeteorologicasService) {}

  ngOnInit(): void {
    this.loadCondiciones();
  }

  loadCondiciones(): void {
    this.service.getCondicionesMeteorologicas(this.currentPage, this.perPage).subscribe({
      next: res => {
        this.condiciones = res.data;
        this.totalPages = res.totalPages;
      },
      error: err => console.error(err)
    });
  }

  eliminar(id: number): void {
    if (!confirm('¿Seguro que quieres eliminar esta condición meteorológica?')) return;
    this.service.deleteCondicionMeteorologica(id).subscribe({
      next: () => this.loadCondiciones(),
      error: err => console.error(err)
    });
  }

  cambiarPagina(direccion: 'anterior' | 'siguiente'): void {
    if (direccion === 'anterior' && this.currentPage > 1) this.currentPage--;
    if (direccion === 'siguiente' && this.currentPage < this.totalPages) this.currentPage++;
    this.loadCondiciones();
  }
}



