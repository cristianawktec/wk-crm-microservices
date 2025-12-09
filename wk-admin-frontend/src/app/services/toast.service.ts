import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

export interface Toast {
  id: number;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number;
}

@Injectable({ providedIn: 'root' })
export class ToastService {
  private toastSubject = new Subject<Toast>();
  public toasts$ = this.toastSubject.asObservable();
  private idCounter = 0;

  success(title: string, message: string, duration = 3000) {
    this.show('success', title, message, duration);
  }

  error(title: string, message: string, duration = 5000) {
    this.show('error', title, message, duration);
  }

  warning(title: string, message: string, duration = 4000) {
    this.show('warning', title, message, duration);
  }

  info(title: string, message: string, duration = 3000) {
    this.show('info', title, message, duration);
  }

  private show(type: Toast['type'], title: string, message: string, duration: number) {
    const toast: Toast = {
      id: ++this.idCounter,
      type,
      title,
      message,
      duration
    };
    this.toastSubject.next(toast);
  }
}
