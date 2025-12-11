import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
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
    axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  const login = async (email: string, password: string) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.post('/api/auth/login', { email, password })
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
      await axios.post('/api/auth/logout')
    } catch (err) {
      console.error('Erro ao fazer logout:', err)
    } finally {
      clearAuth()
    }
  }

  const fetchUser = async () => {
    if (!token.value) return
    
    try {
      const response = await axios.get('/api/auth/me')
      user.value = response.data
    } catch (err) {
      console.error('Erro ao buscar usuário:', err)
      clearAuth()
    }
  }

  // Inicializar token no axios se existir
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
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
    clearAuth
  }
})
