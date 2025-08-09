<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">
        ¡Bienvenido, {{ user?.name }}! 👋
      </h1>
      <p class="mt-2 text-gray-600">
        Gestiona tus documentos y tablas desde este panel central
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatsCard title="Tablas Excel" :value="stats.tables" icon="📊" color="blue" description="Tablas cargadas" />

      <StatsCard title="Documentos PDF" :value="stats.documents" icon="📄" color="green"
        description="PDFs almacenados" />

      <StatsCard title="Registros" :value="stats.records" icon="📝" color="purple" description="Total de datos" />

      <StatsCard title="Almacenamiento" :value="stats.storage" icon="💾" color="orange" description="Espacio usado" />
    </div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Upload Excel Card -->
      <ActionCard title="Cargar Archivo Excel"
        description="Sube un archivo Excel para crear tablas dinámicas y gestionar datos" icon="📊" color="blue"
        button-text="Subir Excel" @action="navigateTo('/tables')" />

      <!-- Upload PDF Card -->
      <ActionCard title="Subir Documento PDF" description="Almacena y visualiza documentos PDF de forma segura"
        icon="📄" color="green" button-text="Subir PDF" @action="navigateTo('/documents')" />
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">
        Actividad Reciente
      </h2>

      <div class="space-y-4">
        <ActivityItem v-for="activity in recentActivity" :key="activity.id" :activity="activity" />

        <div v-if="recentActivity.length === 0" class="text-center py-8 text-gray-500">
          <div class="text-4xl mb-2">📋</div>
          <p>No hay actividad reciente</p>
          <p class="text-sm">Comienza subiendo un archivo Excel o PDF</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import StatsCard from '@/components/dashboard/StatsCard.vue'
import ActionCard from '@/components/dashboard/ActionCard.vue'
import ActivityItem from '@/components/dashboard/ActivityItem.vue'

const router = useRouter()
const { user } = useAuth()

const stats = ref({
  tables: 0,
  documents: 0,
  records: 0,
  storage: '0 MB'
})

const recentActivity = ref([])

const navigateTo = (path) => {
  router.push(path)
}

onMounted(() => {
  document.title = 'Dashboard - Gestión Documental'

  // TODO: Cargar stats reales desde API
  // loadDashboardStats()
})
</script>

<style scoped>
.grid>* {
  animation: fadeInUp 0.6s ease-out;
}

.grid>*:nth-child(1) {
  animation-delay: 0.1s;
}

.grid>*:nth-child(2) {
  animation-delay: 0.2s;
}

.grid>*:nth-child(3) {
  animation-delay: 0.3s;
}

.grid>*:nth-child(4) {
  animation-delay: 0.4s;
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