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
    localStorage.setItem('loggedOut', 'true')
    delete apiClient.defaults.headers.common['Authorization']
  }

  const login = async (email: string, password: string) => {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/auth/login', { email, password })
      const { token: authToken, data: userData } = response.data
      
      setToken(authToken)
      user.value = userData
      
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
      await apiClient.post('/auth/logout')
    } catch (err) {
      console.error('Erro ao fazer logout:', err)
    } finally {
      clearAuth()
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
