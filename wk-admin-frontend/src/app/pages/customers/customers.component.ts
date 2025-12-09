import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-customers',
  templateUrl: './customers.component.html',
  styleUrls: ['./customers.component.css']
})
export class CustomersComponent implements OnInit {
  loading = true;
  customers: any[] = [];
  error: string | null = null;
  successMessage: string | null = null;
  searchTerm: string = '';
  searchTimeout: any;

  constructor(private api: ApiService, public router: Router) {}

  ngOnInit(): void {
    this.loadCustomers();
    // show success message if redirected after save
    const params = new URLSearchParams(window.location.search);
    if (params.get('saved') === '1') {
      this.successMessage = 'Cliente salvo com sucesso.';
      setTimeout(() => this.successMessage = null, 4000);
      // remove query param from url
      const url = new URL(window.location.href);
      url.searchParams.delete('saved');
      window.history.replaceState({}, document.title, url.toString());
    }
  }

  goNew() {
    // ensure spinner is hidden when navigating to the new-customer form
    this.loading = false;
    this.router.navigate(['/customers', 'new']);
  }

  loadCustomers() {
    this.loading = true;
    this.error = null;
    this.api.getCustomers(this.searchTerm).subscribe({
      next: (res: any) => {
        // backend returns either an array (Customer::all()) or an object with data[], handle both
        if (Array.isArray(res)) {
          this.customers = res;
        } else {
          this.customers = res?.data || [];
        }
        this.loading = false;
      },
      error: (err: any) => {
        console.warn('Failed to load customers', err);
        this.error = err?.message || 'Erro ao carregar clientes';
        this.loading = false;
      }
    });
  }

  onSearchChange() {
    // Clear previous timeout
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }
    
    // Wait 500ms after user stops typing before searching
    this.searchTimeout = setTimeout(() => {
      this.loadCustomers();
    }, 500);
  }

  clearSearch() {
    this.searchTerm = '';
    this.loadCustomers();
  }

  editCustomer(id: any) {
    this.router.navigate(['/customers', id]);
  }

  deleteCustomer(id: any) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
    this.api.deleteCustomer(id).subscribe({ next: () => this.loadCustomers(), error: () => { alert('Erro ao excluir cliente'); } });
  }
}
