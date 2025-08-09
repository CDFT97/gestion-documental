<!-- resources/js/views/auth/components/Footer.vue -->
<template>
  <footer class="mt-8 text-center">
    <!-- Copyright -->
    <div class="text-sm text-gray-500 mb-4">
      <p>© {{ currentYear }} Sistema de Gestión Documental</p>
    </div>

    <!-- Links útiles -->
    <div class="flex justify-center space-x-6 text-xs text-gray-400">
      <a href="#" class="hover:text-gray-600 transition-colors">
        Términos de Servicio
      </a>
      <a href="#" class="hover:text-gray-600 transition-colors">
        Política de Privacidad
      </a>
      <a href="#" class="hover:text-gray-600 transition-colors">
        Soporte
      </a>
    </div>

    <!-- Version info (solo en desarrollo) -->
    <div v-if="isDevelopment" class="mt-4 text-xs text-gray-400">
      <p>Versión: {{ version }} | Entorno: {{ environment }}</p>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())

const isDevelopment = computed(() => {
  return import.meta.env.DEV
})

const environment = computed(() => {
  return import.meta.env.MODE
})

const version = computed(() => {
  return import.meta.env.VITE_APP_VERSION || '1.0.0'
})
</script>

<style scoped>
footer {
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>