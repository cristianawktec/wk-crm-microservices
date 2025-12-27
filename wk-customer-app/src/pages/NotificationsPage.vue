<template>
  <div class="notifications-page">
    <div class="page-header">
      <h1>Notificações</h1>
      <div class="header-actions">
        <select v-model="filterType" class="filter-select">
          <option value="">Todos os tipos</option>
          <option value="opportunity_created">Oportunidades Criadas</option>
          <option value="opportunity_status_changed">Status Alterado</option>
          <option value="opportunity_value_changed">Valor Alterado</option>
        </select>
        
        <select v-model="filterStatus" class="filter-select">
          <option value="">Todas</option>
          <option value="unread">Não lidas</option>
          <option value="read">Lidas</option>
        </select>

        <button
          v-if="hasUnread"
          @click="markAllAsRead"
          class="btn-action"
        >
          Marcar todas como lidas
        </button>
      </div>
    </div>

    <!-- Notifications List -->
    <div v-if="filteredNotifications.length > 0" class="notifications-container">
      <div
        v-for="notification in filteredNotifications"
        :key="notification.id"
        class="notification-card"
        :class="{ unread: !notification.is_read }"
      >
        <div class="card-icon" :class="notification.type">
          <svg
            v-if="notification.type === 'opportunity_created'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 8v8M8 12h8" stroke="white" stroke-width="2"></path>
          </svg>
          <svg
            v-else-if="notification.type === 'opportunity_status_changed'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path d="M20 6L9 17l-5-5" stroke="white" stroke-width="2"></path>
          </svg>
          <svg
            v-else-if="notification.type === 'opportunity_value_changed'"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path d="M12 2L15.09 8.26H22L17.64 12.61L19.16 18.97L12 15.77L4.84 18.97L6.36 12.61L2 8.26H8.91L12 2Z"></path>
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="19" cy="12" r="1"></circle>
            <circle cx="5" cy="12" r="1"></circle>
          </svg>
        </div>

        <div class="card-content">
          <h3>{{ notification.title }}</h3>
          <p>{{ notification.message }}</p>
          <div class="card-meta">
            <span class="date">{{ formatDate(notification.created_at_formatted) }}</span>
            <span
              v-if="!notification.is_read"
              class="badge-unread"
            >
              Não lida
            </span>
          </div>
          <div v-if="notification.data" class="card-data">
            <div v-if="notification.data.opportunity" class="data-item">
              <strong>Oportunidade:</strong>
              {{ notification.data.opportunity.name }}
            </div>
            <div v-if="notification.data.old_value || notification.data.new_value" class="data-item">
              <span v-if="notification.data.old_value">
                <strong>De:</strong> R$ {{ formatCurrency(notification.data.old_value) }}
              </span>
              <span v-if="notification.data.new_value">
                <strong>Para:</strong> R$ {{ formatCurrency(notification.data.new_value) }}
              </span>
            </div>
          </div>
        </div>

        <div class="card-actions">
          <button
            v-if="!notification.is_read"
            @click="markAsRead(notification.id)"
            class="btn-mark-read"
            title="Marcar como lida"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M20 6L9 17l-5-5"></path>
            </svg>
          </button>
          <button
            @click="goToOpportunity(notification)"
            v-if="notification.action_url"
            class="btn-view"
          >
            Ver Detalhes
          </button>
          <button
            @click="deleteNotification(notification.id)"
            class="btn-delete"
            title="Remover"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button
          :disabled="currentPage === 1"
          @click="previousPage"
          class="btn-pagination"
        >
          Anterior
        </button>
        <span class="page-info">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
        <button
          :disabled="currentPage === totalPages"
          @click="nextPage"
          class="btn-pagination"
        >
          Próxima
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
      </svg>
      <h2>Nenhuma notificação</h2>
      <p>Você não tem notificações{{ filterType || filterStatus ? ' que correspondem aos filtros' : '' }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationService, type Notification } from '../services/notification'
import apiClient from '../services/api'

const router = useRouter()
const { markAsRead, markAllAsRead, deleteNotification, cleanup } = useNotificationService()

const filterType = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const itemsPerPage = 20
const allNotifications = ref<Notification[]>([])
const totalCount = ref(0)

onMounted(async () => {
  await loadNotifications()
  
  // Auto-reload every 30 seconds
  const interval = setInterval(() => {
    loadNotifications()
  }, 30000)

  onUnmounted(() => {
    clearInterval(interval)
    cleanup()
  })
})

