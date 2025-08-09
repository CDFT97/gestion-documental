<template>
  <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <router-link to="/dashboard" class="flex items-center">
            <div class="text-2xl mr-3">📄</div>
            <span class="text-xl font-bold text-primary-600">
              Gestión Documental
            </span>
          </router-link>

          <!-- Navigation Links (Desktop) -->
          <div class="hidden md:flex items-center ml-10 space-x-8">
            <NavLink to="/dashboard" icon="🏠">
              Dashboard
            </NavLink>

            <NavLink to="/tables" icon="📊">
              Tablas
            </NavLink>

            <NavLink to="/documents" icon="📄">
              Documentos
            </NavLink>
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <button class="p-2 text-gray-400 hover:text-gray-600 relative" title="Notificaciones">
            <BellIcon class="h-6 w-6" />
            <span
              class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              3
            </span>
          </button>

          <div class="relative" ref="userMenuRef">
            <button @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ userInitials }}
                </span>
              </div>

              <div class="hidden sm:block text-left">
                <div class="text-sm font-medium text-gray-900">
                  {{ user?.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ user?.email }}
                </div>
              </div>

              <ChevronDownIcon :class="[
                'h-4 w-4 text-gray-400 transition-transform duration-200',
                showUserMenu ? 'rotate-180' : ''
              ]" />
            </button>

            <transition enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95">
              <div v-if="showUserMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                <router-link to="/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserMenu = false">
                  <UserIcon class="h-4 w-4 mr-3" />
                  Mi Perfil
                </router-link>

                <router-link to="/settings" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserMenu = false">
                  <CogIcon class="h-4 w-4 mr-3" />
                  Configuración
                </router-link>

                <hr class="my-1" />

                <button @click="handleLogout" :disabled="loading"
                  class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 disabled:opacity-50">
                  <ArrowRightOnRectangleIcon class="h-4 w-4 mr-3" />
                  {{ loading ? 'Cerrando...' : 'Cerrar Sesión' }}
                </button>
              </div>
            </transition>
          </div>
        </div>

        <div class="md:hidden flex items-center">
          <button @click="showMobileMenu = !showMobileMenu"
            class="text-gray-400 hover:text-gray-600 focus:outline-none p-2">
            <Bars3Icon v-if="!showMobileMenu" class="h-6 w-6" />
            <XMarkIcon v-else class="h-6 w-6" />
          </button>
        </div>
      </div>

      <transition enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0">
        <div v-if="showMobileMenu" class="md:hidden border-t border-gray-200">
          <div class="px-2 pt-2 pb-3 space-y-1">
            <MobileNavLink to="/dashboard" icon="🏠" @click="showMobileMenu = false">
              Dashboard
            </MobileNavLink>

            <MobileNavLink to="/tables" icon="📊" @click="showMobileMenu = false">
              Tablas
            </MobileNavLink>

            <MobileNavLink to="/documents" icon="📄" @click="showMobileMenu = false">
              Documentos
            </MobileNavLink>

            <MobileNavLink to="/profile" icon="👤" @click="showMobileMenu = false">
              Mi Perfil
            </MobileNavLink>
          </div>
        </div>
      </transition>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import {
  BellIcon,
  ChevronDownIcon,
  UserIcon,
  CogIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import NavLink from './NavLink.vue'
import MobileNavLink from './MobileNavLink.vue'

const router = useRouter()
const { user, logout, loading } = useAuth()

const showUserMenu = ref(false)
const showMobileMenu = ref(false)
const userMenuRef = ref(null)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const handleLogout = async () => {
  showUserMenu.value = false
  await logout()
  router.push('/login')
}

const handleClickOutside = (event) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.router-link-active {
  @apply text-primary-600 bg-primary-50;
}

/* Badge animation */
@keyframes pulse {

  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.1);
  }
}

.animate-pulse {
  animation: pulse 2s infinite;
}
</style>