<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-90vh overflow-y-auto">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5.36-5.36l.707-.707M5.05 5.05A9 9 0 1112 3c-4.4 0-8.27 2.903-9.657 6.82" />
          </svg>
          <h2 class="text-xl font-bold text-white">Análise de IA</h2>
        </div>
        <button @click="close" class="text-white hover:bg-white hover:bg-opacity-20 p-1 rounded">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-8 flex flex-col items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4"></div>
        <p class="text-gray-600">Analisando oportunidade com IA...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <h3 class="font-medium text-red-900">Erro ao analisar</h3>
              <p class="text-red-700 text-sm mt-1">{{ error }}</p>
            </div>
          </div>
          <button 
            @click="analyzeOpportunity" 
            class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm font-medium"
          >
            Tentar Novamente
          </button>
        </div>
      </div>

      <!-- Content -->
      <div v-else-if="analysis" class="p-6 space-y-6">
        <!-- Risk Score Section -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Nível de Risco</h3>
            <span :class="getRiskBadgeClass(analysis.risk_label)" class="px-3 py-1 rounded-full text-sm font-medium">
              {{ analysis.risk_label?.toUpperCase() || 'N/A' }}
            </span>
          </div>
          
          <!-- Risk Gauge -->
          <div class="space-y-2">
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <div 
                :class="getRiskGaugeColor(analysis.risk_score)"
                :style="{ width: analysis.risk_score + '%' }"
                class="h-full transition-all duration-300"
              ></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
              <span>Baixo Risco</span>
              <span>{{ analysis.risk_score }}%</span>
              <span>Alto Risco</span>
            </div>
          </div>
        </div>

        <!-- Summary Section -->
        <div v-if="analysis.summary" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="font-semibold text-blue-900 mb-2">Resumo</h3>
          <p class="text-blue-800 text-sm leading-relaxed">{{ analysis.summary }}</p>
        </div>

        <!-- Next Action Section -->
        <div v-if="analysis.next_action" class="bg-green-50 border border-green-200 rounded-lg p-4">
          <h3 class="font-semibold text-green-900 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Próxima Ação Recomendada
          </h3>
          <p class="text-green-800 text-sm">{{ analysis.next_action }}</p>
        </div>

        <!-- Recommendation Section -->
        <div v-if="analysis.recommendation" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <h3 class="font-semibold text-yellow-900 mb-2">Recomendação</h3>
          <p class="text-yellow-800 text-sm leading-relaxed">{{ analysis.recommendation }}</p>
        </div>

        <!-- Key Insights -->
        <div v-if="analysis.insights && analysis.insights.length > 0" class="space-y-3">
          <h3 class="font-semibold text-gray-900">Insights Principais</h3>
          <ul class="space-y-2">
            <li v-for="(insight, idx) in analysis.insights" :key="idx" class="flex gap-3">
              <span class="text-indigo-600 font-bold text-sm">•</span>
              <span class="text-gray-700 text-sm">{{ insight }}</span>
            </li>
          </ul>
        </div>

        <!-- Analysis Metadata -->
        <div v-if="analysis.created_at" class="border-t pt-4 text-xs text-gray-500">
          <p>Análise realizada em: {{ formatDate(analysis.created_at) }}</p>
          <p v-if="analysis.processing_time_ms">Tempo de processamento: {{ analysis.processing_time_ms }}ms</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
        <button 
          @click="close"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-medium"
        >
          Fechar
        </button>
        <button 
          v-if="!loading"
          @click="analyzeOpportunity"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium"
        >
          {{ analysis ? 'Analisar Novamente' : 'Analisar' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { api } from '../../services/api'
import type { Opportunity } from '../../types'

interface Props {
  isOpen: boolean
  opportunity?: Opportunity | null
}

interface Analysis {
  risk_score: number
  risk_label: string
  summary?: string
  next_action?: string
  recommendation?: string
  insights?: string[]
  created_at?: string
  processing_time_ms?: number
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  opportunity: null
})

const emit = defineEmits<{
  close: []
}>()

const loading = ref(false)
const error = ref('')
const analysis = ref<Analysis | null>(null)

watch(() => props.isOpen, (newVal) => {
  if (newVal && props.opportunity) {
    // Auto-analyze when modal opens
    analyzeOpportunity()
  } else if (!newVal) {
    // Reset when modal closes
    analysis.value = null
    error.value = ''
  }
})

async function analyzeOpportunity() {
  if (!props.opportunity) return

  loading.value = true
  error.value = ''
  analysis.value = null

  try {
    const response = await api.getOpportunityInsights({
      id: props.opportunity.id,
      title: props.opportunity.title,
      value: props.opportunity.value,
      probability: props.opportunity.probability,
      status: props.opportunity.status,
      description: props.opportunity.notes || props.opportunity.title
    })

    if (response && response.data && response.success) {
      analysis.value = response.data
    } else if (response && response.data) {
      // In case response doesn't have success field
      analysis.value = response.data
    } else {
      error.value = 'Não foi possível analisar a oportunidade'
    }
  } catch (err: any) {
    console.error('Erro ao analisar:', err)
    error.value = err.response?.data?.message || 'Erro ao analisar oportunidade'
  } finally {
    loading.value = false
  }
}

function close() {
  emit('close')
}

function getRiskBadgeClass(label: string): string {
  if (!label) return 'bg-gray-100 text-gray-800'
  const lower = label.toLowerCase()
  if (lower === 'baixo' || lower === 'low') return 'bg-green-100 text-green-800'
  if (lower === 'médio' || lower === 'medium') return 'bg-yellow-100 text-yellow-800'
  if (lower === 'alto' || lower === 'high') return 'bg-red-100 text-red-800'
  return 'bg-gray-100 text-gray-800'
}

function getRiskGaugeColor(score: number): string {
  if (score < 30) return 'bg-green-500'
  if (score < 70) return 'bg-yellow-500'
  return 'bg-red-500'
}

function formatDate(date: string): string {
  if (!date) return '—'
  try {
    return new Intl.DateTimeFormat('pt-BR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(new Date(date))
  } catch {
    return date
  }
}
</script>

<style scoped>
.max-h-90vh {
  max-height: 90vh;
}
</style>
