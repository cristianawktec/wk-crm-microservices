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

// Initialize notification service after app mount
setTimeout(() => {
  const authStore = useAuthStore()
  if (authStore.isAuthenticated) {
    const notificationService = useNotificationService()
    notificationService.init()
  }
}, 500)
