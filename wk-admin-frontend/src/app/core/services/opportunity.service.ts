import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Opportunity {
  id?: string;
  title: string;
  description?: string;
  amount: number;
  status: 'aberta' | 'em_negociacao' | 'proposta_enviada' | 'fechada_ganha' | 'fechada_perdida';
  probability?: number;
  expected_close_date?: string;
  cliente_id: string;
  lead_id?: string;
  created_at?: string;
  updated_at?: string;
}

@Injectable({
  providedIn: 'root'
})
export class OpportunityService {
  private apiUrl = `${environment.apiUrl}/oportunidades`;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Opportunity[]> {
    return this.http.get<Opportunity[]>(this.apiUrl);
  }

  getById(id: string): Observable<Opportunity> {
    return this.http.get<Opportunity>(`${this.apiUrl}/${id}`);
  }

  create(opportunity: Opportunity): Observable<Opportunity> {
    return this.http.post<Opportunity>(this.apiUrl, opportunity);
  }

  update(id: string, opportunity: Opportunity): Observable<Opportunity> {
    return this.http.put<Opportunity>(`${this.apiUrl}/${id}`, opportunity);
  }

  delete(id: string): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }
}
