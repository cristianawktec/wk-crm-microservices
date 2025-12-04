import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-lead-form',
  templateUrl: './lead-form.component.html'
})
export class LeadFormComponent implements OnInit {
  form: FormGroup;
  loading = true;
  currentId: string | null = null;
  sellers: any[] = [];
  sources: any[] = [];

  constructor(private fb: FormBuilder, private api: ApiService, private route: ActivatedRoute, private router: Router) {
    this.form = this.fb.group({
      name: ['', Validators.required],
      email: ['', Validators.email],
      phone: [''],
      source: [''],
      source_custom: [''],
      status: ['new'],
      seller_id: ['']
    });
  }

  ngOnInit(): void {
    this.loadSellers();
    this.loadSources();
    const id = this.route.snapshot.paramMap.get('id');
    if (id && id !== 'new') {
      this.currentId = id;
      this.loadLead(id);
    } else {
      this.loading = false;
    }
  }

  loadLead(id: string) {
    this.loading = true;
    this.api.getLead(id).subscribe({
      next: (res: any) => {
        // ensure seller_id is present in the form if returned
        const payload = Object.assign({}, res);
        if (res.seller_id) payload.seller_id = res.seller_id;
        this.form.patchValue(payload);
        this.loading = false;
      },
      error: () => {
        alert('Erro ao carregar lead');
        this.loading = false;
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

  loadSources() {
    this.api.getLeadSources().subscribe({
      next: (res: any) => {
        if (Array.isArray(res)) this.sources = res;
        else this.sources = res?.data || res || [];
      },
      error: (err: any) => {
        console.warn('Could not load lead sources', err);
        this.sources = [];
      }
    });
  }

  save() {
    if (this.form.invalid) return;
    this.loading = true;
    const payload: any = Object.assign({}, this.form.value);
    if (payload.source === 'other' && payload.source_custom) {
      payload.source = payload.source_custom;
    }
    // remove helper field
    delete payload.source_custom;
    if (this.currentId) {
      this.api.updateLead(this.currentId, payload).subscribe({ next: () => this.onSaved(), error: () => { alert('Erro ao salvar'); this.loading = false; } });
    } else {
      this.api.createLead(payload).subscribe({ next: () => this.onSaved(), error: () => { alert('Erro ao salvar'); this.loading = false; } });
    }
  }

  onSaved() {
    this.loading = false;
    // navigate back to list with saved query param to show success
    this.router.navigate(['/leads'], { queryParams: { saved: '1' } });
  }
}