async function loadNotifications() {
  try {
    const skip = (currentPage.value - 1) * itemsPerPage
    const limit = itemsPerPage
    
    console.log(`📡 Loading notifications: limit=${limit}, page=${currentPage.value}`)
    
    const response = await apiClient.get(`/notifications?limit=${limit}`)
    
    console.log('📦 Notifications response:', response.data)
    
    if (response.data.success) {
      allNotifications.value = response.data.data || []
      totalCount.value = response.data.total || 0
      console.log(`✅ Loaded ${allNotifications.value.length} notifications`)
    }
  } catch (error) {
    console.error('❌ Error loading notifications:', error)
  }
}

const filteredNotifications = computed(() => {
  return allNotifications.value.filter((n: Notification) => {
    if (filterType.value && n.type !== filterType.value) return false
    if (filterStatus.value === 'unread' && n.is_read) return false
    if (filterStatus.value === 'read' && !n.is_read) return false
    return true
  })
})

const hasUnread = computed(() => {
  return allNotifications.value.some((n: Notification) => !n.is_read)
})

const totalPages = computed(() => {
  return Math.ceil(totalCount.value / itemsPerPage)
})

function formatDate(date: string): string {
  if (date === 'Agora') return 'Agora'
  
  const d = new Date(date)
  if (isNaN(d.getTime())) return date
  
  const now = new Date()
  const diffMs = now.getTime() - d.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Agora'
  if (diffMins < 60) return `${diffMins}m atrás`
  if (diffHours < 24) return `${diffHours}h atrás`
  if (diffDays < 7) return `${diffDays}d atrás`
  
  return d.toLocaleDateString('pt-BR')
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

function goToOpportunity(notification: Notification) {
  if (notification.action_url) {
    router.push(notification.action_url)
  }
}

function previousPage() {
  if (currentPage.value > 1) {
    currentPage.value--
    loadNotifications()
  }
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadNotifications()
  }
}
</script>

<style scoped>
.notifications-page {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background: white;
  cursor: pointer;
}

.btn-action {
  padding: 8px 16px;
  background: #1e7e34;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-action:hover {
  background-color: #15571f;
}

.notifications-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.notification-card {
  display: grid;
  grid-template-columns: 60px 1fr auto;
  gap: 16px;
  align-items: start;
  padding: 16px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.2s;
}

.notification-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.notification-card.unread {
  background-color: #f0f9f7;
  border-color: #1e7e34;
}

.card-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon svg {
  width: 32px;
  height: 32px;
}

.card-icon.opportunity_created {
  background-color: #e3f2fd;
  color: #1976d2;
}

.card-icon.opportunity_status_changed {
  background-color: #e8f5e9;
  color: #388e3c;
}

.card-icon.opportunity_value_changed {
  background-color: #fff3e0;
  color: #f57c00;
}

.card-content h3 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.card-content p {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #666;
  line-height: 1.5;
}

.card-meta {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 8px;
}

.date {
  font-size: 12px;
  color: #999;
}

.badge-unread {
  display: inline-block;
  padding: 2px 8px;
  background-color: #fff3cd;
  color: #856404;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.card-data {
  background-color: rgba(0, 0, 0, 0.02);
  padding: 8px;
  border-radius: 4px;
  font-size: 12px;
}

.data-item {
  margin: 4px 0;
}

.data-item strong {
  color: #333;
}

.card-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.btn-mark-read,
.btn-view,
.btn-delete {
  padding: 8px 12px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-mark-read svg,
.btn-delete svg {
  width: 16px;
  height: 16px;
  stroke: currentColor;
  stroke-width: 2;
  fill: none;
}

.btn-mark-read:hover {
  background-color: #e8f5e9;
  border-color: #4caf50;
  color: #4caf50;
}

.btn-view {
  background: #1e7e34;
  color: white;
  border-color: #1e7e34;
}

.btn-view:hover {
  background-color: #15571f;
}

.btn-delete:hover {
  background-color: #ffebee;
  border-color: #d32f2f;
  color: #d32f2f;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 32px;
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

.btn-pagination {
  padding: 8px 16px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-pagination:hover:not(:disabled) {
  background-color: #f5f5f5;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 14px;
  color: #666;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  text-align: center;
}

.empty-state svg {
  width: 80px;
  height: 80px;
  color: #ccc;
  margin-bottom: 16px;
  stroke: currentColor;
  stroke-width: 1.5;
  fill: none;
}

.empty-state h2 {
  margin: 0 0 8px 0;
  font-size: 20px;
  color: #999;
}

.empty-state p {
  margin: 0;
  color: #ccc;
  font-size: 14px;
}

@media (max-width: 768px) {
  .notifications-page {
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .notification-card {
    grid-template-columns: 1fr;
  }

  .card-actions {
    flex-wrap: wrap;
  }

  .header-actions {
    width: 100%;
  }

  .filter-select {
    flex: 1;
    min-width: 150px;
  }
}
</style>
