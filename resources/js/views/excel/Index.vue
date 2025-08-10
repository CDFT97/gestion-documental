<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">
        Gestión de Tablas Excel
      </h1>
      <p class="mt-2 text-gray-600">
        Sube archivos Excel para crear tablas dinámicas y gestionar datos
      </p>
    </div>

    <div class="mb-12">
      <ExcelUploader @upload-success="handleUploadSuccess" />
    </div>

    <div>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">
          Mis Tablas ({{ totalTables }})
        </h2>

        <div class="flex items-center space-x-4">
          <div class="relative">
            <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Buscar tablas..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
          </div>

          <button @click="refreshTables" :disabled="loading" class="btn-secondary">
            <ArrowPathIcon :class="['h-4 w-4', loading && 'animate-spin']" />
          </button>
        </div>
      </div>

      <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error al cargar las tablas</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <div class="mt-3">
              <button @click="refreshTables" class="btn-primary">
                Reintentar
              </button>
            </div>
          </div>
        </div>
      </div>

      <TablesList :tables="tables" :loading="loading" @refresh="refreshTables" @delete="handleDeleteTable"
        @page-change="handlePageChange" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import {
  MagnifyingGlassIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useTables } from '@/composables/useTables'
import ExcelUploader from '@/components/excel/ExcelUploader.vue'
import TablesList from '@/components/excel/TablesList.vue'

const {
  tables,
  loading,
  error,
  hasTables,
  totalTables,
  loadTables,
  deleteTable,
  searchTables,
  clearErrors
} = useTables()

const searchQuery = ref('')
const searchTimeout = ref(null)

const refreshTables = async () => {
  clearErrors()
  await loadTables()
}

const handleDeleteTable = async (tableId) => {
  const result = await deleteTable(tableId)
  if (result.success) {
    if (tables.value?.data?.length === 0 && tables.value?.current_page > 1) {
      await loadTables(tables.value.current_page - 1)
    }
  }
}

const handleUploadSuccess = async (uploadedTable) => {
  await refreshTables()
}

const handlePageChange = async (url) => {
  const urlObj = new URL(url)
  const page = urlObj.searchParams.get('page')

  if (searchQuery.value) {
    await searchTables(searchQuery.value, parseInt(page))
  } else {
    await loadTables(parseInt(page))
  }
}

// Search with debounce
const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    if (searchQuery.value.trim()) {
      await searchTables(searchQuery.value.trim())
    } else {
      await loadTables()
    }
  }, 300)
}

onMounted(() => {
  loadTables()
  document.title = 'Gestión de Tablas - Gestión Documental'
})

onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium p-2 rounded-lg transition-colors duration-200;
}

.btn-primary:disabled,
.btn-secondary:disabled {
  @apply opacity-50 cursor-not-allowed;
}
</style>