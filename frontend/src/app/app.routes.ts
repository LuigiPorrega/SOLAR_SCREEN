import { Routes } from '@angular/router';
import {InicioComponent} from './inicio/inicio.component';
import {
  CondicionMeteorologicaListComponent
} from './condiciones-meteorologicas/condicion-meteorologica-list/condicion-meteorologica-list.component';
import {
  CondicionMeteorologicaEditComponent
} from './condiciones-meteorologicas/condicion-meteorologica-edit/condicion-meteorologica-edit.component';
import {FundaListComponent} from './fundas/funda-list/funda-list.component';
import {FundaEditComponent} from './fundas/funda-edit/funda-edit.component';
import {FundaDetailComponent} from './fundas/funda-detail/funda-detail.component';
import {IdeaEditComponent} from './ideas/idea-edit/idea-edit.component';
import {IdeaListComponent} from './ideas/idea-list/idea-list.component';
import {SimulacionListComponent} from './simulaciones/simulacion-list/simulacion-list.component';
import {SimulacionEditComponent} from './simulaciones/simulacion-edit/simulacion-edit.component';
import {CartComponent} from './cart/cart.component';
import {authGuard} from './guards/auth.guard';
import {LoginComponent} from './login/login.component';
import {ContactoComponent} from './pages/contacto/contacto.component';
import {AboutComponent} from './pages/about/about.component';
import {UnauthorizedComponent} from './core/unauthorized/unauthorized/unauthorized.component';
import {RegistrarseComponent} from './registrarse/registrarse.component';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'inicio',
    pathMatch: 'full',
  },
  {
    path: 'inicio',
    component: InicioComponent,
  },
  {
    path: 'unauthorized',
    component: UnauthorizedComponent,
  },
  {
    path: 'login',
    component: LoginComponent,
  },
  {
    path: 'registrarse',
    component: RegistrarseComponent,
  },

  {
    path: 'contacto',
    component: ContactoComponent,
  },
  {
    path: 'about',
    component: AboutComponent,
  },
  {
    path: 'condicion-meteorologica-list',
    component: CondicionMeteorologicaListComponent,
  },
  {
    path: 'condicion-meteorologica-add',
    component: CondicionMeteorologicaEditComponent,
    canActivate: [authGuard],
  },
  {
    path: 'condicion-meteorologica-edit/:id',
    component: CondicionMeteorologicaEditComponent,
    canActivate: [authGuard],
  },
  {
    path: 'funda-list',
    component: FundaListComponent,
  },
  {
    path: 'funda-add',
    component: FundaEditComponent,
    canActivate: [authGuard],

  },
  {
    path: 'funda-edit/:id',
    component: FundaEditComponent,
    canActivate: [authGuard],
  },
  {
    path: 'funda-detail/:id',
    component: FundaDetailComponent,
  },
  {
    path: 'idea-list',
    component: IdeaListComponent,
  },
  {
    path: 'idea-add',
    component: IdeaEditComponent,
    canActivate: [authGuard],
  },
  {
    path: 'idea-edit/:id',
    component: IdeaEditComponent,
    canActivate: [authGuard],
  },
  {
    path: 'simulacion-list',
    component: SimulacionListComponent,
  },
  {
    path: 'simulacion-add',
    component: SimulacionEditComponent,
  },
  {
    path: 'simulacion-edit/:id',
    component: SimulacionEditComponent,
    canActivate: [authGuard],
  },
  {
    path: 'cart',
    component: CartComponent,
  },
  {
    path: '**',
    redirectTo: 'inicio',
    pathMatch: 'full',
  }
];
