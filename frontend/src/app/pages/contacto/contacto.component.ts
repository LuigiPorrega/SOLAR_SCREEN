import {Component, inject} from '@angular/core';
import {Router} from '@angular/router';
import {FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators} from '@angular/forms';
import {faFacebook, faInstagram} from '@fortawesome/free-brands-svg-icons';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import {FormValidators} from '../../validators/formValidators';
import {NgbToast} from '@ng-bootstrap/ng-bootstrap';
import {ContactoService} from '../../services/contacto.service';

@Component({
  selector: 'app-contacto',
  imports: [
    FormsModule,
    FaIconComponent,
    ReactiveFormsModule,
    NgbToast
  ],
  standalone: true,
  templateUrl: './contacto.component.html',
  styleUrl: './contacto.component.css'
})
export class ContactoComponent {
  private readonly router: Router = inject (Router);
  private readonly contactoService: ContactoService = inject(ContactoService);

  protected readonly faFacebook = faFacebook;
  protected readonly faInstagram = faInstagram;

  //Toast
  toast ={
    body: '',
    color: 'bg-success',
    duration: 1500,
  }
  toastShow = false;
  private showToast(message: string, color: string, duration: number){
    this.toast.body = message;
    this.toast.color = color;
    this.toastShow = true;
    setTimeout(() =>{
      this.toastShow = false;
    },duration);
  }
  //Fin del Toast

  //Formulario reactivo
  private readonly formBuilder : FormBuilder = inject (FormBuilder);
  contactoForm : FormGroup = this.formBuilder.group({
    nombre: ['', [Validators.required,FormValidators.notOnlyWhiteSpace, FormValidators.forbiddenName(/xxx|sex|drug/i)]],
    email: ['', [Validators.required, Validators.email]],
    mensaje: ['', [Validators.required, FormValidators.notOnlyWhiteSpace, FormValidators.forbiddenName(/xxx|sex|drug/i)]]
  });

  //Getters
  get nombre(): any{
    return this.contactoForm.get('nombre');
  }
  get email(): any {
    return this.contactoForm.get('email');
  }
  get mensaje(): any {
    return this.contactoForm.get('mensaje');
  }

  onSubmit(): void {
    if (this.contactoForm.valid) {
      this.contactoService.enviarFormulario(this.contactoForm.value)
        .then(() => {
          console.log('Mensaje enviado correctamente');
          this.showToast('Mensaje enviado correctamente!','bg-success text-light', 1500);

          this.contactoForm.reset();
        })
        .catch(error => {
            console.error('Error completo:', JSON.stringify(error, null, 2));
            alert(JSON.stringify(error));

          // EmailJSResponseStatus tiene propiedades útiles como status y text
          const mensajeError = error?.text || `Error ${error?.status || ''}: No se pudo enviar el mensaje`;
          this.showToast(mensajeError, 'bg-danger text-light', 3000);
        });
    }
  }
}
