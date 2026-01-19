import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
  constructor(
    private router: Router,
    private authService: AuthService
  ) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    const url = state.url;
    console.log('🔐 [AuthGuard.canActivate] CHAMADO');
    console.log('🔐 [AuthGuard] state.url =', url);
    console.log('🔐 [AuthGuard] router.url =', this.router.url);
    console.log('🔐 [AuthGuard] Contains /login?', url.includes('/login'));
    
    const isLoginRoute = url.includes('/login');
    
    if (isLoginRoute) {
      console.log('🔐 [AuthGuard] ROTA DE LOGIN - PERMITINDO ACESSO SEM AUTENTICAÇÃO');
      return true;
    }
    
    // Para rotas protegidas, verificar autenticação
    const authenticated = this.authService.isAuthenticated();
    console.log('🔐 [AuthGuard] Rota protegida - isAuthenticated():', authenticated);
    
    if (authenticated) {
      console.log('🔐 [AuthGuard] Usuário autenticado - permitindo');
      return true;
    }

    console.log('🔐 [AuthGuard] NÃO autenticado - redirecionando para /login');
    this.router.navigate(['/login'], { queryParams: { returnUrl: url } });
    return false;
  }
}
