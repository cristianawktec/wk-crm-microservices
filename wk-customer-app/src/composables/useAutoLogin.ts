import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ref } from 'vue'

export function useAutoLogin() {
  const router = useRouter()
  const authStore = useAuthStore()
  const isLoggingIn = ref(false)

  async function autoLogin() {
    // Se já está autenticado, não faz nada
    if (authStore.isAuthenticated && authStore.token) {
      console.log('✅ Já autenticado, pulando auto-login')
      return
    }

    isLoggingIn.value = true
    try {
      const apiBase = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/$/, '')
      const loginUrl = `${apiBase}/api/auth/test-customer`
      console.log('📡 Fetching test-customer token from:', loginUrl)
      const response = await fetch(loginUrl, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
      })

      console.log('📦 Response status:', response.status)

      if (response.ok) {
        const data = await response.json()
        console.log('✅ Response data:', data)
        
        if (data.success && data.token) {
          console.log('🔐 Setting token:', data.token.substring(0, 20) + '...')
          // Salva o token no localStorage
          localStorage.setItem('token', data.token)
          localStorage.setItem('user', JSON.stringify(data.user))

          // Atualiza o store
          authStore.setToken(data.token)
          authStore.setUser(data.user)

          console.log('✅ Auto-login realizado com sucesso, navegando...')
          // Navegar para dashboard/notifications
          if (router.currentRoute.value.path === '/login') {
            router.push({ name: 'Dashboard' })
          }
          return true
        }
      } else {
        console.error('❌ Response not OK:', response.status, response.statusText)
      }
    } catch (error) {
      console.error('❌ Erro no auto-login:', error)
    } finally {
      isLoggingIn.value = false
    }

    return false
  }

  return {
    autoLogin,
    isLoggingIn
  }
}
