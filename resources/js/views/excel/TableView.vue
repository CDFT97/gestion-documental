<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
              <li>
                <router-link to="/dashboard" class="text-gray-400 hover:text-gray-500">
                  <HomeIcon class="flex-shrink-0 h-5 w-5" />
                </router-link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <router-link to="/tables" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Tablas
                  </router-link>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span class="ml-4 text-sm font-medium text-gray-900">
                    {{ currentTable?.original_filename || 'Cargando...' }}
                  </span>
                </div>
              </li>
            </ol>
          </nav>

          <div class="flex items-center space-x-3">
            <button @click="handleRefresh" :disabled="tableLoading" class="btn-secondary" title="Actualizar">
              <ArrowPathIcon :class="['h-4 w-4', tableLoading && 'animate-spin']" />
            </button>

            <button v-if="currentTable" @click="handleExport" class="btn-secondary" title="Exportar tabla">
              <ArrowDownTrayIcon class="h-4 w-4" />
            </button>

            <router-link to="/tables" class="btn-secondary">
              <ArrowLeftIcon class="h-4 w-4 mr-2" />
              Volver
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="tableLoading" class="flex items-center justify-center py-12">
        <LoadingSpinner size="lg" color="text-primary-600" />
        <span class="ml-3 text-gray-600">Cargando tabla...</span>
      </div>

      <div v-else-if="tableError" class="bg-red-50 border border-red-200 rounded-md p-6">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error al cargar la tabla</h3>
            <p class="mt-2 text-sm text-red-700">{{ tableError }}</p>
            <div class="mt-4">
              <button @click="handleRetry" class="btn-primary">
                Reintentar
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="currentTable" class="space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                {{ currentTable.original_filename }}
              </h1>
              <p class="text-gray-600 mt-1">
                Tabla: <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ currentTable.name }}</code>
              </p>
            </div>

            <div class="flex items-center space-x-2">
              <span :class="getStatusBadge(currentTable.status)">
                {{ getStatusText(currentTable.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-primary-50 rounded-lg">
              <div class="text-3xl font-bold text-primary-600">
                {{ currentTable.total_records?.toLocaleString() || 0 }}
              </div>
              <div class="text-sm text-primary-700 font-medium">Total Registros</div>
            </div>

            <div class="text-center p-4 bg-green-50 rounded-lg">
              <div class="text-3xl font-bold text-green-600">
                {{ currentTable.columns?.length || 0 }}
              </div>
              <div class="text-sm text-green-700 font-medium">Columnas</div>
            </div>

            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <div class="text-3xl font-bold text-blue-600">
                {{ formatFileSize(currentTable.metadata?.file_size) }}
              </div>
              <div class="text-sm text-blue-700 font-medium">Tamaño Archivo</div>
            </div>

            <div class="text-center p-4 bg-purple-50 rounded-lg">
              <div class="text-3xl font-bold text-purple-600">
                {{ formatDate(currentTable.created_at) }}
              </div>
              <div class="text-sm text-purple-700 font-medium">Fecha Creación</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Estructura de la Tabla</h3>
            <p class="text-sm text-gray-600 mt-1">
              Definición de columnas y tipos de datos
            </p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Columna
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nullable
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nombre Original
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(column, index) in currentTable.columns" :key="column.name">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <div class="flex items-center">
                      <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs mr-2">
                        {{ index + 1 }}
                      </span>
                      {{ column.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span :class="getTypeClass(column.type)">
                      {{ formatColumnType(column.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span :class="column.nullable ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                      {{ column.nullable ? 'Sí' : 'No' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ column.original_name }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <DataTable :table="currentTable" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  HomeIcon,
  ChevronRightIcon,
  ArrowLeftIcon,
  ArrowPathIcon,
  ArrowDownTrayIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useTables } from '@/composables/useTables'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import DataTable from '@/components/excel/DataTable.vue'
import  { formatDate } from '@/utils/formatters'
const route = useRoute()

const {
  currentTable,
  tableLoading,
  tableError,
  loadTable,
  exportTable,
  refreshTable,
  clearErrors
} = useTables()

const handleRetry = async () => {
  await loadTable(route.params.id)
}

const handleRefresh = async () => {
  await refreshTable()
}

const handleExport = async () => {
  if (currentTable.value?.id) {
    await exportTable(currentTable.value.id)
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'N/A'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatColumnType = (type) => {
  const types = {
    string: 'Texto',
    number: 'Número',
    boolean: 'Booleano',
    date: 'Fecha',
    datetime: 'Fecha/Hora',
    text: 'Texto Largo',
    email: 'Email'
  }
  return types[type] || type
}

const getTypeClass = (type) => {
  const classes = {
    string: 'px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full',
    number: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    boolean: 'px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full',
    date: 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full',
    datetime: 'px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full',
    text: 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full',
    email: 'px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full'
  }
  return classes[type] || classes.string
}

const getStatusBadge = (status) => {
  const badges = {
    completed: 'px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    processing: 'px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full',
    failed: 'px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full'
  }
  return badges[status] || badges.processing
}

const getStatusText = (status) => {
  const texts = {
    completed: 'Completado',
    processing: 'Procesando',
    failed: 'Error'
  }
  return texts[status] || 'Procesando'
}

onMounted(() => {
  loadTable(route.params.id)
  document.title = `Tabla ${route.params.id} - Gestión Documental`
})

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      clearErrors()
      loadTable(newId)
    }
  }
)
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center;
}

.btn-primary:disabled,
.btn-secondary:disabled {
  @apply opacity-50 cursor-not-allowed;
}

code {
  @apply font-mono text-sm;
}
</style>