export interface User {
  id: string
  name: string
  email: string
  company?: string
  phone?: string
  avatar?: string
  role?: string
  roles?: string[]
  created_at?: string
}

export interface Opportunity {
  id: string
  title: string
  value: number
  customer_id?: string
  customer?: {
    id: string
    name: string
    company?: string
  }
  seller_id?: string
  seller?: {
    id: string
    name: string
  } | string
  status: string
  probability?: number
  notes?: string
  created_at: string
  updated_at?: string
}

export interface DashboardStats {
  totalOpportunities: number
  totalValue: number
  openOpportunities: number
  wonOpportunities: number
  avgProbability: number
  recentActivity: Activity[]
}

export interface Activity {
  id: string
  type: string
  title: string
  description?: string
  created_at: string
}
