import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
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
import { RouterModule } from '@angular/router';

@NgModule({
  imports: [BrowserModule, HttpClientModule, FormsModule, ReactiveFormsModule, RouterModule.forRoot([
    { path: '', component: DashboardComponent },
    { path: 'customers', component: CustomersComponent },
    { path: 'customers/new', component: CustomerFormComponent },
    { path: 'customers/:id', component: CustomerFormComponent },
    { path: 'leads', component: LeadsComponent },
    { path: 'leads/new', component: LeadFormComponent },
    { path: 'leads/:id', component: LeadFormComponent }
    ,
    { path: 'sellers', component: SellersComponent },
    { path: 'sellers/new', component: SellerFormComponent },
    { path: 'sellers/:id', component: SellerFormComponent }
    ,
    { path: 'sales', redirectTo: 'opportunities', pathMatch: 'full' },
    { path: 'opportunities', component: OpportunitiesComponent },
    { path: 'opportunities/new', component: OpportunitiesFormComponent },
    { path: 'opportunities/:id', component: OpportunitiesFormComponent }
  ] , { useHash: true })],
  declarations: [AppComponent, HeaderComponent, SidebarComponent, DashboardComponent, CustomersComponent, CustomerFormComponent, LeadsComponent, LeadFormComponent, SellersComponent, SellerFormComponent, SalesComponent, OpportunitiesComponent, OpportunitiesFormComponent],
  bootstrap: [AppComponent]
})
export class AppModule {}
