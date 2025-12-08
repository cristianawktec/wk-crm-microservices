import { Component } from '@angular/core';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html'
})
export class HeaderComponent {
  currentUser$ = this.authService.currentUser;

  constructor(private authService: AuthService) {}

  logout(): void {
    this.authService.logout();
  }
}
