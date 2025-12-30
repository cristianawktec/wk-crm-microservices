

<template>
  <router-view />
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useAutoLogin } from './composables/useAutoLogin'

const authStore = useAuthStore()
const { autoLogin } = useAutoLogin()

onMounted(async () => {
  console.log('🔄 App mounted, checking auth...')
  console.log('Token from localStorage:', localStorage.getItem('token')?.substring(0, 20))
  console.log('User from localStorage:', localStorage.getItem('user'))
  console.log('Auto-login enabled:', import.meta.env.VITE_ENABLE_AUTO_LOGIN === 'true')
  
  // Se não está autenticado, tenta auto-login (apenas se habilitado)
  if (!authStore.token) {
    console.log('🔄 No token in store, checking auto-login...')
    await autoLogin()
  } else {
    console.log('✅ Token exists in store:', authStore.token.substring(0, 20) + '...')
    
    // Se tem token mas não tem user, tenta carregar do localStorage
    if (!authStore.user) {
      const storedUser = localStorage.getItem('user')
      if (storedUser) {
        try {
          authStore.setUser(JSON.parse(storedUser))
          console.log('✅ User loaded from localStorage:', authStore.user)
        } catch (e) {
          console.error('❌ Error parsing stored user:', e)
        }
      }
    }
  }

  // Se ainda assim tem token, carrega os dados do usuário
  if (authStore.token && authStore.user) {
    console.log('✅ Auth complete, user:', authStore.user.name)
  } else {
    console.log('❌ No token/user after auto-login attempt')
  }
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
    'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
