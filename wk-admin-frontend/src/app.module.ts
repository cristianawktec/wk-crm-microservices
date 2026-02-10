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
import { AdminGuard } from './app/guards/admin.guard';
import { RouterModule } from '@angular/router';
import { LoginAuditsComponent } from './app/pages/login-audits/login-audits.component';

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
        component: DashboardComponent
      },
      {
        path: 'admin/login-audits',
        component: LoginAuditsComponent,
        canActivate: [AuthGuard, AdminGuard]
      },
      { 
        path: 'customers', 
        component: CustomersComponent
      },
      { 
        path: 'customers/new', 
        component: CustomerFormComponent
      },
      { 
        path: 'customers/:id', 
        component: CustomerFormComponent
      },
      { 
        path: 'leads', 
        component: LeadsComponent
      },
      { 
        path: 'leads/new', 
        component: LeadFormComponent
      },
      { 
        path: 'leads/:id', 
        component: LeadFormComponent
      },
      { 
        path: 'sellers', 
        component: SellersComponent
      },
      { 
        path: 'sellers/new', 
        component: SellerFormComponent
      },
      { 
        path: 'sellers/:id', 
        component: SellerFormComponent
      },
      { 
        path: 'sales', 
        redirectTo: 'opportunities', 
        pathMatch: 'full' 
      },
      { 
        path: 'opportunities', 
        component: OpportunitiesComponent
      },
      { 
        path: 'opportunities/new', 
        component: OpportunitiesFormComponent
      },
      { 
        path: 'opportunities/:id', 
        component: OpportunitiesFormComponent
      },
      { 
        path: 'reports', 
        component: ReportsComponent
      },
      { 
        path: 'reports', 
        component: ReportsComponent
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
    LoginAuditsComponent,
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
