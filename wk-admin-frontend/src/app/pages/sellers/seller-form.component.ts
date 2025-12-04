import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-seller-form',
  templateUrl: './seller-form.component.html'
})
export class SellerFormComponent implements OnInit {
  form: FormGroup;
  loading = true;
  currentId: string | null = null;
  roles: any[] = [];

  constructor(private fb: FormBuilder, private api: ApiService, private route: ActivatedRoute, private router: Router) {
    this.form = this.fb.group({
      name: ['', Validators.required],
      email: ['', Validators.email],
      phone: [''],
      role: [''],
      role_custom: ['']
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
    if (this.form.invalid) return;
    this.loading = true;
    const payload: any = Object.assign({}, this.form.value);
    if (payload.role === 'other' && payload.role_custom) payload.role = payload.role_custom;
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
}
