<template>
  <div>
    <!-- Back Button -->
    <button
      @click="$router.back()"
      class="mb-6 flex items-center text-gray-600 hover:text-gray-900 transition-colors"
    >
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Voltar
    </button>

    <!-- Loading -->
    <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 animate-pulse">
      <div class="h-8 bg-gray-200 rounded w-1/2 mb-6"></div>
      <div class="space-y-4">
        <div class="h-4 bg-gray-200 rounded w-full"></div>
        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
      <svg class="w-16 h-16 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Oportunidade não encontrada</h3>
      <p class="text-gray-600">{{ error }}</p>
    </div>

    <!-- Opportunity Details -->
    <div v-else-if="opportunity" class="space-y-6">
      <!-- Header Card -->
      <div class="bg-white rounded-xl shadow-sm p-6 border-t-4" :class="getStatusBorderColor(opportunity.status)">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ opportunity.title }}</h1>
            <span
              class="inline-block px-4 py-2 text-sm font-semibold rounded-full"
              :class="getStatusBadgeClass(opportunity.status)"
            >
              {{ getStatusLabel(opportunity.status) }}
            </span>
          </div>
          <button
            @click="editOpportunity"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          >
            Editar
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg mr-4">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Valor</p>
              <p class="text-xl font-bold text-gray-900">{{ formatCurrency(opportunity.value) }}</p>
            </div>
          </div>

          <div v-if="opportunity.probability" class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg mr-4">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Probabilidade</p>
              <p class="text-xl font-bold text-gray-900">{{ opportunity.probability }}%</p>
            </div>
          </div>

          <div v-if="opportunity.seller" class="flex items-center">
            <div class="p-3 bg-indigo-100 rounded-lg mr-4">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Vendedor</p>
              <p class="text-lg font-semibold text-gray-900">{{ sellerName }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Info -->
      <div v-if="opportunity.customer" class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Cliente
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-600">Nome</p>
            <p class="text-gray-900">{{ opportunity.customer.name }}</p>
          </div>
          <div v-if="opportunity.customer.email">
            <p class="text-sm font-medium text-gray-600">Email</p>
            <p class="text-gray-900">{{ opportunity.customer.email }}</p>
          </div>
          <div v-if="opportunity.customer.phone">
            <p class="text-sm font-medium text-gray-600">Telefone</p>
            <p class="text-gray-900">{{ opportunity.customer.phone }}</p>
          </div>
          <div v-if="opportunity.customer.document">
            <p class="text-sm font-medium text-gray-600">Documento</p>
            <p class="text-gray-900">{{ opportunity.customer.document }}</p>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="opportunity.notes" class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Observações
        </h2>
        <p class="text-gray-700 whitespace-pre-wrap">{{ opportunity.notes }}</p>
      </div>

      <!-- Metadata -->
      <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Informações Adicionais
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <p class="font-medium text-gray-600">Criada em</p>
            <p class="text-gray-900">{{ formatDate(opportunity.created_at) }}</p>
          </div>
          <div>
            <p class="font-medium text-gray-600">Última atualização</p>
            <p class="text-gray-900">{{ formatDate(opportunity.updated_at) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { opportunitiesApi } from '../services/api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref<string | null>(null)
const opportunity = ref<any>(null)

const sellerName = computed(() => {
  if (!opportunity.value?.seller) return 'Não atribuído'
  return opportunity.value.seller.name || 'Não atribuído'
})

const formatCurrency = (value: number | null) => {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    open: 'Aberta',
    negotiation: 'Em Negociação',
    proposal: 'Proposta Enviada',
    won: 'Ganha',
    lost: 'Perdida'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    negotiation: 'bg-yellow-100 text-yellow-800',
    proposal: 'bg-purple-100 text-purple-800',
    won: 'bg-green-100 text-green-800',
    lost: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusBorderColor = (status: string) => {
  const colors: Record<string, string> = {
    open: 'border-blue-500',
    negotiation: 'border-yellow-500',
    proposal: 'border-purple-500',
    won: 'border-green-500',
    lost: 'border-red-500'
  }
  return colors[status] || 'border-gray-500'
}

const fetchOpportunity = async () => {
  try {
    loading.value = true
    error.value = null
    const id = route.params.id as string
    const response = await opportunitiesApi.getById(parseInt(id))
    opportunity.value = response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Não foi possível carregar a oportunidade'
    console.error('Failed to fetch opportunity:', err)
  } finally {
    loading.value = false
  }
}

const editOpportunity = () => {
  router.push({ name: 'Opportunities' })
}

onMounted(() => {
  fetchOpportunity()
})
</script>
