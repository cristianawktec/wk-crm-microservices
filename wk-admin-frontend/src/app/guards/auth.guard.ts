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
    if (this.authService.isAuthenticated()) {
      // Verifica se o token ainda é válido no backend
      this.authService.verifyToken().subscribe({
        next: (response) => {
          // Token válido, continua
        },
        error: (error) => {
          // Token inválido ou expirado, força logout
          this.authService.logout();
          this.router.navigate(['/login']);
        }
      });
      return true;
    }

    // Não autenticado, redireciona para login
    this.router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
    return false;
  }
}
