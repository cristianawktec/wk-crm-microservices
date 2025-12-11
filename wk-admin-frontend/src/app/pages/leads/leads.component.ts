import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-leads',
  templateUrl: './leads.component.html'
})
export class LeadsComponent implements OnInit {
  loading = true;
  leads: any[] = [];
  error: string | null = null;
  sellers: any[] = [];
  searchTerm: string = '';
  searchTimeout: any;

  constructor(private api: ApiService, private router: Router) {}

  ngOnInit(): void {
    this.loadSellers();
    this.loadLeads();
  }

  loadLeads() {
    this.loading = true;
    this.error = null;
    this.api.getLeads(this.searchTerm).subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) {
          this.leads = res;
        } else {
          this.leads = res?.data || [];
        }
        this.loading = false;
      },
      error: (err: any) => {
        console.warn('Failed to load leads', err);
        this.error = err?.message || 'Erro ao carregar leads';
        this.loading = false;
      }
    });
  }

  onSearchChange() {
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }
    this.searchTimeout = setTimeout(() => {
      this.loadLeads();
    }, 500);
  }

  clearSearch() {
    this.searchTerm = '';
    this.loadLeads();
  }

  loadSellers() {
    this.api.getSellers().subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) this.sellers = res;
        else this.sellers = res?.data || res || [];
      },
      error: (err: any) => {
        console.warn('Failed to load sellers', err);
        this.sellers = [];
      }
    });
  }

  getSellerName(id: string | null): string {
    if (!id) return '-';
    const s = this.sellers.find(x => x.id === id || x.id === Number(id) || x.id === (x.id && id));
    if (!s) return id;
    return s.name || s.nome || String(s.id);
  }

  getSellerEmail(id: string | null): string {
    if (!id) return '';
    const s = this.sellers.find(x => x.id === id || x.id === Number(id) || x.id === (x.id && id));
    if (!s) return '';
    return s.email || s.email_address || '';
  }

  statusLabel(status: string | null): string {
    const map: any = {
      new: 'Novo',
      contacted: 'Contactado',
      qualified: 'Qualificado',
      converted: 'Convertido',
      lost: 'Perdido'
    };
    return map[status || ''] || status || '-';
  }

  sourceLabel(source: string | null): string {
    const map: any = {
      web: 'Web',
      referral: 'Referência',
      event: 'Evento',
      outbound: 'Outbound',
      inbound: 'Inbound',
      ads: 'Anúncios'
    };
    return map[source || ''] || source || '—';
  }

  goNew() {
    this.loading = false;
    this.router.navigate(['/leads', 'new']);
  }

  editLead(id: any) {
    this.router.navigate(['/leads', id]);
  }

  deleteLead(id: any) {
    if (!confirm('Tem certeza que deseja excluir este lead?')) return;
    this.api.deleteLead(id).subscribe({ next: () => this.loadLeads(), error: () => { alert('Erro ao excluir lead'); } });
  }
}
