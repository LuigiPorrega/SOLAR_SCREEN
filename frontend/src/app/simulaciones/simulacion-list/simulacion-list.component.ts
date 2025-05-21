import { Component, OnInit, inject } from '@angular/core';
import { CommonModule, NgForOf, DatePipe } from '@angular/common';
import { FaIconComponent } from '@fortawesome/angular-fontawesome';
import { faSun, faLightbulb, faCalendarAlt } from '@fortawesome/free-solid-svg-icons';
import { Simulacion } from '../../common/InterfaceSimulaciones';
import { SimulacionesService } from '../../services/simulaciones.service';
import {NavigationEnd, Router, RouterLink} from '@angular/router';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { NgbPagination } from '@ng-bootstrap/ng-bootstrap';
import {filter} from 'rxjs';

@Component({
  selector: 'app-simulacion-list',
  standalone: true,
  imports: [
    CommonModule,
    NgForOf,
    FaIconComponent,
    DatePipe,
    RouterLink,
    NgbPagination
  ],
  templateUrl: './simulacion-list.component.html',
  styleUrl: './simulacion-list.component.css'
})
export class SimulacionListComponent implements OnInit {
  private readonly simulacionesService = inject(SimulacionesService);
  private router : Router = inject(Router);
  simulaciones: (Simulacion & { porcentajeCarga: number; color: string })[] = [];
  simulacionesPaginadas: (Simulacion & { porcentajeCarga: number; color: string })[] = [];

  isLoading = true;
  isLoggedIn = !!localStorage.getItem('token');

  // Paginación
  currentPage: number = 1;
  perPage: number = 9;
  totalItems: number = 0;

  // Iconos
  faSun = faSun;
  faLightbulb = faLightbulb;
  faCalendarAlt = faCalendarAlt;

  // Toast
  toast = {
    body: '',
    color: 'bg-success',
    duration: 1500,
  };
  toastShow = false;

  ngOnInit(): void {
    if(history.state.toastMessage) {
      this.showToast(history.state.toastMessage, 'bg-warning text-light', 2000);
    }
    this.loadSimulaciones();
  }


  loadSimulaciones(): void {
    this.isLoading = true;
    this.simulacionesService.getSimulaciones(1, 1000).subscribe({
      next: (res) => {
        const datos = Array.isArray(res) ? res : res.data ?? [];

        // Ordenar por fecha descendente (más reciente primero)
        datos.sort((a, b) => new Date(b.Fecha).getTime() - new Date(a.Fecha).getTime());

        this.simulaciones = datos.map(sim => {
          const porcentajeCarga = Math.min(100, Math.round((sim.EnergiaGenerada / 30000) * 100));
          const color =
            porcentajeCarga >= 80 ? 'success' :
              porcentajeCarga >= 50 ? 'warning' :
                'danger';
          return { ...sim, porcentajeCarga, color };
        });

        this.totalItems = this.simulaciones.length;
        this.actualizarPaginacion();
      },
      error: (error) => {
        console.error('Error al cargar simulaciones:', error);
      },
      complete: () => {
        this.isLoading = false;
      }
    });
  }

  cambiarPagina(nuevaPagina: number): void {
    this.currentPage = nuevaPagina;
    this.actualizarPaginacion();
  }

  actualizarPaginacion(): void {
    const start = (this.currentPage - 1) * this.perPage;
    const end = start + this.perPage;
    this.simulacionesPaginadas = this.simulaciones.slice(start, end);
  }

  descargarSimulacionPDF(id: string) {
    const original = document.getElementById(id);
    if (!original) return;

    const clone = original.cloneNode(true) as HTMLElement;
    clone.style.background = '#ffffff';
    clone.style.color = '#000000';
    clone.style.boxShadow = 'none';
    clone.style.border = '2px solid #000';
    clone.style.padding = '20px';
    clone.style.fontSize = '16px';
    clone.style.fontWeight = '500';

    const descendants = clone.querySelectorAll('*');
    descendants.forEach((el) => {
      const htmlEl = el as HTMLElement;
      htmlEl.style.color = '#000000';
      htmlEl.style.filter = 'none';
      htmlEl.style.textShadow = 'none';
      htmlEl.style.boxShadow = 'none';
      htmlEl.style.borderColor = '#000000';
      htmlEl.style.fontWeight = '500';
      if (htmlEl.classList.contains('btn')) {
        htmlEl.style.display = 'none';
      }
    });

    const canvas = clone.querySelector('canvas') as HTMLCanvasElement;
    if (canvas) {
      const ctx = canvas.getContext('2d');
      if (ctx) {
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) {
          const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
          const contrast = avg > 128 ? 255 : 0;
          data[i] = data[i + 1] = data[i + 2] = contrast;
        }
        ctx.putImageData(imageData, 0, 0);
      }
    }

    clone.id = 'simulacion-preview-export';
    clone.style.position = 'fixed';
    clone.style.top = '-9999px';
    document.body.appendChild(clone);

    html2canvas(clone).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p', 'mm', 'a4');
      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      pdf.save('simulacion.pdf');
      document.body.removeChild(clone);

      this.showToast('Simulación exportada exitosamente.', 'bg-success text-light', 2000);
    }).catch(error => {
      console.error('❌ Error al generar PDF:', error);
      this.showToast('Error al generar la simulación en PDF.', 'bg-danger text-light', 2000);
    });
  }

  protected showToast(message: string, color: string, duration: number): void {
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => {
      this.toastShow = false;
    }, duration);
  }
}
