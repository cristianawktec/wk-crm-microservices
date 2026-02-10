import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';

@Component({
  selector: 'app-login-audits',
  templateUrl: './login-audits.component.html'
})
export class LoginAuditsComponent implements OnInit {
  loading = true;
  error: string | null = null;
  audits: any[] = [];
  perPage = 50;
  page = 1;
  lastPage = 1;
  total = 0;
  userIdFilter = '';
  searchQuery = '';

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.load();
  }

  load(page = 1): void {
    this.loading = true;
    this.error = null;
    this.page = page;

    const params: any = {
      page: this.page,
      per_page: this.perPage
    };

    if (this.userIdFilter) {
      params.user_id = this.userIdFilter;
    }

    if (this.searchQuery) {
      params.q = this.searchQuery;
    }

    this.api.getLoginAudits(params).subscribe({
      next: (res: any) => {
        const data = res?.data || {};
        this.audits = data.data || [];
        this.page = data.current_page || 1;
        this.lastPage = data.last_page || 1;
        this.total = data.total || 0;
        this.loading = false;
      },
      error: (err: any) => {
        this.error = err?.message || 'Erro ao carregar registros.';
        this.loading = false;
      }
    });
  }

  refresh(): void {
    this.load(this.page);
  }

  applyFilter(): void {
    this.load(1);
  }

  clearFilter(): void {
    this.userIdFilter = '';
    this.searchQuery = '';
    this.load(1);
  }

  prevPage(): void {
    if (this.page > 1) {
      this.load(this.page - 1);
    }
  }

  nextPage(): void {
    if (this.page < this.lastPage) {
      this.load(this.page + 1);
    }
  }

  trackById(index: number, item: any): any {
    return item?.id || index;
  }
}
