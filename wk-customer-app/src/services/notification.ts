// src/services/notification.ts

import { ref, Ref } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

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

export function useNotificationService() {
  const toast = useToast()
  const apiUrl = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '')

  /**
   * Initialize notification service
   */
  function init() {
    loadNotifications()
    startSSEStream()
  }

  /**
   * Load notifications from API
   */
  async function loadNotifications() {
    try {
      const response = await axios.get(`${apiUrl}/notifications?limit=20`)
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
      const response = await axios.get(`${apiUrl}/notifications/unread-count`)
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
      const response = await axios.put(`${apiUrl}/notifications/${notificationId}/read`)
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
      const response = await axios.post(`${apiUrl}/notifications/read-all`)
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
      const response = await axios.delete(`${apiUrl}/notifications/${notificationId}`)
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
      const token = localStorage.getItem('auth_token')
      const streamUrl = new URL(`${apiUrl}/notifications/stream`)
      if (token) {
        streamUrl.searchParams.set('token', token)
      }

      sseConnection = new EventSource(streamUrl.toString())

      sseConnection.addEventListener('message', (event) => {
        try {
          const data = JSON.parse(event.data)

          if (data.type === 'connected') {
            console.log('SSE connected for user:', data.user_id)
          } else if (data.type === 'heartbeat') {
            unreadCount.value = data.unread_count || 0
          } else if (data.type === 'notification') {
            // New real-time notification received
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

            // Show toast notification
            showToastForNotification(data)
          }
        } catch (error) {
          console.error('Error parsing SSE message:', error)
        }
      })

      sseConnection.onerror = () => {
        console.warn('SSE connection error, will retry in 5 seconds...')
        closeSSEStream()
        setTimeout(() => startSSEStream(), 5000)
      }
    } catch (error) {
      console.error('Error starting SSE stream:', error)
    }
  }

  /**
   * Close SSE stream
   */
  function closeSSEStream() {
    if (sseConnection) {
      sseConnection.close()
      sseConnection = null
    }
  }

  /**
   * Show toast for notification based on type
   */
  function showToastForNotification(notification: any) {
    const toastOptions = {
      duration: 5000,
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
        toast.default(notification.title, toastOptions)
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
