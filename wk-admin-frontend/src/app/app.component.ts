import { Component, OnInit } from '@angular/core';
import { AuthService } from './core/services/auth.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements OnInit {
  title = 'wk-admin-temp';

  constructor(private auth: AuthService) {}

  ngOnInit(): void {
    // Apenas referencia o serviço para disparar auto-login se necessário
    if (this.auth.isAuthenticated) {
      console.log('🔐 Usuário já autenticado (DEV auto).');
    }
  }
}
