export interface Lead {
  id: number;
  nome: string;
  origem?: string; // ex: campanha, indicação
  status?: string; // ex: novo, em_contato, qualificado
  email?: string;
  telefone?: string;
  created_at?: string;
  updated_at?: string;
}
