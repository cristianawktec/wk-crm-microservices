<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Meu Perfil</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Avatar Card -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
          <div class="w-32 h-32 mx-auto bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl font-bold mb-4">
            {{ userInitials }}
          </div>
          <h2 class="text-xl font-bold text-gray-900">{{ profile.name }}</h2>
          <p class="text-gray-600 mt-1">{{ profile.email }}</p>
          <div v-if="profile.company" class="mt-4 px-4 py-2 bg-indigo-50 rounded-lg">
            <p class="text-sm text-indigo-700 font-medium">{{ profile.company }}</p>
          </div>
        </div>
      </div>

      <!-- Profile Form -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Informações Pessoais</h3>

          <form @submit.prevent="handleUpdate" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Nome Completo
                </label>
                <input
                  id="name"
                  v-model="profile.name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  Email
                </label>
                <input
                  id="email"
                  v-model="profile.email"
                  type="email"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>

              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                  Telefone
                </label>
                <input
                  id="phone"
                  v-model="profile.phone"
                  type="tel"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>

              <div>
                <label for="company" class="block text-sm font-medium text-gray-700 mb-2">
                  Empresa
                </label>
                <input
                  id="company"
                  v-model="profile.company"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>
            </div>

            <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm text-red-600">{{ error }}</p>
            </div>

            <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
              <p class="text-sm text-green-600">Perfil atualizado com sucesso!</p>
            </div>

            <div class="flex justify-end space-x-4">
              <button
                type="button"
                @click="fetchProfile"
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!loading">Salvar Alterações</span>
                <span v-else>Salvando...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { api } from '../services/api'
import { useToast } from 'vue-toastification'
import type { User } from '../types'

const toast = useToast()
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref(false)

const profile = ref<User>({
  id: '',
  name: '',
  email: '',
  company: '',
  phone: ''
})

const userInitials = computed(() => {
  const name = profile.value.name || 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
})

const fetchProfile = async () => {
  try {
    const data = await api.getProfile()
    profile.value = {
      id: data.id,
      name: data.name,
      email: data.email,
      phone: data.phone || '',
      company: data.company || ''
    }
  } catch (err) {
    console.error('Erro ao carregar perfil:', err)
  }
}

const handleUpdate = async () => {
  loading.value = true
  error.value = null
  success.value = false

  try {
    const updated = await api.updateProfile(profile.value)
    profile.value = {
      id: updated.id,
      name: updated.name,
      email: updated.email,
      phone: updated.phone || '',
      company: updated.company || ''
    }
    success.value = true
    toast.success('Perfil atualizado com sucesso!')
    
    setTimeout(() => {
      success.value = false
    }, 3000)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao atualizar perfil'
    toast.error('Erro ao atualizar perfil')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProfile()
})
</script>
