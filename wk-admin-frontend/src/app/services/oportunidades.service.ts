import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { Oportunidade } from '../models/oportunidade';

@Injectable({ providedIn: 'root' })
export class OportunidadesService {
  private baseUrl = `${environment.apiUrl}/oportunidades`;

  constructor(private http: HttpClient) {}

  list(): Observable<Oportunidade[]> {
    console.log('[OportunidadesService] GET', this.baseUrl);
    return this.http.get<any>(this.baseUrl).pipe(
      map(response => {
        console.log('[OportunidadesService] Response:', response);
        return response.data || response;
      })
    );
  }

  get(id: number): Observable<Oportunidade> {
    return this.http.get<Oportunidade>(`${this.baseUrl}/${id}`);
  }

  create(data: Partial<Oportunidade>): Observable<Oportunidade> {
    return this.http.post<Oportunidade>(this.baseUrl, data);
  }

  update(id: number, data: Partial<Oportunidade>): Observable<Oportunidade> {
    return this.http.put<Oportunidade>(`${this.baseUrl}/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
