import { Component, OnInit, HostListener } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService, User } from '../core/services/auth.service';
import { ThemeService } from '../core/services/theme.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-layout',
  templateUrl: './layout.component.html',
  styleUrls: ['./layout.component.scss']
})
export class LayoutComponent implements OnInit {
  currentUser: User | null = null;
  sidenavOpened = true;
  isDarkTheme = false;
  isMobile = false;

  menuItems = [
    { icon: 'dashboard', label: 'Dashboard', route: '/dashboard' },
    { icon: 'people', label: 'Clientes', route: '/clientes' },
    { icon: 'contacts', label: 'Leads', route: '/leads' },
    { icon: 'business_center', label: 'Oportunidades', route: '/oportunidades' }
  ];

  constructor(
    private authService: AuthService,
    private router: Router,
    private toastr: ToastrService,
    private themeService: ThemeService
  ) {}

  ngOnInit(): void {
    this.authService.currentUser.subscribe(user => {
      this.currentUser = user;
    });
    this.checkScreenSize();
    this.initThemeSync();
  }

  @HostListener('window:resize')
  onResize() {
    this.checkScreenSize();
  }

  checkScreenSize(): void {
    this.isMobile = window.innerWidth < 768;
    // Mobile: inicia fechado; Desktop: aberto
    this.sidenavOpened = !this.isMobile;
    console.log('Layout state (flex shell):', { isMobile: this.isMobile, sidenavOpened: this.sidenavOpened });
    this.applySidebarBodyClass();
  }

  initThemeSync(): void {
    // Initialize from service current state
    const saved = localStorage.getItem('wkcrm_theme');
    this.isDarkTheme = saved === 'dark';
    if (this.isDarkTheme) {
      this.themeService.setTheme('dark');
    } else {
      this.themeService.setTheme('light');
    }
    this.themeService.themeChanges.subscribe(theme => {
      this.isDarkTheme = theme === 'dark';
    });
  }

  toggleTheme(): void {
    this.themeService.toggleTheme();
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        this.toastr.success('Logout realizado com sucesso', 'Até logo');
        this.router.navigate(['/login']);
      },
      error: () => {
        this.toastr.error('Erro ao fazer logout', 'Erro');
      }
    });
  }

  toggleSidenav(): void {
    this.sidenavOpened = !this.sidenavOpened;
    console.log('Toggled sidenav (flex shell):', { sidenavOpened: this.sidenavOpened });
    this.applySidebarBodyClass();
  }

  navigate(route: string): void {
    console.log('Navegando para:', route);
    this.router.navigateByUrl(route).then(() => {
      if (this.isMobile) {
        this.sidenavOpened = false;
        this.applySidebarBodyClass();
      }
    });
  }

  // Body classes removed; flex shell handles layout entirely.
  private applySidebarBodyClass(): void {}
}
