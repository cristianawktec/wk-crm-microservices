<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-indigo-700 text-white transform transition-transform duration-300 ease-in-out z-30 lg:translate-x-0"
          :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
      <div class="flex items-center justify-between p-4 border-b border-indigo-600">
        <h1 class="text-xl font-bold">WK CRM</h1>
        <button @click="sidebarOpen = false" class="lg:hidden">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <nav class="p-4 space-y-2">
        <router-link
          v-for="item in menuItems"
          :key="item.name"
          :to="item.path"
          class="flex items-center px-4 py-3 rounded-lg transition-colors"
          :class="$route.name === item.name ? 'bg-indigo-800' : 'hover:bg-indigo-600'"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          {{ item.label }}
        </router-link>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
      <!-- Header -->
      <header class="bg-white shadow-sm sticky top-0 z-20">
        <div class="flex items-center justify-between px-4 py-3">
          <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <div class="flex-1 lg:ml-4">
            <h2 class="text-xl font-semibold text-gray-800">{{ pageTitle }}</h2>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <NotificationBell />

            <!-- User Menu -->
            <div class="relative" @click="userMenuOpen = !userMenuOpen" @blur="userMenuOpen = false" tabindex="0">
              <button class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg">
                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ userInitials }}
                </div>
                <span class="hidden lg:block text-sm font-medium text-gray-700">{{ authStore.user?.name }}</span>
              </button>
              
              <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                <router-link to="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Meu Perfil
                </router-link>
                <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Sair
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <router-view />
      </main>
    </div>

    <!-- Overlay for mobile -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import NotificationBell from '../NotificationBell.vue'
import { 
  HomeIcon, 
  ChartBarIcon, 
  UserCircleIcon,
  BellIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const sidebarOpen = ref(false)
const userMenuOpen = ref(false)

const menuItems = [
  { name: 'Dashboard', path: '/', label: 'Dashboard', icon: HomeIcon },
  { name: 'Opportunities', path: '/opportunities', label: 'Oportunidades', icon: ChartBarIcon },
  { name: 'Notifications', path: '/notifications', label: 'Notificações', icon: BellIcon },
  { name: 'Profile', path: '/profile', label: 'Meu Perfil', icon: UserCircleIcon }
]

const pageTitle = computed(() => {
  const item = menuItems.find(m => m.name === route.name)
  return item?.label || 'Dashboard'
})

const userInitials = computed(() => {
  const name = authStore.user?.name || 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
