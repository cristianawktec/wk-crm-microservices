<template>
  <div class="notification-bell">
    <!-- Bell Icon Button -->
    <button
      @click="togglePanel"
      class="bell-button"
      :class="{ active: showPanel }"
      title="Notificações"
    >
      <svg
        class="bell-icon"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
      >
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
      </svg>
      
      <!-- Badge for unread count -->
      <span v-if="unreadCount > 0" class="badge">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Panel -->
    <transition name="panel-slide">
      <div v-if="showPanel" class="notification-panel">
        <!-- Header -->
        <div class="panel-header">
          <h3>Notificações</h3>
          <div class="header-actions">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="btn-action"
              title="Marcar todas como lidas"
            >
              <svg class="icon-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 6L9 17l-5-5"></path>
              </svg>
            </button>
            <button @click="closePanel" class="btn-close">
              <svg class="icon-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list">
          <div v-if="notifications.length === 0" class="empty-state">
            <p>Nenhuma notificação</p>
          </div>

          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="notification-item"
            :class="{ unread: !notification.is_read }"
            @click="handleNotificationClick(notification)"
          >
            <!-- Icon -->
            <div class="notification-icon" :class="notification.type">
              <svg
                v-if="notification.type === 'opportunity_created'"
                class="icon"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
              >
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 8v8M8 12h8"></path>
              </svg>
              <svg
                v-else-if="notification.type === 'opportunity_status_changed'"
                class="icon"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
              >
                <path d="M20 6L9 17l-5-5"></path>
              </svg>
              <svg
                v-else-if="notification.type === 'opportunity_value_changed'"
                class="icon"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
              >
                <path d="M12 2L15.09 8.26H22L17.64 12.61L19.16 18.97L12 15.77L4.84 18.97L6.36 12.61L2 8.26H8.91L12 2Z"></path>
              </svg>
              <svg
                v-else
                class="icon"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
              >
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="19" cy="12" r="1"></circle>
                <circle cx="5" cy="12" r="1"></circle>
              </svg>
            </div>

            <!-- Content -->
            <div class="notification-content">
              <h4>{{ notification.title }}</h4>
              <p>{{ notification.message }}</p>
              <span class="timestamp">{{ notification.created_at_formatted }}</span>
            </div>

            <!-- Actions -->
            <div class="notification-actions">
              <button
                v-if="!notification.is_read"
                @click.stop="markAsRead(notification.id)"
                class="btn-icon"
                title="Marcar como lida"
              >
                <svg class="icon-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path d="M20 6L9 17l-5-5"></path>
                </svg>
              </button>
              <button
                @click.stop="deleteNotification(notification.id)"
                class="btn-icon danger"
                title="Remover"
              >
                <svg class="icon-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="panel-footer">
          <button class="btn-secondary" @click="loadNotifications">
            Atualizar
          </button>
          <router-link to="/notifications" class="btn-primary" @click="closePanel">
            Ver Todas
          </router-link>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <div
      v-if="showPanel"
      class="panel-overlay"
      @click="closePanel"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useNotificationService } from '../services/notification'

const showPanel = ref(false)
const { notifications, unreadCount, init, loadNotifications, markAsRead, markAllAsRead, deleteNotification, cleanup } = useNotificationService()

onMounted(() => {
  init()
  // Reload notifications every 30 seconds
  const interval = setInterval(() => {
    loadNotifications()
  }, 30000)
  
  onUnmounted(() => {
    clearInterval(interval)
    cleanup()
  })
})

function togglePanel() {
  showPanel.value = !showPanel.value
}

function closePanel() {
  showPanel.value = false
}

function handleNotificationClick(notification: any) {
  if (!notification.is_read) {
    markAsRead(notification.id)
  }
  if (notification.action_url) {
    window.location.hash = `#${notification.action_url}`
    closePanel()
  }
}
</script>

<style scoped>
.notification-bell {
  position: relative;
}

.bell-button {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  transition: color 0.2s;
}

.bell-button:hover {
  color: #333;
}

.bell-button.active {
  color: #1e7e34;
}

.bell-icon {
  width: 24px;
  height: 24px;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.notification-panel {
  position: absolute;
  top: 100%;
  right: 0;
  width: 400px;
  max-height: 600px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  margin-top: 8px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.panel-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.btn-action,
.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #666;
  transition: color 0.2s;
}

.btn-action:hover,
.btn-close:hover {
  color: #333;
}

.icon-sm {
  width: 16px;
  height: 16px;
}

.notifications-list {
  flex: 1;
  overflow-y: auto;
}

.empty-state {
  padding: 32px 16px;
  text-align: center;
  color: #999;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid #f5f5f5;
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #f9f9f9;
}

.notification-item.unread {
  background-color: #f0f9f7;
}

.notification-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.notification-icon.opportunity_created {
  background-color: #e3f2fd;
  color: #1976d2;
}

.notification-icon.opportunity_status_changed {
  background-color: #e8f5e9;
  color: #388e3c;
}

.notification-icon.opportunity_value_changed {
  background-color: #fff3e0;
  color: #f57c00;
}

.icon {
  width: 20px;
  height: 20px;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-content h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.notification-content p {
  margin: 0 0 4px 0;
  font-size: 13px;
  color: #666;
  white-space: normal;
  word-break: break-word;
}

.timestamp {
  font-size: 12px;
  color: #999;
}

.notification-actions {
  flex-shrink: 0;
  display: flex;
  gap: 4px;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #666;
  transition: color 0.2s;
}

.btn-icon:hover {
  color: #333;
}

.btn-icon.danger:hover {
  color: #d32f2f;
}

.panel-footer {
  padding: 12px 16px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  gap: 8px;
}

.btn-secondary,
.btn-primary {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.btn-secondary {
  background: white;
  color: #333;
}

.btn-secondary:hover {
  background-color: #f5f5f5;
}

.btn-primary {
  background: #1e7e34;
  color: white;
  border-color: #1e7e34;
}

.btn-primary:hover {
  background-color: #15571f;
}

.panel-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
}

.panel-slide-enter-active,
.panel-slide-leave-active {
  transition: all 0.3s ease;
}

.panel-slide-enter-from,
.panel-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@media (max-width: 600px) {
  .notification-panel {
    width: 320px;
  }
}
</style>
