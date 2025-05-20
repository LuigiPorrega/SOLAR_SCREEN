import { Component, OnInit, inject } from '@angular/core';
import { CommonModule, NgForOf, DatePipe } from '@angular/common';
import { FaIconComponent } from '@fortawesome/angular-fontawesome';
import { faSun, faLightbulb, faCalendarAlt } from '@fortawesome/free-solid-svg-icons';
import {Simulacion} from '../../common/InterfaceSimulaciones';
import {SimulacionesService} from '../../services/simulaciones.service';
import {RouterLink} from '@angular/router';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

@Component({
  selector: 'app-simulacion-list',
  standalone: true,
  imports: [
    CommonModule,
    NgForOf,
    FaIconComponent,
    DatePipe,
    RouterLink
  ],
  templateUrl: './simulacion-list.component.html',
  styleUrl: './simulacion-list.component.css'
})
export class SimulacionListComponent implements OnInit {
  simulaciones: (Simulacion & { porcentajeCarga: number; color: string })[] = [];
  isLoggedIn = !!localStorage.getItem('token'); // O usa AuthService si lo tienes
  private readonly simulacionesService = inject(SimulacionesService);
  isLoading = true;
  faSun = faSun;
  faLightbulb = faLightbulb;
  faCalendarAlt = faCalendarAlt;

  //Toast
  toast = {
    body: '',
    color: 'bg-success',
    duration: 1500,
  }
  toastShow = false;

  protected showToast(message: string, color: string, duration: number) {
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => {
      this.toastShow = false;
    }, duration);
  }
  //Fin del Toast


  ngOnInit(): void {
    this.simulacionesService.getSimulaciones(1, 100).subscribe({
      next: (res) => {
        const todas = Array.isArray(res) ? res : res.data ?? [];
        this.isLoading = false;

        this.simulaciones = todas.map(sim => {
          const porcentajeCarga = Math.min(Math.round(sim.EnergiaGenerada), 100);
          this.isLoading = false;

          // Lógica de color corregida
          let color: string;
          if (porcentajeCarga < 50) color = 'danger';
          else if (porcentajeCarga < 80) color = 'warning';
          else color = 'success';

          return {...sim, porcentajeCarga, color};
        });
      },
      error: (error) => {
        console.error('Error al cargar simulaciones:', error);
        this.isLoading = false;
      }
    });
  }

  //Descargar una simulacion en pdf con grafico
  descargarSimulacionPDF(id: string) {
    const original = document.getElementById(id);
    if (!original) return;

    const clone = original.cloneNode(true) as HTMLElement;

    // Estilos para impresión
    clone.style.background = '#ffffff';
    clone.style.color = '#000000';
    clone.style.boxShadow = 'none';
    clone.style.border = '2px solid #000';
    clone.style.padding = '20px';
    clone.style.fontSize = '16px';
    clone.style.fontWeight = '500';

    // Ajustar todos los hijos
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
        htmlEl.style.display = 'none'; // Oculta el botón dentro del PDF
      }
    });

    // Forzar blanco y negro al canvas (gráfico)
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

    // Preparar clon invisible en DOM
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


}
