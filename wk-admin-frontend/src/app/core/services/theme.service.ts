import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

export type AppTheme = 'light' | 'dark';

interface ThemeDefinition {
  name: AppTheme;
  vars: Record<string, string>;
}

@Injectable({ providedIn: 'root' })
export class ThemeService {
  private currentTheme: AppTheme = 'light';
  private theme$ = new BehaviorSubject<AppTheme>(this.currentTheme);

  // Base definitions (can be extended later)
  private themes: ThemeDefinition[] = [
    {
      name: 'light',
      vars: {
        '--app-bg': '#ecf0f5',
        '--card-bg': '#ffffff',
        '--text-primary': '#444444',
        '--text-secondary': '#666666',
        '--text-muted': '#999999',
        '--border-color': '#d2d6de',
        '--hover-bg': '#f4f4f4',
        '--color-primary': '#3c8dbc',
        '--color-accent': '#5c6bc0',
        '--color-success': '#28a745',
        '--color-warning': '#f39c12',
        '--color-danger': '#dc3545'
      }
    },
    {
      name: 'dark',
      vars: {
        '--app-bg': '#111218',
        '--card-bg': '#1a1b21',
        '--text-primary': 'rgba(255,255,255,0.87)',
        '--text-secondary': 'rgba(255,255,255,0.7)',
        '--text-muted': 'rgba(255,255,255,0.54)',
        '--border-color': 'rgba(255,255,255,0.12)',
        '--hover-bg': 'rgba(255,255,255,0.04)',
        '--color-primary': '#5fa2cc',
        '--color-accent': '#7986cb',
        '--color-success': '#2ecc71',
        '--color-warning': '#f7b84e',
        '--color-danger': '#e66a5a'
      }
    }
  ];

  constructor() {
    const saved = localStorage.getItem('wkcrm_theme') as AppTheme | null;
    if (saved && (saved === 'light' || saved === 'dark')) {
      this.currentTheme = saved;
    }
    this.applyTheme(this.currentTheme);
  }

  get themeChanges() {
    return this.theme$.asObservable();
  }

  toggleTheme(): void {
    this.setTheme(this.currentTheme === 'light' ? 'dark' : 'light');
  }

  setTheme(theme: AppTheme): void {
    if (this.currentTheme === theme) return;
    this.currentTheme = theme;
    localStorage.setItem('wkcrm_theme', theme);
    this.applyTheme(theme);
    this.theme$.next(theme);
  }

  private applyTheme(theme: AppTheme): void {
    const def = this.themes.find(t => t.name === theme);
    if (!def) return;

    const root = document.documentElement; // :root
    Object.entries(def.vars).forEach(([key, value]) => {
      root.style.setProperty(key, value);
    });

    // Body class for existing SCSS rules
    document.body.classList.remove('theme-light', 'theme-dark');
    document.body.classList.add(`theme-${theme}`);
  }
}
