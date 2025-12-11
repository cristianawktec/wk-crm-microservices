<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Minhas Oportunidades</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por título..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            @input="debouncedFetch"
          />
        </div>
        <div>
          <select
            v-model="statusFilter"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            @change="fetchOpportunities"
          >
            <option value="">Todos os Status</option>
            <option value="open">Aberta</option>
            <option value="negotiation">Em Negociação</option>
            <option value="proposal">Proposta Enviada</option>
            <option value="won">Ganha</option>
            <option value="lost">Perdida</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="n in 6" :key="n" class="bg-white rounded-xl shadow-sm p-6 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
        <div class="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
        <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
        <div class="h-3 bg-gray-200 rounded w-2/3"></div>
      </div>
    </div>

    <!-- Opportunities Grid -->
    <div v-else-if="opportunities.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="opp in opportunities"
        :key="opp.id"
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border-t-4"
        :class="getStatusBorderColor(opp.status)"
      >
        <div class="flex items-start justify-between mb-4">
          <h3 class="font-semibold text-lg text-gray-900">{{ opp.title }}</h3>
          <span
            class="px-3 py-1 text-xs font-semibold rounded-full"
            :class="getStatusBadgeClass(opp.status)"
          >
            {{ getStatusLabel(opp.status) }}
          </span>
        </div>

        <div class="space-y-3">
          <div class="flex items-center text-sm text-gray-600">
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold">{{ formatCurrency(opp.value) }}</span>
          </div>

          <div v-if="opp.seller" class="flex items-center text-sm text-gray-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>{{ opp.seller.name }}</span>
          </div>

          <div v-if="opp.probability" class="flex items-center text-sm text-gray-600">
            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span>Probabilidade: {{ opp.probability }}%</span>
          </div>

          <div class="pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500">
              Criada em {{ formatDate(opp.created_at) }}
            </p>
          </div>

          <div v-if="opp.notes" class="pt-2">
            <p class="text-sm text-gray-600 line-clamp-2">{{ opp.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma oportunidade encontrada</h3>
      <p class="text-gray-600">{{ search || statusFilter ? 'Tente ajustar os filtros' : 'Você ainda não possui oportunidades cadastradas' }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '../services/api'
import type { Opportunity } from '../types'

const opportunities = ref<Opportunity[]>([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
let debounceTimeout: any = null

const fetchOpportunities = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (search.value) params.search = search.value
    if (statusFilter.value) params.status = statusFilter.value
    
    const response = await api.getOpportunities(params)
    opportunities.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    console.error('Erro ao carregar oportunidades:', error)
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    fetchOpportunities()
  }, 500)
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    open: 'Aberta',
    won: 'Ganha',
    lost: 'Perdida',
    negotiation: 'Em Negociação',
    proposal: 'Proposta Enviada'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    won: 'bg-green-100 text-green-800',
    lost: 'bg-red-100 text-red-800',
    negotiation: 'bg-yellow-100 text-yellow-800',
    proposal: 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusBorderColor = (status: string) => {
  const colors: Record<string, string> = {
    open: 'border-blue-500',
    won: 'border-green-500',
    lost: 'border-red-500',
    negotiation: 'border-yellow-500',
    proposal: 'border-purple-500'
  }
  return colors[status] || 'border-gray-300'
}

onMounted(() => {
  fetchOpportunities()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
