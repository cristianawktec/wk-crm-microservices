import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '../services/api'
import type { User } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  const setToken = (newToken: string) => {
    token.value = newToken
    localStorage.setItem('token', newToken)
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  const setUser = (newUser: User | null) => {
    user.value = newUser
    if (newUser) {
      localStorage.setItem('user', JSON.stringify(newUser))
    } else {
      localStorage.removeItem('user')
    }
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    delete apiClient.defaults.headers.common['Authorization']
  }

  const login = async (email: string, password: string) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/auth/login', { email, password })
      const { token: authToken, user: userPayload } = response.data
      const resolvedUser = userPayload

      if (!authToken || !resolvedUser) {
        throw new Error('Credenciais inválidas')
      }

      console.log('✅ Login normal - Token recebido:', authToken.substring(0, 20))
      console.log('✅ Login normal - User:', resolvedUser)

      // Garantir que token e user sejam salvos ANTES de retornar
      setToken(authToken)
      setUser(resolvedUser)
      
      // Garantir que o header Authorization está setado
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      console.log('✅ Token e user salvos no localStorage')

      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao fazer login'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      console.log('[logout] Iniciando logout...')
      console.log('[logout] Token antes do logout:', token.value ? 'presente' : 'ausente')

      if (token.value) {
        await apiClient.post('/auth/logout')
        console.log('[logout] Logout no backend realizado')
      } else {
        console.log('[logout] Sem token, apenas limpeza local')
      }
    } catch (err: any) {
      console.error('[logout] Erro ao fazer logout no backend:', err?.response?.data || err?.message || err)
      // Mesmo com erro no backend, continuar com logout local
    } finally {
      console.log('[logout] Limpando dados locais...')
      clearAuth()
      console.log('[logout] Logout completo')
    }
  }

  const fetchUser = async () => {
    if (!token.value) return
    
    try {
      const response = await apiClient.get('/auth/me')
      user.value = response.data
    } catch (err) {
      console.error('Erro ao buscar usuário:', err)
      clearAuth()
    }
  }

  // Inicializar token e usuário do localStorage se existir
  if (token.value) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    
    const storedUser = localStorage.getItem('user')
    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
      } catch (e) {
        console.error('Erro ao parsear usuário do localStorage:', e)
      }
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    login,
    logout,
    fetchUser,
    clearAuth,
    setToken,
    setUser
  }
})
