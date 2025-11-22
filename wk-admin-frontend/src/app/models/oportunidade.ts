export interface Oportunidade {
  id: number;
  titulo: string;
  valor?: number;
  etapa?: string; // ex: proposta, negociacao, fechado
  cliente_id?: number;
  created_at?: string;
  updated_at?: string;
}
