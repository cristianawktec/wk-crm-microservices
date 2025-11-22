import { Component, OnInit } from '@angular/core';
import { LeadsService } from '../../services/leads.service';
import { Lead } from '../../models/lead';

interface Column {
  key: keyof Lead | 'actions';
  label: string;
}

@Component({
  selector: 'app-leads',
  templateUrl: './leads.component.html',
  styleUrl: './leads.component.scss'
})
export class LeadsComponent implements OnInit {
  loading = false;
  error: string | null = null;
  leads: Lead[] = [];
  displayedColumns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'nome', label: 'Nome' },
    { key: 'status', label: 'Status' },
    { key: 'origem', label: 'Origem' },
    { key: 'actions', label: 'Ações' }
  ];

  constructor(private leadsService: LeadsService) {}

  ngOnInit(): void {
    console.log('[LeadsComponent] Versão 2 carregada');
    this.loadLeads();
  }

  loadLeads(): void {
    this.loading = true;
    this.error = null;
    this.leadsService.list().subscribe({
      next: data => {
        this.leads = data;
        this.loading = false;
      },
      error: err => {
        this.error = 'Falha ao carregar leads';
        console.error('Erro leads:', err);
        this.loading = false;
      }
    });
  }
}
