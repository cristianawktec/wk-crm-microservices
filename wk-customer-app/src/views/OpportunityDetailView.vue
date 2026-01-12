<template>
  <div class="opportunity-detail">
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="opportunity" class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ opportunity.title }}</h1>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <span>Criada em {{ formatDate(opportunity.created_at) }}</span>
              <span class="px-3 py-1 rounded-full text-xs font-medium" :class="statusClass(opportunity.status)">
                {{ opportunity.status }}
              </span>
            </div>
          </div>
          <router-link to="/opportunities" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </router-link>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Value Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-600">Valor</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900">
            {{ formatCurrency(opportunity.value) }}
          </p>
        </div>

        <!-- Probability Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-600">Probabilidade</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900">{{ opportunity.probability || 0 }}%</p>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="opportunity.notes" class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Observações</h3>
        <p class="text-gray-700 whitespace-pre-wrap">{{ opportunity.notes }}</p>
      </div>

      <!-- Seller -->
      <div v-if="opportunity.seller" class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Responsável</h3>
        <div class="flex items-center">
          <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold mr-3">
            {{ getSellerName(opportunity.seller).charAt(0).toUpperCase() }}
          </div>
          <span class="text-gray-700">{{ getSellerName(opportunity.seller) }}</span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex space-x-4">
        <router-link 
          to="/opportunities" 
          class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-center font-medium"
        >
          Voltar
        </router-link>
        <button 
          @click="editOpportunity"
          class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium"
        >
          Editar
        </button>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-600">Oportunidade não encontrada</p>
      <router-link to="/opportunities" class="text-indigo-600 hover:text-indigo-700 mt-4 inline-block">
        Voltar para oportunidades
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../services/api'
import type { Opportunity } from '../types'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const opportunity = ref<Opportunity | null>(null)

onMounted(async () => {
  await loadOpportunity()
})

async function loadOpportunity() {
  loading.value = true
  try {
    const id = route.params.id as string
    const response = await api.getOpportunity(id)
    opportunity.value = response.data
  } catch (error) {
    console.error('Erro ao carregar oportunidade:', error)
  } finally {
    loading.value = false
  }
}

function editOpportunity() {
  router.push('/opportunities')
  // TODO: Abrir modal de edição com o ID da oportunidade
}

function formatDate(date: string): string {
  if (!date) return '—'
  const d = new Date(date)
  const options: Intl.DateTimeFormatOptions = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }
  return d.toLocaleDateString('pt-BR', options)
}

function formatCurrency(value: number | undefined): string {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

function getSellerName(seller: string | { id: string; name: string } | undefined): string {
  if (!seller) return 'Não atribuído'
  return typeof seller === 'string' ? seller : seller.name
}

function statusClass(status: string): string {
  const classes: Record<string, string> = {
    'Aberta': 'bg-blue-100 text-blue-800',
    'Em Negociação': 'bg-yellow-100 text-yellow-800',
    'Proposta Enviada': 'bg-purple-100 text-purple-800',
    'Ganha': 'bg-green-100 text-green-800',
    'Perdida': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>

<style scoped>
.opportunity-detail {
  padding: 24px;
  min-height: calc(100vh - 200px);
}
</style>
