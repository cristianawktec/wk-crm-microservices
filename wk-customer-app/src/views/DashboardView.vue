<template>
  <div>
    <!-- Header com Saudação -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Olá, {{ authStore.user?.name?.split(' ')[0] }}! 👋</h1>
      <p class="text-gray-600 mt-2">Bem-vindo ao seu painel de gerenciamento</p>
    </div>

    <!-- Stats Cards -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="n in 4" :key="n" class="bg-white rounded-xl shadow-sm p-6 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
        <div class="h-8 bg-gray-200 rounded w-1/2"></div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Oportunidades</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalOpportunities }}</p>
          </div>
          <div class="p-3 bg-indigo-100 rounded-lg">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Valor Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ formatCurrency(stats.totalValue) }}</p>
          </div>
          <div class="p-3 bg-green-100 rounded-lg">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Abertas</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.openOpportunities }}</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Prob. Média</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.avgProbability }}%</p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-lg">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Atividades Recentes -->
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Atividades Recentes</h2>
      
      <div v-if="loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="flex items-start space-x-4 p-4 border-l-4 border-gray-200 animate-pulse">
          <div class="h-10 w-10 bg-gray-200 rounded-full"></div>
          <div class="flex-1">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
          </div>
        </div>
      </div>

      <div v-else-if="stats.recentActivity && stats.recentActivity.length > 0" class="space-y-4">
        <div
          v-for="activity in stats.recentActivity"
          :key="activity.id"
          class="flex items-start space-x-4 p-4 hover:bg-gray-50 rounded-lg transition-colors border-l-4"
          :class="getActivityBorderColor(activity.type)"
        >
          <div class="p-2 rounded-full" :class="getActivityBgColor(activity.type)">
            <svg class="w-6 h-6" :class="getActivityTextColor(activity.type)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ activity.title }}</h3>
            <p v-if="activity.description" class="text-sm text-gray-600 mt-1">{{ activity.description }}</p>
            <p class="text-xs text-gray-500 mt-2">{{ formatDate(activity.created_at) }}</p>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        Nenhuma atividade recente
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { api } from '../services/api'
import type { DashboardStats } from '../types'

const authStore = useAuthStore()
const loading = ref(true)
const stats = ref<DashboardStats>({
  totalOpportunities: 0,
  totalValue: 0,
  openOpportunities: 0,
  wonOpportunities: 0,
  avgProbability: 0,
  recentActivity: []
})

const fetchDashboardData = async () => {
  try {
    const response = await api.getDashboardStats()
    stats.value = {
      totalOpportunities: response.data.totalOpportunities || 0,
      totalValue: response.data.totalValue || 0,
      openOpportunities: response.data.openOpportunities || 0,
      wonOpportunities: response.data.wonOpportunities || 0,
      avgProbability: response.data.avgProbability || 0,
      recentActivity: response.data.recentActivity || []
    }
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  } finally {
    loading.value = false
  }
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
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getActivityBorderColor = (type: string) => {
  const colors: Record<string, string> = {
    opportunity: 'border-blue-500',
    update: 'border-yellow-500',
    completed: 'border-green-500'
  }
  return colors[type] || 'border-gray-300'
}

const getActivityBgColor = (type: string) => {
  const colors: Record<string, string> = {
    opportunity: 'bg-blue-100',
    update: 'bg-yellow-100',
    completed: 'bg-green-100'
  }
  return colors[type] || 'bg-gray-100'
}

const getActivityTextColor = (type: string) => {
  const colors: Record<string, string> = {
    opportunity: 'text-blue-600',
    update: 'text-yellow-600',
    completed: 'text-green-600'
  }
  return colors[type] || 'text-gray-600'
}

onMounted(() => {
  fetchDashboardData()
})
</script>
