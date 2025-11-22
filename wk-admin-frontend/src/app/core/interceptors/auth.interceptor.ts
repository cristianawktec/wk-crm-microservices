import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = this.authService.token;

    // Garantir sempre Accept JSON para respostas estruturadas
    const baseHeaders: any = { 
      Accept: 'application/json',
      'Content-Type': 'application/json'
    };
    
    if (token) {
      baseHeaders.Authorization = `Bearer ${token}`;
      console.log('Token adicionado à requisição:', request.url);
    } else {
      console.warn('Nenhum token encontrado para:', request.url);
    }
    
    request = request.clone({ 
      setHeaders: baseHeaders,
      withCredentials: false // Não usar cookies, apenas Bearer token
    });

    console.log('Requisição interceptada:', request.url, 'Headers:', request.headers.keys());

    return next.handle(request).pipe(
      catchError((error: HttpErrorResponse) => {
        console.error('Erro na requisição:', error.url, 'Status:', error.status, 'Mensagem:', error.message);
        
        if (error.status === 401) {
          // Token inválido ou expirado
          console.log('Token inválido, redirecionando para login');
          localStorage.removeItem('token');
          localStorage.removeItem('currentUser');
          this.router.navigate(['/login']);
        }
        
        if (error.status === 0) {
          console.error('Erro de CORS ou rede - API não alcançável');
        }
        
        return throwError(() => error);
      })
    );
  }
}
