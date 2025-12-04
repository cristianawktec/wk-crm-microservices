import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-opportunity-form',
  templateUrl: './opportunities-form.component.html'
})
export class OpportunitiesFormComponent implements OnInit {
  form: FormGroup;
  loading = false;
  isEdit = false;
  id: string | null = null;
  customers: any[] = [];
  sellers: any[] = [];

  constructor(private fb: FormBuilder, private api: ApiService, private route: ActivatedRoute, private router: Router) {
    this.form = this.fb.group({
      title: ['', Validators.required],
      client_id: [null],
      seller_id: [null],
      value: [null],
      currency: ['BRL'],
      probability: [null],
      status: ['open'],
      close_date: [null]
    });
  }

  ngOnInit(): void {
    this.loadSelects();
    this.id = this.route.snapshot.paramMap.get('id');
    this.isEdit = !!this.id && this.id !== 'new';
    if (this.isEdit && this.id) this.loadEntity(this.id);
  }

  loadSelects() {
    this.api.getCustomers().subscribe({ next: (r: any) => { this.customers = r?.data || r || []; }, error: () => {} });
    this.api.getSellers().subscribe({ next: (r: any) => { this.sellers = r?.data || r || []; }, error: () => {} });
  }

  loadEntity(id: string) {
    this.loading = true;
    this.api.getOpportunity(id).subscribe({
      next: (res: any) => {
        const data = res?.data || res || {};
        this.form.patchValue({
          title: data.title,
          client_id: data.client_id || data.customer_id || null,
          seller_id: data.seller_id || null,
          value: data.value || null,
          currency: data.currency || 'BRL',
          probability: data.probability || null,
          status: data.status || 'open',
          close_date: data.close_date || null
        });
        this.loading = false;
      },
      error: (err: any) => { this.loading = false; alert('Erro ao carregar oportunidade'); }
    });
  }

  submit() {
    if (this.form.invalid) return this.form.markAllAsTouched();
    const payload = this.form.value;
    this.loading = true;
    if (this.isEdit && this.id) {
      this.api.updateOpportunity(this.id, payload).subscribe({ next: () => { this.loading = false; this.router.navigate(['/opportunities']); }, error: () => { this.loading = false; alert('Erro ao atualizar'); } });
    } else {
      this.api.createOpportunity(payload).subscribe({ next: () => { this.loading = false; this.router.navigate(['/opportunities']); }, error: () => { this.loading = false; alert('Erro ao criar'); } });
    }
  }

  cancel() { this.router.navigate(['/opportunities']); }
}
