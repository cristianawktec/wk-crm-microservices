import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';

export interface User {
  id: string;
  name: string;
  email: string;
}

export interface LoginResponse {
  success?: boolean;
  message?: string;
  token?: string;
  user?: {
    id: string;
    name: string;
    email: string;
    role?: string;
    roles?: string[];
  };
  data?: {
    id: string;
    name: string;
    email: string;
    role?: string;
    roles?: string[];
  };
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private currentUserSubject: BehaviorSubject<User | null>;
  public currentUser: Observable<User | null>;

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    const savedUser = localStorage.getItem('currentUser');
    this.currentUserSubject = new BehaviorSubject<User | null>(
      savedUser ? JSON.parse(savedUser) : null
    );
    this.currentUser = this.currentUserSubject.asObservable();
  }

  public get currentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  public get token(): string | null {
    return localStorage.getItem('token');
  }

  login(email: string, password: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/auth/login`, { email, password })
      .pipe(
        tap(response => {
          const token = response.token;
          const userData = response.data || response.user;

          if (token && userData) {
            localStorage.setItem('token', token);
            const user: User = {
              id: userData.id,
              name: userData.name,
              email: userData.email
            };
            localStorage.setItem('currentUser', JSON.stringify(user));
            this.currentUserSubject.next(user);
          }
        })
      );
  }

  logout(): void {
    const token = this.token;
    if (token) {
      this.http.post(`${this.apiUrl}/auth/logout`, {}).subscribe({
        complete: () => {
          this.clearAuthData();
        },
        error: () => {
          this.clearAuthData();
        }
      });
    } else {
      this.clearAuthData();
    }
  }

  private clearAuthData(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('currentUser');
    this.currentUserSubject.next(null);
    this.router.navigate(['/login']);
  }

  isAuthenticated(): boolean {
    const token = this.token;
    console.log('🔐 AuthService.isAuthenticated() - token exists:', !!token);
    if (!token) {
      return false;
    }
    return true;
  }

  verifyToken(): Observable<boolean> {
    return this.http.get<any>(`${this.apiUrl}/auth/me`)
      .pipe(
        tap(response => {
          if (!response || response.error) {
            this.clearAuthData();
          }
        })
      );
  }
}
