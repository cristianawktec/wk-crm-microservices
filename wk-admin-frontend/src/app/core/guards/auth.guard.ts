import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    const token = this.authService.token;
    const isAuth = this.authService.isAuthenticated;
    
    console.log('🛡️ AuthGuard verificando:', {
      url: state.url,
      token: token ? token.substring(0, 20) + '...' : 'null',
      isAuthenticated: isAuth
    });
    
    if (isAuth) {
      console.log('✅ AuthGuard: Acesso permitido');
      return true;
    }

    console.log('❌ AuthGuard: Bloqueado - redirecionando para login');
    // Não autenticado, redirecionar para login
    this.router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
    return false;
  }
}
