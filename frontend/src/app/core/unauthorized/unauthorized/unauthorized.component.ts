import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { faBan, faExclamationCircle, faPhone } from '@fortawesome/free-solid-svg-icons';
import { FaIconComponent } from '@fortawesome/angular-fontawesome';

@Component({
  selector: 'app-unauthorized',
  imports: [
    RouterLink,
    FaIconComponent
  ],
  standalone: true,
  templateUrl: './unauthorized.component.html',
  styleUrl: './unauthorized.component.css'
})
export class UnauthorizedComponent implements OnInit {
  @Output() cerrar = new EventEmitter<void>();

  cerrarModal() {
    this.cerrar.emit();
  }

  private readonly router = inject(Router);

  ngOnInit() {
    setTimeout(() => {
      this.cerrarModal(); // Emite evento cerrar
      this.router.navigate(['/']);
    }, 10000);
  }

  protected readonly faPhone = faPhone;
  protected readonly faBan = faBan;
  protected readonly faExclamationCircle = faExclamationCircle;
}
