import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ref } from 'vue'

export function useAutoLogin() {
  const router = useRouter()
  const authStore = useAuthStore()
  const isLoggingIn = ref(false)

  async function autoLogin() {
    console.log('🚫 Auto-login desativado')
    return false
  }

  return {
    autoLogin,
    isLoggingIn
  }
}
