import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { IdeasService } from '../../services/ideas.service';
import { AuthService } from '../../services/auth.service';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import {FormValidators} from '../../validators/formValidators';

@Component({
  selector: 'app-idea-edit',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './idea-edit.component.html',
})
export class IdeaEditComponent implements OnInit {
  form!: FormGroup;
  isLoggedIn = false;

  private fb = inject(FormBuilder);
  private ideaService = inject(IdeasService);
  private authService = inject(AuthService);
  protected modal = inject(NgbActiveModal); // si lo usás como modal
  private router = inject(Router);

  toast = {
    body: '',
    color: 'bg-success',
    show: false
  };

  ngOnInit(): void {
    this.isLoggedIn = !!localStorage.getItem('user');
    if (!this.isLoggedIn) {
      this.router.navigate(['/login']);
    }

    this.form = this.fb.group({
      Titulo: [
        '',
        [
          Validators.required,
          Validators.maxLength(100),
          FormValidators.notOnlyWhiteSpace,
          FormValidators.forbiddenName(/(idiota|tonto|puta|cabrón|sexo|sex|drug)/i)
        ]
      ],
      Descripcion: [
        '',
        [
          Validators.required,
          Validators.maxLength(1000),
          FormValidators.notOnlyWhiteSpace,
          FormValidators.forbiddenName(/(idiota|tonto|puta|cabrón|sexo|sex|drug)/i)
        ]
      ]
    });

  }

  guardarIdea() {
    if (this.form.invalid) {
      this.mostrarToast('Completa todos los campos', 'bg-warning');
      return;
    }

    const payload = this.form.value;

    this.ideaService.addIdea(payload).subscribe({
      next: (res) => {
        this.mostrarToast('💡 Idea guardada con éxito', 'bg-success');
        setTimeout(() => this.modal.close('refresh'), 1500);
      },
      error: () => {
        this.mostrarToast('Error al guardar la idea', 'bg-danger');
      }
    });
  }

  mostrarToast(msg: string, color: string) {
    this.toast.body = msg;
    this.toast.color = color;
    this.toast.show = true;
    setTimeout(() => this.toast.show = false, 2000);
  }
}
