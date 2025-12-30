<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl p-6 relative max-h-[90vh] overflow-y-auto">
      <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="close">✕</button>
      
      <div class="flex items-center gap-3 mb-6">
        <div class="p-3 bg-purple-100 rounded-full">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Insights de IA</h2>
          <p class="text-sm text-gray-500">{{ opportunity.title }}</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-5/6"></div>
        </div>
      </div>

      <!-- Insights -->
      <div v-else-if="insights" class="space-y-6">
        <!-- Risk Score -->
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-200">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-gray-700">Nível de Risco</span>
            <span 
              class="px-3 py-1 text-xs font-semibold rounded-full"
              :class="getRiskBadgeClass(insights.risk_label)"
            >
              {{ getRiskLabel(insights.risk_label) }}
            </span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div 
              class="h-3 rounded-full transition-all duration-500"
              :class="getRiskBarClass(insights.risk_label)"
              :style="{ width: `${insights.risk_score * 100}%` }"
            ></div>
          </div>
          <p class="text-xs text-gray-600 mt-2">{{ (insights.risk_score * 100).toFixed(0) }}% de risco</p>
        </div>

        <!-- Summary -->
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <h3 class="text-sm font-semibold text-gray-900 mb-1">Resumo</h3>
              <p class="text-sm text-gray-700">{{ insights.summary }}</p>
            </div>
          </div>
        </div>

        <!-- Next Action -->
        <div class="bg-green-50 rounded-xl p-4 border border-green-200">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <div>
              <h3 class="text-sm font-semibold text-gray-900 mb-1">Próxima Ação Recomendada</h3>
              <p class="text-sm text-gray-700">{{ insights.next_action }}</p>
            </div>
          </div>
        </div>

        <!-- Recommendation -->
        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <h3 class="text-sm font-semibold text-gray-900 mb-1">Recomendação</h3>
              <p class="text-sm text-gray-700">{{ insights.recommendation }}</p>
            </div>
          </div>
        </div>

        <!-- Model Info -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
          <div class="flex items-center gap-2 text-xs text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Modelo: {{ insights.model }}</span>
          </div>
          <div v-if="insights.cached" class="flex items-center gap-1 text-xs text-amber-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Resposta em cache</span>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 rounded-xl p-4 border border-red-200">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-sm font-semibold text-red-900 mb-1">Erro ao gerar insights</h3>
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
        <button 
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          @click="close"
        >
          Fechar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { api } from '../services/api'
import type { Opportunity } from '../types'

interface Props {
  isOpen: boolean
  opportunity: Opportunity
}

interface Insight {
  risk_score: number
  risk_label: string
  next_action: string
  recommendation: string
  summary: string
  model: string
  cached: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'close'): void
}>()

const loading = ref(false)
const insights = ref<Insight | null>(null)
const error = ref<string | null>(null)

const close = () => {
  emit('close')
}

const getRiskLabel = (label: string) => {
  const labels: Record<string, string> = {
    low: 'Baixo',
    medium: 'Médio',
    high: 'Alto',
    unknown: 'Desconhecido'
  }
  return labels[label] || label
}

const getRiskBadgeClass = (label: string) => {
  const classes: Record<string, string> = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-red-100 text-red-800',
    unknown: 'bg-gray-100 text-gray-800'
  }
  return classes[label] || 'bg-gray-100 text-gray-800'
}

const getRiskBarClass = (label: string) => {
  const classes: Record<string, string> = {
    low: 'bg-green-500',
    medium: 'bg-yellow-500',
    high: 'bg-red-500',
    unknown: 'bg-gray-500'
  }
  return classes[label] || 'bg-gray-500'
}

const fetchInsights = async () => {
  if (!props.isOpen) return
  
  loading.value = true
  error.value = null
  insights.value = null

  try {
    const payload = {
      id: props.opportunity.id,
      title: props.opportunity.title,
      description: props.opportunity.notes || undefined,
      value: props.opportunity.value || undefined,
      probability: props.opportunity.probability || undefined,
      status: props.opportunity.status || undefined,
      customer_name: undefined,
      sector: undefined
    }

    console.log('📊 Fetching insights for:', props.opportunity.title)
    console.log('Payload:', payload)
    insights.value = await api.getOpportunityInsights(payload)
    console.log('✅ Insights received:', insights.value)
  } catch (err: any) {
    console.error('❌ Error fetching insights:', err)
    console.error('Response status:', err.response?.status)
    console.error('Response data:', err.response?.data)
    error.value = err.response?.data?.message || err.message || 'Não foi possível gerar os insights. Tente novamente.'
  } finally {
    loading.value = false
  }
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchInsights()
  }
})
</script>
