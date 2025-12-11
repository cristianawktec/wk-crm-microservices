import axios, { type AxiosInstance } from 'axios'

const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8001',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 30000
})

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default apiClient

// API Methods
export const api = {
  // Dashboard
  getDashboardStats: async () => {
    const response = await apiClient.get('/api/dashboard/customer-stats')
    // Map response to expected format
    const { data } = response.data
    return {
      data: {
        totalOpportunities: data.totalOpportunities,
        totalValue: data.totalValue,
        openOpportunities: data.openOpportunities,
        wonOpportunities: data.openOpportunities - (data.openOpportunities || 0), // fallback
        avgProbability: data.avgProbability,
        recentActivity: data.activities || []
      }
    }
  },
  
  // Opportunities
  getOpportunities: async (params?: any) => {
    const response = await apiClient.get('/api/customer-opportunities', { params })
    // Map response to expected format
    const { data } = response.data
    return {
      data: Array.isArray(data) ? data : []
    }
  },
  createOpportunity: async (payload: {
    title: string
    value?: number
    status?: string
    probability?: number
    notes?: string
  }) => {
    const response = await apiClient.post('/api/customer-opportunities', payload)
    return response.data.data
  },
  updateOpportunity: async (id: string, payload: {
    title: string
    value?: number
    status?: string
    probability?: number
    notes?: string
  }) => {
    const response = await apiClient.put(`/api/customer-opportunities/${id}`, payload)
    return response.data.data
  },
  deleteOpportunity: async (id: string) => {
    const response = await apiClient.delete(`/api/customer-opportunities/${id}`)
    return response.data
  },
  getOpportunity: (id: string) => apiClient.get(`/api/opportunities/${id}`),
  
  // Profile
  getProfile: async () => {
    const response = await apiClient.get('/api/profile')
    // Return the data object from the response
    return response.data.data
  },
  updateProfile: async (data: any) => {
    const response = await apiClient.put('/api/profile', data)
    return response.data.data
  },
  
  // Auth
  login: (credentials: { email: string; password: string }) => 
    apiClient.post('/api/auth/login', credentials),
  logout: () => apiClient.post('/api/auth/logout'),
  me: () => apiClient.get('/api/auth/me')
}
