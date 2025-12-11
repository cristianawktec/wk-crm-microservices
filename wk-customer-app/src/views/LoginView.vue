<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">WK CRM</h1>
        <p class="text-gray-600">Portal do Cliente</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="seu@email.com"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Senha
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="••••••••"
          />
        </div>

        <div v-if="authStore.error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ authStore.error }}</p>
        </div>

        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!authStore.loading">Entrar</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Carregando...
          </span>
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        Esqueceu sua senha? 
        <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">Recuperar</a>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const form = ref({
  email: '',
  password: ''
})

const handleLogin = async () => {
  try {
    await authStore.login(form.value.email, form.value.password)
    toast.success('Login realizado com sucesso!')
    router.push('/')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Erro ao fazer login')
  }
}
</script>
