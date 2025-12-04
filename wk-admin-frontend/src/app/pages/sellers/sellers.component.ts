import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-sellers',
  templateUrl: './sellers.component.html'
})
export class SellersComponent implements OnInit {
  loading = true;
  sellers: any[] = [];
  error: string | null = null;

  constructor(private api: ApiService, private router: Router) {}

  ngOnInit(): void {
    this.loadSellers();
  }

  loadSellers() {
    this.loading = true;
    this.error = null;
    this.api.getSellers().subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) {
          this.sellers = res;
        } else {
          this.sellers = res?.data || res || [];
        }
        this.loading = false;
      },
      error: (err: any) => {
        console.warn('Failed to load sellers', err);
        this.error = err?.message || 'Erro ao carregar vendedores';
        this.loading = false;
      }
    });
  }

  goNew() {
    this.loading = false;
    this.router.navigate(['/sellers', 'new']);
  }

  editSeller(id: any) {
    this.router.navigate(['/sellers', id]);
  }

  deleteSeller(id: any) {
    if (!confirm('Tem certeza que deseja excluir este vendedor?')) return;
    this.api.deleteSeller(id).subscribe({ next: () => this.loadSellers(), error: () => { alert('Erro ao excluir vendedor'); } });
  }
}
