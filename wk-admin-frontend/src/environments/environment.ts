export const environment = {
  production: false,
  // Usaremos a API Laravel dentro do Docker na porta 8000 diretamente
  apiUrl: 'http://localhost:8000/api',
  apiUrlLocal: 'http://localhost:8000/api',
  // URL do dashboard antigo (Blade/AdminLTE) para redirecionar pós-login
  adminUrl: 'http://localhost:8000/admin',
  // Credenciais de desenvolvimento para auto-login REAL (NÃO usar em produção)
  devUserEmail: 'admin@wkcrm.dev',
  devUserPassword: 'password',
  // Controla se o dashboard usa dados mock quando API falhar
  useMockDashboard: true
};
