import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

import App from './App.vue'
import router from './router'
import './services/api'
import './style.css'
import { useNotificationService } from './services/notification'
import { useAuthStore } from './stores/auth'
import { watch } from 'vue'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(Toast, {
  position: 'top-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true
})

app.mount('#app')

// Initialize notification service when auth is ready
const pinia = app.config.globalProperties.$pinia || app._context.provides['pinia']
setTimeout(() => {
  const authStore = useAuthStore()
  console.log('📱 Main.ts: Checking auth status...', {
    isAuthenticated: authStore.isAuthenticated,
    hasToken: !!authStore.token,
    hasUser: !!authStore.user
  })
  
  if (authStore.isAuthenticated) {
    console.log('📱 Main.ts: Auth OK, initializing NotificationService')
    const notificationService = useNotificationService()
    notificationService.init()
  } else {
    console.log('📱 Main.ts: Not authenticated yet, waiting...')
    // Watch for auth changes
    watch(
      () => authStore.isAuthenticated,
      (isAuthenticated) => {
        if (isAuthenticated) {
          console.log('📱 Main.ts: Auth changed to authenticated, initializing NotificationService')
          const notificationService = useNotificationService()
          notificationService.init()
        }
      }
    )
  }
}, 1000)
