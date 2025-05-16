import {Component} from '@angular/core';
import {NgbToast} from "@ng-bootstrap/ng-bootstrap";
import {RouterLink} from '@angular/router';
import {FaIconComponent} from '@fortawesome/angular-fontawesome';
import {LucideAngularModule} from 'lucide-angular';



@Component({
  selector: 'app-inicio',
  imports: [
    NgbToast,
    RouterLink,
    FaIconComponent,
    LucideAngularModule
  ],
  standalone: true,
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent {


}
