<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="text-6xl mb-4">
          <ExclamationTriangleIcon class="h-20 w-20 text-yellow-400 mx-auto" />
        </div>
      </div>

      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">
          Página no encontrada
        </h2>
        <p class="text-gray-500 mb-8">
          Lo sentimos, la página que buscas no existe o ha sido movida.
        </p>
      </div>

      <div class="space-y-4">
        <button @click="goBack"
          class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
          <ArrowLeftIcon class="h-4 w-4 mr-2" />
          Volver atrás
        </button>

        <router-link :to="dashboardRoute"
          class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-primary-600 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
          <HomeIcon class="h-4 w-4 mr-2" />
          {{ dashboardText }}
        </router-link>
      </div>

      <!-- Enlaces útiles -->
      <div class="mt-8 text-center">
        <p class="text-sm text-gray-500 mb-4">
          O puedes ir a:
        </p>
        <div class="space-y-2">
          <template v-if="isAuthenticated">
            <router-link to="/tables" class="block text-primary-600 hover:text-primary-500 text-sm transition-colors">
              📊 Gestión de Tablas
            </router-link>
            <router-link to="/documents"
              class="block text-primary-600 hover:text-primary-500 text-sm transition-colors">
              📄 Gestión de Documentos
            </router-link>
            <router-link to="/profile" class="block text-primary-600 hover:text-primary-500 text-sm transition-colors">
              👤 Mi Perfil
            </router-link>
          </template>
          <template v-else>
            <router-link to="/login" class="block text-primary-600 hover:text-primary-500 text-sm transition-colors">
              🔐 Iniciar Sesión
            </router-link>
            <router-link to="/register" class="block text-primary-600 hover:text-primary-500 text-sm transition-colors">
              📝 Registrarse
            </router-link>
          </template>
        </div>
      </div>

      <!-- Información adicional -->
      <div class="mt-8 text-center text-xs text-gray-400">
        <p>
          Error 404 - Ruta: <code class="bg-gray-100 px-1 py-0.5 rounded">{{ currentPath }}</code>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import {
  ExclamationTriangleIcon,
  ArrowLeftIcon,
  HomeIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const { isAuthenticated } = useAuth()

const currentPath = computed(() => route.fullPath)

const dashboardRoute = computed(() => {
  return isAuthenticated.value ? '/dashboard' : '/login'
})

const dashboardText = computed(() => {
  return isAuthenticated.value ? 'Ir al Dashboard' : 'Iniciar Sesión'
})

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    router.push(dashboardRoute.value)
  }
}
document.title = 'Página no encontrada - Gestión Documental'
</script>

<style scoped>
/* Animación suave para la entrada */
.fade-enter-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
}

/* Efectos hover mejorados */
button:hover,
a:hover {
  transform: translateY(-1px);
}

button:active,
a:active {
  transform: translateY(0);
}

/* Estilo para el código */
code {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.75rem;
}
</style>