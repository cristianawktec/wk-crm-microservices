import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup;
  loading = false;
  submitted = false;
  error = '';
  returnUrl = '';

  constructor(
    private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthService
  ) {
    console.log('🔐 [LoginComponent Constructor] CHAMADO');
    // Limpar localStorage completamente ao entrar em login
    localStorage.clear();
    sessionStorage.clear();
    console.log('🔐 [LoginComponent Constructor] localStorage + sessionStorage LIMPOS');
  }

  ngOnInit(): void {
    console.log('🔐 [LoginComponent ngOnInit] CHAMADO - URL:', this.router.url);
    
    this.loginForm = this.formBuilder.group({
      email: ['admin@consultoriawk.com', [Validators.required, Validators.email]],
      password: ['Admin@2025', Validators.required]
    });

    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
    console.log('🔐 [LoginComponent ngOnInit] Form inicializado, returnUrl:', this.returnUrl);
  }

  get f() {
    return this.loginForm.controls;
  }

  onSubmit(): void {
    this.submitted = true;
    this.error = '';

    // Para se o formulário for inválido
    if (this.loginForm.invalid) {
      return;
    }

    this.loading = true;
    this.authService.login(this.f['email'].value, this.f['password'].value)
      .subscribe({
        next: (response) => {
          if (response.token) {
            this.router.navigate([this.returnUrl]);
          } else {
            this.error = 'Credenciais inválidas';
            this.loading = false;
          }
        },
        error: (error) => {
          this.error = error.error?.message || 'Erro ao fazer login. Verifique suas credenciais.';
          this.loading = false;
        }
      });
  }
}
