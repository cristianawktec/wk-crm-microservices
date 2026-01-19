import { Component, OnInit } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthService } from './app/services/auth.service';

@Component({
  selector: 'app-root',
  template: `
    <app-toast-container></app-toast-container>
    <ng-container *ngIf="!isLoginPage">
      <app-header></app-header>
      <div class="wrapper">
        <app-sidebar></app-sidebar>
        <div class="content-wrapper">
          <router-outlet></router-outlet>
        </div>
      </div>
    </ng-container>
    <ng-container *ngIf="isLoginPage">
      <router-outlet></router-outlet>
    </ng-container>
  `
})
export class AppComponent implements OnInit {
  isLoginPage = false;

  constructor(private router: Router, private authService: AuthService) {
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe((event: NavigationEnd) => {
        this.isLoginPage = event.url.includes('login');
        console.log('🔐 Navigation event - URL:', event.url, 'isLoginPage:', this.isLoginPage);
      });
  }

  ngOnInit(): void {
    console.log('🔐 AppComponent.ngOnInit() - não faz nada aqui');
    // Não fazer nada aqui - deixar rotas e AuthGuard controlarem
  }
}
