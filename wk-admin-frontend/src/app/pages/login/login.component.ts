import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../core/services/auth.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  loginForm!: FormGroup;
  loading = false;
  returnUrl: string = '/dashboard';

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute,
    private toastr: ToastrService
  ) {}

  ngOnInit(): void {
    console.log('🔧 LoginComponent inicializando...');
    
    // Inicializa formulário PRIMEIRO
    this.loginForm = this.formBuilder.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(3)]]
    });
    
    console.log('✅ Formulário criado:', this.loginForm);

    // Redirect if already logged in
    if (this.authService.isAuthenticated) {
      console.log('⚠️ Já autenticado, redirecionando...');
      this.router.navigate(['/dashboard']);
      return;
    }

    // Get return url from route parameters or default to '/dashboard'
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/dashboard';
  }

  get f() {
    return this.loginForm?.controls || {};
  }

  onSubmit(): void {
    if (!this.loginForm || this.loginForm.invalid) {
      this.toastr.error('Por favor, preencha os campos corretamente', 'Erro de Validação');
      return;
    }

    const email = this.loginForm.get('email')?.value;
    const password = this.loginForm.get('password')?.value;
    
    if (!email || !password) {
      this.toastr.error('Email e senha são obrigatórios', 'Erro');
      return;
    }

    this.loading = true;
    console.log('🔐 Iniciando login...', email);
    
    this.authService.login(email, password)
      .subscribe({
        next: (response) => {
          console.log('✅ Login component recebeu resposta:', response);
          
          // Verificar se token foi salvo
          const tokenSalvo = localStorage.getItem('token');
          console.log('🔑 Token após login:', tokenSalvo ? 'OK' : 'FALHOU');
          
          this.loading = false;
          this.toastr.success('Login realizado com sucesso!', 'Bem-vindo');
          
          // Aguardar um pouco mais para garantir que tudo foi salvo
          setTimeout(() => {
            console.log('🚀 Navegando para dashboard...');
            this.router.navigate(['/dashboard']).then(success => {
              console.log('Navegação:', success ? 'sucesso' : 'falhou');
            });
          }, 300);
        },
        error: (error) => {
          console.error('❌ Erro no login component:', error);
          this.loading = false;
          const message = error.error?.message || 'Erro ao fazer login. Tente novamente.';
          this.toastr.error(message, 'Erro de Autenticação');
        }
      });
  }
}
