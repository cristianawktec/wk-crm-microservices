<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Minhas Oportunidades</h1>
      <button
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
        @click="openModal = true"
      >
        Nova Oportunidade
      </button>
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
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span><span class="font-medium">Vendedor:</span> {{ sellerName(opp) }}</span>
          </div>

          <div v-if="opp.probability" class="flex items-center text-sm text-gray-600">
            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span>Probabilidade: {{ opp.probability }}%</span>
          </div>

          <div class="pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500">
              <span class="font-medium">Criada:</span> {{ formatDate(opp.created_at) }}
            </p>
          </div>

          <div v-if="opp.notes" class="pt-2">
            <p class="text-xs font-medium text-gray-500 mb-1">Observações:</p>
            <p class="text-sm text-gray-600 line-clamp-2">{{ opp.notes }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex items-center gap-2 pt-3 border-t border-gray-100">
          <button 
            @click="showInsights(opp)" 
            class="flex-1 px-3 py-2 text-sm bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            Insights
          </button>
          <button 
            @click="editOpportunity(opp)" 
            class="flex-1 px-3 py-2 text-sm bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors"
          >
            Editar
          </button>
          <button 
            @click="confirmDelete(opp)" 
            class="flex-1 px-3 py-2 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors"
          >
            Excluir
          </button>
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

    <!-- Modal Nova Oportunidade -->
    <div v-if="openModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 px-4">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl p-6 relative">
        <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" @click="closeModal">✕</button>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ editingId ? 'Editar Oportunidade' : 'Nova Oportunidade' }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
            <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Ex: Projeto CRM" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
            <input v-model.number="form.value" type="number" min="0" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Probabilidade (%)</label>
            <input v-model.number="form.probability" type="number" min="0" max="100" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
              <option value="Aberta">Aberta</option>
              <option value="Em Negociação">Em Negociação</option>
              <option value="Proposta Enviada">Proposta Enviada</option>
              <option value="Ganha">Ganha</option>
              <option value="Perdida">Perdida</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Contexto, próximos passos, etc."></textarea>
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50" @click="closeModal">Cancelar</button>
          <button :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50" @click="saveOpportunity">
            {{ saving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- AI Analysis Modal -->
    <AiAnalysisModal 
      :is-open="insightModal.isOpen"
      :opportunity="insightModal.opportunity"
      @close="insightModal.isOpen = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '../services/api'
import type { Opportunity } from '../types'
import { useToast } from 'vue-toastification'
import AiAnalysisModal from '../components/AI/AiAnalysisModal.vue'

const opportunities = ref<Opportunity[]>([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
let debounceTimeout: any = null
const toast = useToast()

const openModal = ref(false)
const saving = ref(false)
const editingId = ref<string | null>(null)
const form = ref({
  title: '',
  value: undefined as number | undefined,
  status: 'Aberta',
  probability: undefined as number | undefined,
  notes: ''
})

const insightModal = ref({
  isOpen: false,
  opportunity: {} as Opportunity
})

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

const saveOpportunity = async () => {
  if (!form.value.title.trim()) {
    toast.error('Título é obrigatório')
    return
  }
  saving.value = true
  try {
    const payload = {
      title: form.value.title,
      value: form.value.value,
      probability: form.value.probability,
      status: form.value.status,
      notes: form.value.notes
    }

    if (editingId.value) {
      await api.updateOpportunity(editingId.value, payload)
      toast.success(`Oportunidade "${form.value.title}" atualizada com sucesso!`)
    } else {
      await api.createOpportunity(payload)
      toast.success(`Oportunidade "${form.value.title}" criada com sucesso!`)
    }
    
    await fetchOpportunities()
    closeModal()
  } catch (error: any) {
    console.error('Erro ao salvar oportunidade:', error)
    const msg = error.response?.data?.message || 'Erro ao salvar oportunidade'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}

const editOpportunity = (opp: Opportunity) => {
  editingId.value = opp.id
  form.value = {
    title: opp.title,
    value: opp.value || undefined,
    status: opp.status,
    probability: opp.probability || undefined,
    notes: opp.notes || ''
  }
  openModal.value = true
}

const confirmDelete = async (opp: Opportunity) => {
  if (!confirm(`Tem certeza que deseja excluir "${opp.title}"?`)) return
  
  try {
    await api.deleteOpportunity(opp.id)
    toast.success(`Oportunidade "${opp.title}" excluída com sucesso!`)
    await fetchOpportunities()
  } catch (error: any) {
    console.error('Erro ao excluir oportunidade:', error)
    const msg = error.response?.data?.message || 'Erro ao excluir oportunidade'
    toast.error(msg)
  }
}

const closeModal = () => {
  openModal.value = false
  editingId.value = null
  form.value = {
    title: '',
    value: undefined,
    status: 'Aberta',
    probability: undefined,
    notes: ''
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const sellerName = (opp: Opportunity) => {
  if (!opp.seller) return 'Não atribuído'
  if (typeof opp.seller === 'string') return opp.seller
  return opp.seller.name || 'Não atribuído'
}

const formatDate = (date: string) => {
  const d = new Date(date)
  return d.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }) + ' às ' + d.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    open: 'Aberta',
    won: 'Ganha',
    lost: 'Perdida',
    negotiation: 'Em Negociação',
    proposal: 'Proposta Enviada',
    'Aberta': 'Aberta',
    'Ganha': 'Ganha',
    'Perdida': 'Perdida',
    'Em Negociação': 'Em Negociação',
    'Proposta Enviada': 'Proposta Enviada'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    won: 'bg-green-100 text-green-800',
    lost: 'bg-red-100 text-red-800',
    negotiation: 'bg-yellow-100 text-yellow-800',
    proposal: 'bg-purple-100 text-purple-800',
    'Aberta': 'bg-blue-100 text-blue-800',
    'Ganha': 'bg-green-100 text-green-800',
    'Perdida': 'bg-red-100 text-red-800',
    'Em Negociação': 'bg-yellow-100 text-yellow-800',
    'Proposta Enviada': 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusBorderColor = (status: string) => {
  const colors: Record<string, string> = {
    open: 'border-blue-500',
    won: 'border-green-500',
    lost: 'border-red-500',
    negotiation: 'border-yellow-500',
    proposal: 'border-purple-500',
    'Aberta': 'border-blue-500',
    'Ganha': 'border-green-500',
    'Perdida': 'border-red-500',
    'Em Negociação': 'border-yellow-500',
    'Proposta Enviada': 'border-purple-500'
  }
  return colors[status] || 'border-gray-300'
}

const showInsights = (opp: Opportunity) => {
  insightModal.value.opportunity = opp
  insightModal.value.isOpen = true
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
