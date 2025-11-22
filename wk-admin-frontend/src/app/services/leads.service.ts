import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { Lead } from '../models/lead';

@Injectable({ providedIn: 'root' })
export class LeadsService {
  private baseUrl = `${environment.apiUrl}/leads`;

  constructor(private http: HttpClient) {}

  list(): Observable<Lead[]> {
    console.log('[LeadsService] GET', this.baseUrl);
    return this.http.get<any>(this.baseUrl).pipe(
      map(response => {
        console.log('[LeadsService] Response:', response);
        return response.data || response;
      })
    );
  }

  get(id: number): Observable<Lead> {
    return this.http.get<Lead>(`${this.baseUrl}/${id}`);
  }

  create(data: Partial<Lead>): Observable<Lead> {
    return this.http.post<Lead>(this.baseUrl, data);
  }

  update(id: number, data: Partial<Lead>): Observable<Lead> {
    return this.http.put<Lead>(`${this.baseUrl}/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
