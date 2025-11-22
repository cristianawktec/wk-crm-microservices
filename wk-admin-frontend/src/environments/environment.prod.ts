export const environment = {
  production: true,
  // API interna via gateway dentro do cluster/docker
  apiUrl: 'http://wk-gateway:3000',
  apiUrlLocal: 'http://wk-gateway:3000',
  // URL pública externa (se necessário para chamadas fora do cluster)
  publicApiUrl: 'https://api.consultoriawk.com/api',
  // Em produção nunca usar mock
  useMockDashboard: false,
  // Chaves de dev mantidas para compatibilidade de build (não usadas em prod)
  devUserEmail: '',
  devUserPassword: '',
  adminUrl: 'https://consultoriawk.com/admin'
};
