import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-opportunities',
  templateUrl: './opportunities.component.html'
})
export class OpportunitiesComponent implements OnInit {
  loading = true;
  opportunities: any[] = [];
  error: string | null = null;
  searchTerm: string = '';
  statusFilter: string = '';
  searchTimeout: any;

  statusMap: { [key: string]: string } = {
    'open': 'Aberto',
    'won': 'Ganho',
    'lost': 'Perdido',
    'pending': 'Pendente',
    'closed': 'Fechado'
  };

  constructor(private api: ApiService, private router: Router) {}

  ngOnInit(): void {
    this.load();
  }

  load() {
    this.loading = true;
    this.error = null;
    const params: any = {};
    if (this.searchTerm) {
      params.search = this.searchTerm;
    }
    if (this.statusFilter) {
      params.status = this.statusFilter;
    }
    this.api.getOpportunities(params).subscribe({
      next: (res: any) => {
        // handle both paginated and array responses
        if (Array.isArray(res)) this.opportunities = res;
        else if (res?.data) this.opportunities = res.data;
        else this.opportunities = res || [];
        this.loading = false;
      },
      error: (err: any) => {
        console.warn('Failed to load opportunities', err);
        this.error = err?.message || 'Erro ao carregar oportunidades';
        this.loading = false;
      }
    });
  }

  onSearchChange() {
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }
    this.searchTimeout = setTimeout(() => {
      this.load();
    }, 500);
  }

  clearSearch() {
    this.searchTerm = '';
    this.load();
  }

  onStatusChange() {
    this.load();
  }

  goNew() {
    this.router.navigate(['/opportunities', 'new']);
  }

  edit(id: any) {
    this.router.navigate(['/opportunities', id]);
  }

  delete(id: any) {
    if (!confirm('Tem certeza que deseja excluir esta oportunidade?')) return;
    this.api.deleteOpportunity(id).subscribe({ next: () => this.load(), error: () => alert('Erro ao excluir oportunidade') });
  }
}
