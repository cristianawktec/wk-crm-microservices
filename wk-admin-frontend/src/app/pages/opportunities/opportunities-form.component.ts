import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastService } from '../../services/toast.service';

@Component({
  selector: 'app-opportunity-form',
  templateUrl: './opportunities-form.component.html'
})
export class OpportunitiesFormComponent implements OnInit {
  form: FormGroup;
  loading = true;
  currentId: string | null = null;
  customers: any[] = [];
  sellers: any[] = [];

  constructor(
    private fb: FormBuilder,
    private api: ApiService,
    private route: ActivatedRoute,
    private router: Router,
    private toast: ToastService
  ) {
    this.form = this.fb.group({
      title: ['', [Validators.required, Validators.maxLength(200)]],
      value: ['', [Validators.required, Validators.min(0)]],
      customer_id: ['', [Validators.required]],
      seller_id: [''],
      status: ['open', [Validators.required]],
      probability: ['', [Validators.min(0), Validators.max(100)]],
      notes: ['', [Validators.maxLength(500)]]
    });
  }

  ngOnInit(): void {
    this.loadCustomers();
    this.loadSellers();
    const id = this.route.snapshot.paramMap.get('id');
    if (id && id !== 'new') {
      this.currentId = id;
      this.loadOpportunity(id);
    } else {
      this.loading = false;
    }
  }

  loadCustomers() {
    this.api.getCustomers().subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) this.customers = res;
        else this.customers = res?.data || res || [];
      },
      error: (err: any) => {
        console.warn('Could not load customers', err);
        this.customers = [];
      }
    });
  }

  loadSellers() {
    this.api.getSellers().subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) this.sellers = res;
        else this.sellers = res?.data || res || [];
      },
      error: (err: any) => {
        console.warn('Could not load sellers', err);
        this.sellers = [];
      }
    });
  }

  loadOpportunity(id: string) {
    this.loading = true;
    this.api.getOpportunity(id).subscribe({
      next: (res: any) => {
        const data = res?.data || res || {};
        this.form.patchValue({
          title: data.title || '',
          value: data.value || '',
          customer_id: data.customer_id || data.client_id || '',
          seller_id: data.seller_id || '',
          status: data.status || 'open',
          probability: data.probability || '',
          notes: data.notes || ''
        });
        this.loading = false;
      },
      error: () => {
        this.toast.error('Erro', 'Não foi possível carregar a oportunidade.');
        this.loading = false;
      }
    });
  }

  save() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      this.toast.error('Dados inválidos', 'Corrija os campos destacados.');
      return;
    }
    this.loading = true;
    const payload: any = { ...this.form.value };
    if (payload.title) payload.title = payload.title.trim();
    if (payload.value) payload.value = parseFloat(payload.value);
    if (payload.probability) payload.probability = parseFloat(payload.probability);
    if (payload.notes) payload.notes = payload.notes.trim();
    
    if (this.currentId) {
      this.api.updateOpportunity(this.currentId, payload).subscribe({
        next: () => this.onSaved(),
        error: () => {
          this.toast.error('Erro', 'Não foi possível salvar a oportunidade.');
          this.loading = false;
        }
      });
    } else {
      this.api.createOpportunity(payload).subscribe({
        next: () => this.onSaved(),
        error: () => {
          this.toast.error('Erro', 'Não foi possível criar a oportunidade.');
          this.loading = false;
        }
      });
    }
  }

  onSaved() {
    this.loading = false;
    this.router.navigate(['/opportunities'], { queryParams: { saved: '1' } });
  }

  get f() { return this.form.controls; }

  statusLabel(status: string | null): string {
    const map: any = {
      'open': 'Aberta',
      'won': 'Ganha',
      'lost': 'Perdida',
      'negotiation': 'Em Negociação',
      'proposal': 'Proposta Enviada'
    };
    return map[status || ''] || status || '—';
  }
}
