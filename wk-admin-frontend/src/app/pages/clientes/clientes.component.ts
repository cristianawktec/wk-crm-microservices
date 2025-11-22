import { Component, OnInit } from '@angular/core';
import { ClientesService } from '../../services/clientes.service';
import { Cliente } from '../../models/cliente';

interface Column {
  key: keyof Cliente | 'actions';
  label: string;
}

@Component({
  selector: 'app-clientes',
  templateUrl: './clientes.component.html',
  styleUrl: './clientes.component.scss'
})
export class ClientesComponent implements OnInit {
  loading = false;
  error: string | null = null;
  clientes: Cliente[] = [];
  creating = false;
  newCliente: Partial<Cliente> = { nome: '', email: '', telefone: '' };
  displayedColumns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'nome', label: 'Nome' },
    { key: 'email', label: 'E-mail' },
    { key: 'telefone', label: 'Telefone' },
    { key: 'actions', label: 'Ações' }
  ];

  constructor(private clientesService: ClientesService) {}

  ngOnInit(): void {
    console.log('[ClientesComponent] Versão 2 carregada');
    this.loadClientes();
  }

  loadClientes(): void {
    this.loading = true;
    this.error = null;
    this.clientesService.list().subscribe({
      next: data => {
        this.clientes = data;
        this.loading = false;
      },
      error: err => {
        this.error = 'Falha ao carregar clientes';
        console.error('Erro clientes:', err);
        this.loading = false;
      }
    });
  }

  toggleCreate(): void {
    this.creating = !this.creating;
    if (this.creating) {
      this.newCliente = { nome: '', email: '', telefone: '' };
    }
  }

  salvarNovo(): void {
    if (!this.newCliente.nome) {
      alert('Nome é obrigatório');
      return;
    }
    const payload = {
      nome: this.newCliente.nome,
      email: this.newCliente.email,
      telefone: this.newCliente.telefone
    };
    console.log('[ClientesComponent] CREATE', payload);
    this.clientesService.create(payload).subscribe({
      next: c => {
        this.clientes.push(c);
        this.creating = false;
      },
      error: err => {
        alert('Erro ao criar cliente');
        console.error(err);
      }
    });
  }

  editarCliente(cliente: Cliente): void {
    console.log('Editar cliente:', cliente);
    alert('Funcionalidade de edição em desenvolvimento');
  }

  deletarCliente(cliente: Cliente): void {
    if (confirm(`Deseja realmente deletar o cliente ${cliente.nome}?`)) {
      this.clientesService.delete(cliente.id).subscribe({
        next: () => {
          this.clientes = this.clientes.filter(c => c.id !== cliente.id);
          alert('Cliente deletado com sucesso');
        },
        error: err => {
          alert('Erro ao deletar cliente');
          console.error(err);
        }
      });
    }
  }
}
