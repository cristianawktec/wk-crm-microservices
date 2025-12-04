import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private base = environment.apiUrl || '/api';

  constructor(private http: HttpClient) {}

  getDashboard(params: any = {}): Observable<any> {
    const qp = new URLSearchParams();
    Object.keys(params || {}).forEach(k => {
      if (params[k] !== undefined && params[k] !== null && params[k] !== '') qp.set(k, params[k]);
    });
    const url = `${this.base}/dashboard` + (qp.toString() ? `?${qp.toString()}` : '');
    return this.http.get(url).pipe(catchError(err => {
      console.warn('Dashboard API failed', err);
      return throwError(() => err);
    }));
  }

  getCustomers(): Observable<any> {
    return this.http.get(`${this.base}/customers`).pipe(catchError(err => {
      console.warn('Customers API failed', err);
      return throwError(() => err);
    }));
  }

  getCustomer(id: string): Observable<any> {
    console.log('[ApiService] getCustomer()', id);
    return this.http.get(`${this.base}/customers/${id}`).pipe(
      tap(() => console.log('[ApiService] getCustomer() request sent for', id)),
      catchError(err => {
        console.warn('getCustomer failed', err);
        return throwError(() => err);
      })
    );
  }

  createCustomer(payload: any): Observable<any> {
    return this.http.post(`${this.base}/customers`, payload).pipe(catchError(err => {
      console.warn('createCustomer failed', err);
      return throwError(() => err);
    }));
  }

  updateCustomer(id: string, payload: any): Observable<any> {
    return this.http.put(`${this.base}/customers/${id}`, payload).pipe(catchError(err => {
      console.warn('updateCustomer failed', err);
      return throwError(() => err);
    }));
  }

  deleteCustomer(id: string): Observable<any> {
    return this.http.delete(`${this.base}/customers/${id}`).pipe(catchError(err => {
      console.warn('deleteCustomer failed', err);
      return throwError(() => err);
    }));
  }

  /* Leads */
  getLeads(): Observable<any> {
    return this.http.get(`${this.base}/leads`).pipe(catchError(err => {
      console.warn('Leads API failed', err);
      return throwError(() => err);
    }));
  }

  getLead(id: string): Observable<any> {
    console.log('[ApiService] getLead()', id);
    return this.http.get(`${this.base}/leads/${id}`).pipe(
      tap(() => console.log('[ApiService] getLead() request sent for', id)),
      catchError(err => {
        console.warn('getLead failed', err);
        return throwError(() => err);
      })
    );
  }

  createLead(payload: any): Observable<any> {
    return this.http.post(`${this.base}/leads`, payload).pipe(catchError(err => {
      console.warn('createLead failed', err);
      return throwError(() => err);
    }));
  }

  updateLead(id: string, payload: any): Observable<any> {
    return this.http.put(`${this.base}/leads/${id}`, payload).pipe(catchError(err => {
      console.warn('updateLead failed', err);
      return throwError(() => err);
    }));
  }

  deleteLead(id: string): Observable<any> {
    return this.http.delete(`${this.base}/leads/${id}`).pipe(catchError(err => {
      console.warn('deleteLead failed', err);
      return throwError(() => err);
    }));
  }

  // metadata for comboboxes
  getLeadSources(): Observable<any> {
    return this.http.get(`${this.base}/leads/sources`).pipe(catchError(err => {
      console.warn('getLeadSources failed', err);
      return of([]);
    }));
  }

  /* Sellers */
  getSellers(): Observable<any> {
    return this.http.get(`${this.base}/sellers`).pipe(catchError(err => {
      console.warn('Sellers API failed', err);
      return throwError(() => err);
    }));
  }

  getSeller(id: string): Observable<any> {
    return this.http.get(`${this.base}/sellers/${id}`).pipe(catchError(err => {
      console.warn('getSeller failed', err);
      return throwError(() => err);
    }));
  }

  createSeller(payload: any): Observable<any> {
    return this.http.post(`${this.base}/sellers`, payload).pipe(catchError(err => {
      console.warn('createSeller failed', err);
      return throwError(() => err);
    }));
  }

  updateSeller(id: string, payload: any): Observable<any> {
    return this.http.put(`${this.base}/sellers/${id}`, payload).pipe(catchError(err => {
      console.warn('updateSeller failed', err);
      return throwError(() => err);
    }));
  }

  deleteSeller(id: string): Observable<any> {
    return this.http.delete(`${this.base}/sellers/${id}`).pipe(catchError(err => {
      console.warn('deleteSeller failed', err);
      return throwError(() => err);
    }));
  }

  getSellerRoles(): Observable<any> {
    return this.http.get(`${this.base}/sellers/roles`).pipe(catchError(err => {
      console.warn('getSellerRoles failed', err);
      return of([]);
    }));
  }

  /* Opportunities */
  getOpportunities(params: any = {}): Observable<any> {
    const qp = new URLSearchParams();
    Object.keys(params || {}).forEach(k => qp.set(k, params[k]));
    const url = `${this.base}/opportunities` + (qp.toString() ? `?${qp.toString()}` : '');
    return this.http.get(url).pipe(catchError(err => {
      console.warn('getOpportunities failed', err);
      return throwError(() => err);
    }));
  }

  getOpportunity(id: string): Observable<any> {
    return this.http.get(`${this.base}/opportunities/${id}`).pipe(catchError(err => {
      console.warn('getOpportunity failed', err);
      return throwError(() => err);
    }));
  }

  createOpportunity(payload: any): Observable<any> {
    return this.http.post(`${this.base}/opportunities`, payload).pipe(catchError(err => {
      console.warn('createOpportunity failed', err);
      return throwError(() => err);
    }));
  }

  updateOpportunity(id: string, payload: any): Observable<any> {
    return this.http.put(`${this.base}/opportunities/${id}`, payload).pipe(catchError(err => {
      console.warn('updateOpportunity failed', err);
      return throwError(() => err);
    }));
  }

  deleteOpportunity(id: string): Observable<any> {
    return this.http.delete(`${this.base}/opportunities/${id}`).pipe(catchError(err => {
      console.warn('deleteOpportunity failed', err);
      return throwError(() => err);
    }));
  }
}
