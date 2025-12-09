import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ApiService } from '../../services/api.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-customer-form',
  templateUrl: './customer-form.component.html',
  styleUrls: ['./customer-form.component.css']
})
export class CustomerFormComponent implements OnInit {
  form: FormGroup;
  loading = false;
  isEdit = false;
  private currentId: string | null = null;

  constructor(private route: ActivatedRoute, private api: ApiService, private router: Router, private fb: FormBuilder) {
    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(120)]],
      email: ['', [Validators.email, Validators.maxLength(180)]],
      phone: ['', [Validators.maxLength(40)]],
      company: ['', [Validators.maxLength(120)]],
      address: ['', [Validators.maxLength(255)]],
      city: ['', [Validators.maxLength(100)]],
      state: ['', [Validators.maxLength(2)]],
      zipcode: ['', [Validators.maxLength(10)]],
      notes: ['', [Validators.maxLength(500)]]
    });
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      // backend uses UUID strings for customer ids, accept strings here
      this.isEdit = true;
      this.currentId = id;
      this.loading = true;
      console.log('[CustomerForm] loading customer', this.currentId);
      this.api.getCustomer(this.currentId).subscribe({ next: (res: any) => {
        const data = res?.data || res || {};
        this.form.patchValue({
          name: data.name || '',
          email: data.email || '',
          phone: data.phone || '',
          company: data.company || '',
          address: data.address || '',
          city: data.city || '',
          state: data.state || '',
          zipcode: data.zipcode || '',
          notes: data.notes || ''
        });
        this.loading = false;
      }, error: () => { this.loading = false; alert('Erro ao carregar cliente'); } });
    }
  }

  save() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.loading = true;
    const payload = this.form.value;
    const op = this.isEdit ? this.api.updateCustomer(this.currentId as string, payload) : this.api.createCustomer(payload);
    op.subscribe({ next: () => { this.loading = false; this.router.navigate(['/customers'], { queryParams: { saved: '1' } }); }, error: () => { this.loading = false; alert('Erro ao salvar'); } });
  }

  cancel() { this.router.navigate(['/customers']); }

  // convenience getters for template
  get name() { return this.form.get('name'); }
  get email() { return this.form.get('email'); }
  get phone() { return this.form.get('phone'); }
  get company() { return this.form.get('company'); }
  get address() { return this.form.get('address'); }
  get city() { return this.form.get('city'); }
  get state() { return this.form.get('state'); }
  get zipcode() { return this.form.get('zipcode'); }
  get notes() { return this.form.get('notes'); }
}
