import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard';
import { LoginComponent } from './pages/login/login.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ClientesComponent } from './pages/clientes/clientes.component';
import { LeadsComponent } from './pages/leads/leads.component';
import { OportunidadesComponent } from './pages/oportunidades/oportunidades.component';
import { LayoutComponent } from './layout/layout.component';

const routes: Routes = [
  { 
    path: 'login', 
    component: LoginComponent 
  },
  {
    path: '',
    component: LayoutComponent,
    canActivate: [AuthGuard],
    children: [
      { 
        path: 'dashboard', 
        component: DashboardComponent 
      },
      { 
        path: 'clientes', 
        component: ClientesComponent 
      },
      { 
        path: 'leads', 
        component: LeadsComponent 
      },
      { 
        path: 'oportunidades', 
        component: OportunidadesComponent 
      },
      { 
        path: '', 
        redirectTo: 'dashboard', 
        pathMatch: 'full' 
      }
    ]
  },
  { 
    path: '**', 
    redirectTo: 'dashboard' 
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
