import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap, catchError, throwError, of } from 'rxjs';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface LoginResponse {
  user: User;
  token: string;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject: BehaviorSubject<User | null>;
  public currentUser: Observable<User | null>;
  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    const storedUser = localStorage.getItem('currentUser');
    this.currentUserSubject = new BehaviorSubject<User | null>(
      storedUser ? JSON.parse(storedUser) : null
    );
    this.currentUser = this.currentUserSubject.asObservable();
    // Auto login em desenvolvimento para evitar tela em branco
    this.ensureDevAutoLogin();
  }

  public get currentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  public get token(): string | null {
    const token = localStorage.getItem('token');
    console.log('🔑 Token atual:', token ? token.substring(0, 20) + '...' : 'null');
    return token;
  }

  public get isAuthenticated(): boolean {
    const hasToken = !!this.token;
    console.log('🔐 isAuthenticated:', hasToken);
    return hasToken;
  }

  login(email: string, password: string): Observable<LoginResponse> {
    console.log('🔐 Login iniciado - Email:', email, 'API URL:', this.apiUrl);
    
    // MODO DESENVOLVIMENTO: Login mock direto (remova em produção)
    if (email && password.length >= 1) {
      console.log('🔧 MODO DEV: Usando mock login direto');
      const mockResponse: LoginResponse = {
        user: { id: 1, name: 'Admin WK CRM', email: email },
        token: 'dev-token-' + Date.now(),
        message: 'Login mock desenvolvimento'
      };
      
      // Salvar ANTES de retornar observable
      localStorage.setItem('token', mockResponse.token);
      localStorage.setItem('currentUser', JSON.stringify(mockResponse.user));
      this.currentUserSubject.next(mockResponse.user);
      
      // Verificar se salvou
      const verificar = localStorage.getItem('token');
      console.log('✅ Token salvo:', verificar ? 'SIM' : 'NÃO');
      console.log('✅ Mock login completo - User:', mockResponse.user.name);
      
      return of(mockResponse);
    }
    
    // Código real (comentado temporariamente para testes)
    return this.http.post<LoginResponse>(`${this.apiUrl}/login`, { email, password })
      .pipe(
        tap(response => {
          console.log('✅ Login API bem-sucedido:', response);
          localStorage.setItem('token', response.token);
          localStorage.setItem('currentUser', JSON.stringify(response.user));
          this.currentUserSubject.next(response.user);
        }),
        catchError(error => {
          console.error('❌ API login failed:', error);
          return throwError(() => error);
        })
      );
  }

  register(name: string, email: string, password: string, password_confirmation: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/register`, { 
      name, 
      email, 
      password, 
      password_confirmation 
    }).pipe(
      tap(response => {
        localStorage.setItem('token', response.token);
        localStorage.setItem('currentUser', JSON.stringify(response.user));
        this.currentUserSubject.next(response.user);
      })
    );
  }

  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {})
      .pipe(
        tap(() => {
          this.clearAuth();
        })
      );
  }

  private clearAuth(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('currentUser');
    this.currentUserSubject.next(null);
    this.router.navigate(['/login']);
  }

  refreshUserData(): Observable<{ user: User }> {
    return this.http.get<{ user: User }>(`${this.apiUrl}/user`)
      .pipe(
        tap(response => {
          localStorage.setItem('currentUser', JSON.stringify(response.user));
          this.currentUserSubject.next(response.user);
        })
      );
  }

  /**
   * Auto-login em ambiente de desenvolvimento se não houver token.
   * Evita experiência de página vazia ao abrir a aplicação local.
   */
  private ensureDevAutoLogin(): void {
    if (environment.production) return;
    const hasToken = !!localStorage.getItem('token');
    if (hasToken) return;

    // Tenta login REAL com credenciais de dev configuradas
    if (environment.devUserEmail && environment.devUserPassword) {
      console.log('🔧 Tentando auto-login real DEV...');
      this.http.post<LoginResponse>(`${environment.apiUrl}/login`, {
        email: environment.devUserEmail,
        password: environment.devUserPassword
      }).pipe(
        tap(res => {
          localStorage.setItem('token', res.token);
          localStorage.setItem('currentUser', JSON.stringify(res.user));
          this.currentUserSubject.next(res.user);
          console.log('✅ Auto-login real DEV bem-sucedido');
        }),
        catchError(err => {
          console.warn('⚠️ Auto-login real DEV falhou, usando token mock. Status:', err.status);
          const mockResponse: LoginResponse = {
            user: { id: 1, name: 'Dev Admin (mock)', email: 'dev@local' },
            token: 'auto-dev-token-' + Date.now(),
            message: 'Auto login mock desenvolvimento'
          };
          localStorage.setItem('token', mockResponse.token);
          localStorage.setItem('currentUser', JSON.stringify(mockResponse.user));
          this.currentUserSubject.next(mockResponse.user);
          return of(mockResponse);
        })
      ).subscribe();
    } else {
      console.warn('⚠️ Credenciais de dev não configuradas; usando mock token.');
      const mockResponse: LoginResponse = {
        user: { id: 1, name: 'Dev Admin (mock)', email: 'dev@local' },
        token: 'auto-dev-token-' + Date.now(),
        message: 'Auto login mock desenvolvimento'
      };
      localStorage.setItem('token', mockResponse.token);
      localStorage.setItem('currentUser', JSON.stringify(mockResponse.user));
      this.currentUserSubject.next(mockResponse.user);
    }
  }
}
