import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastService } from '../../services/toast.service';

@Component({
  selector: 'app-seller-form',
  templateUrl: './seller-form.component.html'
})
export class SellerFormComponent implements OnInit {
  form: FormGroup;
  loading = true;
  currentId: string | null = null;
  roles: any[] = [];

  constructor(
    private fb: FormBuilder,
    private api: ApiService,
    private route: ActivatedRoute,
    private router: Router,
    private toast: ToastService
  ) {
    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(120)]],
      email: ['', [Validators.email, Validators.maxLength(180)]],
      phone: ['', [Validators.maxLength(30)]],
      monthly_goal: ['', [Validators.min(0)]],
      role: ['', [Validators.maxLength(80)]],
      role_custom: ['', [Validators.maxLength(80)]]
    });
  }

  ngOnInit(): void {
    this.loadRoles();
    const id = this.route.snapshot.paramMap.get('id');
    if (id && id !== 'new') {
      this.currentId = id;
      this.loadSeller(id);
    } else {
      this.loading = false;
    }
  }

  loadSeller(id: string) {
    this.loading = true;
    this.api.getSeller(id).subscribe({
      next: (res: any) => {
        this.form.patchValue(res);
        this.loading = false;
      },
      error: () => {
        alert('Erro ao carregar vendedor');
        this.loading = false;
      }
    });
  }

  save() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      this.toast.error('Dados inválidos', 'Corrija os campos destacados (ex.: email inválido).');
      return;
    }
    this.loading = true;
    const payload: any = { ...this.form.value };
    if (payload.role === 'other' && payload.role_custom) payload.role = payload.role_custom;
    if (payload.phone) payload.phone = payload.phone.trim();
    if (payload.email) payload.email = payload.email.trim();
    if (payload.name) payload.name = payload.name.trim();
    if (payload.monthly_goal) payload.monthly_goal = parseFloat(payload.monthly_goal);
    delete payload.role_custom;
    if (this.currentId) {
      this.api.updateSeller(this.currentId, payload).subscribe({ next: () => this.onSaved(), error: () => { alert('Erro ao salvar'); this.loading = false; } });
    } else {
      this.api.createSeller(payload).subscribe({ next: () => this.onSaved(), error: () => { alert('Erro ao salvar'); this.loading = false; } });
    }
  }

  loadRoles() {
    this.api.getSellerRoles().subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) this.roles = res;
        else this.roles = res?.data || res || [];
      },
      error: (err: any) => {
        console.warn('Could not load roles', err);
        this.roles = [];
      }
    });
  }

  onSaved() {
    this.loading = false;
    this.router.navigate(['/sellers'], { queryParams: { saved: '1' } });
  }

  get f() { return this.form.controls; }

  roleLabel(role: string | null): string {
    const map: any = {
      'sales_rep': 'Representante de Vendas',
      'sales_representative': 'Representante de Vendas',
      'manager': 'Gerente de Vendas',
      'sales_manager': 'Gerente de Vendas',
      'director': 'Diretor',
      'account_executive': 'Executivo de Contas',
      'business_development': 'Desenvolvimento de Negócios',
      'inside_sales': 'Vendas Internas',
      'enterprise_sales': 'Vendas Corporativas'
    };
    return map[role || ''] || role || '—';
  }
}
