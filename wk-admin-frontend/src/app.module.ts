import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { CommonModule } from '@angular/common';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AppComponent } from './app.component';
import { HeaderComponent } from './app/shared/header/header.component';
import { SidebarComponent } from './app/shared/sidebar/sidebar.component';
import { DashboardComponent } from './app/pages/dashboard/dashboard.component';
import { CustomersComponent } from './app/pages/customers/customers.component';
import { CustomerFormComponent } from './app/pages/customers/customer-form.component';
import { LeadsComponent } from './app/pages/leads/leads.component';
import { LeadFormComponent } from './app/pages/leads/lead-form.component';
import { SellersComponent } from './app/pages/sellers/sellers.component';
import { SellerFormComponent } from './app/pages/sellers/seller-form.component';
import { SalesComponent } from './app/pages/sales/sales.component';
import { OpportunitiesComponent } from './app/pages/opportunities/opportunities.component';
import { OpportunitiesFormComponent } from './app/pages/opportunities/opportunities-form.component';
import { ReportsComponent } from './app/pages/reports/reports.component';
import { LoginComponent } from './app/components/login/login.component';
import { ToastContainerComponent } from './app/components/toast-container/toast-container.component';
import { AuthInterceptor } from './app/interceptors/auth.interceptor';
import { AuthGuard } from './app/guards/auth.guard';
import { RouterModule } from '@angular/router';

@NgModule({
  imports: [
    BrowserModule,
    CommonModule, 
    HttpClientModule, 
    FormsModule, 
    ReactiveFormsModule, 
    RouterModule.forRoot([
      { path: 'login', component: LoginComponent },
      { 
        path: '', 
        component: DashboardComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'customers', 
        component: CustomersComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'customers/new', 
        component: CustomerFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'customers/:id', 
        component: CustomerFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'leads', 
        component: LeadsComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'leads/new', 
        component: LeadFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'leads/:id', 
        component: LeadFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'sellers', 
        component: SellersComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'sellers/new', 
        component: SellerFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'sellers/:id', 
        component: SellerFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'sales', 
        redirectTo: 'opportunities', 
        pathMatch: 'full' 
      },
      { 
        path: 'opportunities', 
        component: OpportunitiesComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'opportunities/new', 
        component: OpportunitiesFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'opportunities/:id', 
        component: OpportunitiesFormComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'reports', 
        component: ReportsComponent,
        canActivate: [AuthGuard]
      },
      { 
        path: 'reports', 
        component: ReportsComponent,
        canActivate: [AuthGuard]
      }
    ], { useHash: true })
  ],
  declarations: [
    AppComponent, 
    HeaderComponent, 
    SidebarComponent, 
    DashboardComponent, 
    CustomersComponent, 
    CustomerFormComponent, 
    LeadsComponent, 
    LeadFormComponent, 
    SellersComponent, 
    SellerFormComponent, 
    SalesComponent, 
    OpportunitiesComponent, 
    OpportunitiesFormComponent,
    LoginComponent,
    ToastContainerComponent
  ],
  providers: [
    AuthGuard,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
