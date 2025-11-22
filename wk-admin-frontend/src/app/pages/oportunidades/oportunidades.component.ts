import { Component, OnInit } from '@angular/core';
import { OportunidadesService } from '../../services/oportunidades.service';
import { Oportunidade } from '../../models/oportunidade';

interface Column {
  key: keyof Oportunidade | 'actions';
  label: string;
}

@Component({
  selector: 'app-oportunidades',
  templateUrl: './oportunidades.component.html',
  styleUrl: './oportunidades.component.scss'
})
export class OportunidadesComponent implements OnInit {
  loading = false;
  error: string | null = null;
  oportunidades: Oportunidade[] = [];
  displayedColumns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'titulo', label: 'Título' },
    { key: 'valor', label: 'Valor' },
    { key: 'etapa', label: 'Etapa' },
    { key: 'actions', label: 'Ações' }
  ];

  constructor(private oportunidadesService: OportunidadesService) {}

  ngOnInit(): void {
    console.log('[OportunidadesComponent] Versão 2 carregada');
    this.loadOportunidades();
  }

  loadOportunidades(): void {
    this.loading = true;
    this.error = null;
    this.oportunidadesService.list().subscribe({
      next: data => {
        this.oportunidades = data;
        this.loading = false;
      },
      error: err => {
        this.error = 'Falha ao carregar oportunidades';
        console.error('Erro oportunidades:', err);
        this.loading = false;
      }
    });
  }
}
