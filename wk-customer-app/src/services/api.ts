import axios, { type AxiosInstance } from 'axios'

const apiBase = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/$/, '')

const apiClient: AxiosInstance = axios.create({
  baseURL: `${apiBase}/api`,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 60000
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
    // Não desloga automaticamente no 401 para evitar loops
    // if (error.response?.status === 401) {
    //   localStorage.removeItem('token')
    //   window.location.href = '/login'
    // }
    return Promise.reject(error)
  }
)

export default apiClient

// API Methods
export const api = {
  // Dashboard
  getDashboardStats: async () => {
    const doRequest = async () => apiClient.get('/dashboard/customer-stats')

    try {
      const response = await doRequest()
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
    } catch (error: any) {
      // Se estiver 401 (token ausente/expirado), tenta regenerar token de teste e refazer a chamada
      if (error?.response?.status === 401) {
        try {
          const testResp = await fetch(`${apiBase}/api/auth/test-customer`)
          const testData = await testResp.json()
          if (testData?.success && testData?.token) {
            localStorage.setItem('token', testData.token)
            localStorage.setItem('user', JSON.stringify(testData.user || {}))
            apiClient.defaults.headers.common.Authorization = `Bearer ${testData.token}`
            const retry = await doRequest()
            const { data } = retry.data
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
          }
        } catch (tokenError) {
          // log silencioso
          console.error('Auto-refresh de token falhou:', tokenError)
        }
      }
      throw error
    }
  },

  // Opportunities
  getOpportunities: async (params?: any) => {
    const response = await apiClient.get('/customer-opportunities', { params })
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
    const response = await apiClient.post('/customer-opportunities', payload)
    return response.data.data
  },
  updateOpportunity: async (id: string, payload: {
    title: string
    value?: number
    status?: string
    probability?: number
    notes?: string
  }) => {
    const response = await apiClient.put(`/customer-opportunities/${id}`, payload)
    return response.data.data
  },
  deleteOpportunity: async (id: string) => {
    const response = await apiClient.delete(`/customer-opportunities/${id}`)
    return response.data
  },
  getOpportunity: (id: string) => apiClient.get(`/customer-opportunities/${id}`),
  
  // Profile
  getProfile: async () => {
    const response = await apiClient.get('/profile')
    // Return the data object from the response
    return response.data.data
  },
  updateProfile: async (data: any) => {
    const response = await apiClient.put('/profile', data)
    return response.data.data
  },
  
  // Auth
  login: (credentials: { email: string; password: string }) => 
    apiClient.post('/auth/login', credentials),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get('/auth/me'),

  // Trends Analysis
  getTrends: async (period: string = 'year') => {
    const doRequest = async () => apiClient.get('/trends/analyze', { params: { period } })

    try {
      const response = await doRequest()
      if (response.data.success) {
        return response.data.data
      }
      throw new Error(response.data.message || 'Erro ao carregar tendências')
    } catch (error: any) {
      // Se estiver 401 (token ausente/expirado), tenta regenerar token de teste e refazer a chamada
      if (error?.response?.status === 401) {
        try {
          const testResp = await fetch(`${apiBase}/api/auth/test-customer`)
          const testData = await testResp.json()
          if (testData?.success && testData?.token) {
            localStorage.setItem('token', testData.token)
            localStorage.setItem('user', JSON.stringify(testData.user || {}))
            apiClient.defaults.headers.common.Authorization = `Bearer ${testData.token}`
            const retry = await doRequest()
            if (retry.data.success) {
              return retry.data.data
            }
          }
        } catch (tokenError) {
          console.error('Auto-refresh de token falhou:', tokenError)
        }
      }
      throw error
    }
  },

  // AI Insights
  getOpportunityInsights: async (payload: {
    id?: string
    title: string
    description?: string
    value?: number
    probability?: number
    status?: string
    customer_name?: string
    sector?: string
  }) => {
    const response = await apiClient.post('/ai/opportunity-insights', payload)
    return response.data.data
  },

  // Generic HTTP methods
  get: (url: string, config?: any) => apiClient.get(url, config),
  post: (url: string, data?: any, config?: any) => apiClient.post(url, data, config),
  put: (url: string, data?: any, config?: any) => apiClient.put(url, data, config),
  delete: (url: string, config?: any) => apiClient.delete(url, config),
  patch: (url: string, data?: any, config?: any) => apiClient.patch(url, data, config)
}

