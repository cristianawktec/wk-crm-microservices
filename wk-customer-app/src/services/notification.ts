// src/services/notification.ts

import { ref, Ref } from 'vue'
import { useToast } from 'vue-toastification'
import apiClient from './api'

export interface Notification {
  id: string
  type: string
  title: string
  message: string
  action_url?: string
  is_read: boolean
  data: any
  created_at_formatted: string
}

const notifications: Ref<Notification[]> = ref([])
const unreadCount: Ref<number> = ref(0)
let sseConnection: EventSource | null = null
let sseRetryCount = 0
const MAX_SSE_RETRIES = 5
const SSE_BASE_RETRY_INTERVAL = 10000 // 10 seconds, increases exponentially

export function useNotificationService() {
  const toast = useToast()
  const apiUrl = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

  /**
   * Initialize notification service
   */
  function init() {
    try {
      console.log('🔔 NotificationService: Initializing...')
      loadNotifications()
      startSSEStream()
      console.log('🔔 NotificationService: Initialized successfully')
    } catch (error) {
      console.error('🔔 NotificationService: Initialization error:', error)
    }
  }

  /**
   * Load notifications from API
   */
  async function loadNotifications() {
    try {
      const response = await apiClient.get('/notifications?limit=20')
      if (response.data.success) {
        notifications.value = response.data.data || []
        unreadCount.value = response.data.unread || 0
      }
    } catch (error) {
      console.error('Error loading notifications:', error)
    }
  }

  /**
   * Get unread count
   */
  async function getUnreadCount() {
    try {
      const response = await apiClient.get('/notifications/unread-count')
      if (response.data.success) {
        unreadCount.value = response.data.unread_count
      }
    } catch (error) {
      console.error('Error getting unread count:', error)
    }
  }

  /**
   * Mark notification as read
   */
  async function markAsRead(notificationId: string) {
    try {
      const response = await apiClient.put(`/notifications/${notificationId}/read`)
      if (response.data.success) {
        const notif = notifications.value.find(n => n.id === notificationId)
        if (notif) {
          notif.is_read = true
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
      }
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }

  /**
   * Mark all as read
   */
  async function markAllAsRead() {
    try {
      const response = await apiClient.post('/notifications/read-all')
      if (response.data.success) {
        notifications.value.forEach(n => n.is_read = true)
        unreadCount.value = 0
        toast.success('Todas as notificações marcadas como lidas')
      }
    } catch (error) {
      console.error('Error marking all as read:', error)
    }
  }

  /**
   * Delete notification
   */
  async function deleteNotification(notificationId: string) {
    try {
      const response = await apiClient.delete(`/notifications/${notificationId}`)
      if (response.data.success) {
        notifications.value = notifications.value.filter(n => n.id !== notificationId)
        toast.success('Notificação removida')
      }
    } catch (error) {
      console.error('Error deleting notification:', error)
    }
  }

  /**
   * Start SSE stream for real-time notifications
   */
  function startSSEStream() {
    try {
      const token = localStorage.getItem('token')
      if (!token) {
        console.warn('SSE: No token found in localStorage')
        return
      }

      // Ensure /api prefix is included
      const baseUrl = apiUrl.includes('/api') ? apiUrl : `${apiUrl}/api`
      const streamUrl = `${baseUrl}/notifications/stream?token=${encodeURIComponent(token)}`

      console.log('SSE: Connecting to:', streamUrl)
      sseConnection = new EventSource(streamUrl)

      sseConnection.addEventListener('message', (event) => {
        try {
          const data = JSON.parse(event.data)

          if (data.type === 'connected') {
            console.log('SSE connected for user:', data.user_id)
          } else if (data.type === 'heartbeat') {
            unreadCount.value = data.unread_count || 0
          } else if (data.type === 'notification') {
            // New real-time notification received
            // Check if notification already exists in list to avoid duplicates/duplicate toasts
            const alreadyExists = notifications.value.some(n => n.id === data.id)
            
            if (!alreadyExists) {
              const newNotif: Notification = {
                id: data.id,
                type: data.type,
                title: data.title,
                message: data.message,
                action_url: data.action_url,
                is_read: false,
                data: data.data,
                created_at_formatted: 'Agora'
              }
              notifications.value.unshift(newNotif)
              unreadCount.value += 1

              // Show toast only for truly new notifications (not already loaded ones)
              showToastForNotification(data)
            }
          }
        } catch (error) {
          console.error('Error parsing SSE message:', error)
        }
      })

      sseConnection.onerror = (error) => {
        console.error('SSE connection error:', {
          readyState: sseConnection?.readyState,
          error: error,
          retryCount: sseRetryCount,
          maxRetries: MAX_SSE_RETRIES
        })
        closeSSEStream()
        
        if (sseRetryCount < MAX_SSE_RETRIES) {
          sseRetryCount++
          // Exponential backoff: 10s, 20s, 40s, 80s, 160s
          const retryDelay = SSE_BASE_RETRY_INTERVAL * Math.pow(2, sseRetryCount - 1)
          console.log(`Retrying SSE in ${retryDelay / 1000}s (attempt ${sseRetryCount}/${MAX_SSE_RETRIES})`)
          setTimeout(() => startSSEStream(), retryDelay)
        } else {
          console.error('Max SSE retries reached, giving up')
        }
      }
    } catch (error) {
      console.error('Error starting SSE stream:', error)
    }
  }

  /**
   * Close SSE stream and reset retry counter
   */
  function closeSSEStream() {
    if (sseConnection) {
      sseConnection.close()
      sseConnection = null
    }
  }

  /**
   * Show toast for notification based on type - with deduplication
   */
  function showToastForNotification(notification: any) {
    const toastOptions = {
      duration: 4000,
      closeButton: true,
      onClick: () => {
        if (notification.action_url) {
          window.location.hash = `#${notification.action_url}`
        }
      }
    }

    switch (notification.type) {
      case 'opportunity_created':
        toast.info(notification.title, toastOptions)
        break
      case 'opportunity_status_changed':
        toast.success(notification.title, toastOptions)
        break
      case 'opportunity_value_changed':
        toast.warning(notification.title, toastOptions)
        break
      default:
        toast.info(notification.title, toastOptions)
    }
  }

  /**
   * Cleanup
   */
  function cleanup() {
    closeSSEStream()
  }

  return {
    notifications,
    unreadCount,
    init,
    loadNotifications,
    getUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    startSSEStream,
    closeSSEStream,
    cleanup
  }
}
