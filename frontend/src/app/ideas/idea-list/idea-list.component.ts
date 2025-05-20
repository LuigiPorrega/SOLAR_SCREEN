import { Component, OnInit } from '@angular/core';
import { Idea } from '../../common/InterfaceIdeas';
import { IdeasService } from '../../services/ideas.service';
import {NgbModal, NgbPagination} from '@ng-bootstrap/ng-bootstrap';
import { AuthService } from '../../services/auth.service';
import { IdeaEditComponent } from '../idea-edit/idea-edit.component';
import { LoginComponent } from '../../login/login.component';
import {CommonModule, NgForOf, NgIf} from '@angular/common';


@Component({
  selector: 'app-idea-list',
  templateUrl: './idea-list.component.html',
  standalone: true,
  imports: [
    NgbPagination,
    NgIf,
    NgForOf,
    CommonModule,
  ],
  styleUrls: ['./idea-list.component.css']
})
export class IdeaListComponent implements OnInit {
  ideas: Idea[] = [];
  isLoading: boolean = false;
  toastShow: boolean = false;
  toast = {
    body: '',
    color: 'bg-success',
    duration: 2000,
  };

  // 🧭 Paginación
  currentPage: number = 1;
  perPage: number = 6;
  totalItems: number = 0;

  constructor(
    private ideasService: IdeasService,
    private modalService: NgbModal,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.cargarIdeas();
  }

  cargarIdeas(): void {
    this.isLoading = true;
    this.ideasService.getIdeas(this.currentPage, this.perPage).subscribe({
      next: res => {
        this.ideas = res.data || [];
        this.totalItems = res.totalItems || 0;
        this.isLoading = false;
      },
      error: () => {
        this.isLoading = false;
        this.mostrarToast('Error al cargar ideas', 'bg-danger');
      }
    });
  }

  cambiarPagina(pagina: number): void {
    this.currentPage = pagina;
    this.cargarIdeas();
  }

  abrirModalCrearIdea(): void {
    const isLogged = this.authService.isLoggedIn();

    if (!isLogged) {
      this.modalService.open(LoginComponent, { centered: true });
      return;
    }

    const modalRef = this.modalService.open(IdeaEditComponent, {
      size: 'lg',
      centered: true,
      backdrop: 'static'
    });

    modalRef.result.then(
      result => {
        if (result === 'idea-creada') {
          this.currentPage = 1; // volver al principio
          this.cargarIdeas();
          this.mostrarToast('¡Idea creada con éxito!', 'bg-success');
        }
      },
      () => {}
    );
  }

  mostrarToast(mensaje: string, color: string): void {
    this.toast.body = mensaje;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() => {
      this.toastShow = false;
    }, this.toast.duration);
  }
}
