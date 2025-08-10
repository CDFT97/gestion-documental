<!-- resources/js/components/excel/TablesList.vue -->
<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="space-y-2">
            <div class="h-3 bg-gray-200 rounded"></div>
            <div class="h-3 bg-gray-200 rounded w-5/6"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!tables?.data?.length" class="text-center py-12">
      <div class="text-6xl mb-4">📊</div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        No tienes tablas creadas
      </h3>
      <p class="text-gray-500 mb-6">
        Sube tu primer archivo Excel para comenzar
      </p>
      <button @click="scrollToUpload" class="btn-primary">
        Subir archivo Excel
      </button>
    </div>

    <!-- Tables Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="table in tables.data" :key="table.id"
        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 relative group">
        <!-- Table Header -->
        <div class="p-6 pb-4">
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 truncate">
                {{ table.original_filename }}
              </h3>
              <p class="text-sm text-gray-500 mt-1">
                Tabla: {{ table.name }}
              </p>
            </div>

            <span :class="getStatusBadge(table.status)">
              {{ getStatusText(table.status) }}
            </span>
          </div>
        </div>

        <!-- Table Stats -->
        <div class="px-6 pb-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-primary-600">
                {{ table.total_records?.toLocaleString() || 0 }}
              </div>
              <div class="text-xs text-gray-500">Registros</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">
                {{ table.columns?.length || 0 }}
              </div>
              <div class="text-xs text-gray-500">Columnas</div>
            </div>
          </div>
        </div>

        <!-- Table Metadata -->
        <div class="px-6 pb-4">
          <div class="text-xs text-gray-500 space-y-1">
            <div class="flex justify-between">
              <span>Creado:</span>
              <span>{{ formatDate(table.created_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tamaño:</span>
              <span>{{ formatFileSize(table.metadata?.file_size) }}</span>
            </div>
          </div>
        </div>

        <!-- Table Actions -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <router-link :to="`/tables/${table.id}`" :class="[
              'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md transition-colors',
              table.status === 'completed'
                ? 'text-primary-700 bg-primary-100 hover:bg-primary-200'
                : 'text-gray-400 bg-gray-100 cursor-not-allowed'
            ]" :disabled="table.status !== 'completed'">
              <EyeIcon class="h-4 w-4 mr-1" />
              Ver datos
            </router-link>

            <!-- Dropdown Menu -->
            <div class="relative" ref="dropdownRef">
              <button @click="toggleDropdown(table.id)" class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                :disabled="table.status === 'processing'">
                <EllipsisVerticalIcon class="h-5 w-5" />
              </button>

              <transition enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95">
                <div v-if="openDropdown === table.id"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                  <router-link :to="`/tables/${table.id}`"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="closeDropdown">
                    <EyeIcon class="h-4 w-4 mr-3" />
                    Ver tabla
                  </router-link>

                  <button @click="handleExportTable(table)"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    :disabled="table.status !== 'completed'">
                    <ArrowDownTrayIcon class="h-4 w-4 mr-3" />
                    Exportar
                  </button>

                  <hr class="my-1" />

                  <button @click="confirmDelete(table)"
                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <TrashIcon class="h-4 w-4 mr-3" />
                    Eliminar
                  </button>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Processing Overlay -->
        <div v-if="table.status === 'processing'"
          class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center">
          <div class="text-center">
            <LoadingSpinner class="mx-auto mb-2" />
            <p class="text-sm text-gray-600">Procesando...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="tables?.links && tables.last_page > 1" class="mt-8">
      <Pagination :links="tables" @page-change="handlePageChange" />
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog v-if="showDeleteModal" title="Eliminar tabla"
      :message="`¿Estás seguro de que quieres eliminar la tabla '${tableToDelete?.original_filename}'? Esta acción no se puede deshacer.`"
      confirm-text="Eliminar" cancel-text="Cancelar" danger @confirm="handleDelete" @cancel="cancelDelete" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import {
  EyeIcon,
  EllipsisVerticalIcon,
  TrashIcon,
  ArrowDownTrayIcon,
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline'
import { useTables } from '@/composables/useTables'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import  { formatDate } from '@/utils/formatters'

const props = defineProps({
  tables: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['refresh', 'delete', 'page-change'])

// Use tables composable for additional operations
const { exportTable, duplicateTable } = useTables()

// Local state
const openDropdown = ref(null)
const showDeleteModal = ref(false)
const tableToDelete = ref(null)

// Methods
const getStatusBadge = (status) => {
  const badges = {
    completed: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    processing: 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full',
    failed: 'px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full'
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

const formatFileSize = (bytes) => {
  if (!bytes) return 'N/A'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Dropdown management
const toggleDropdown = (tableId) => {
  openDropdown.value = openDropdown.value === tableId ? null : tableId
}

const closeDropdown = () => {
  openDropdown.value = null
}

// Calcular posición del dropdown según el espacio disponible
const getDropdownPosition = (tableId) => {
  // Por defecto, mostrar hacia arriba en las cards (ya que están en la parte inferior)
  return 'bottom-full mb-2'
}

// Table actions
const handleExportTable = async (table) => {
  closeDropdown()
  await exportTable(table.id)
}

// Delete operations
const confirmDelete = (table) => {
  tableToDelete.value = table
  showDeleteModal.value = true
  closeDropdown()
}

const handleDelete = () => {
  emit('delete', tableToDelete.value.id)
  cancelDelete()
}

const cancelDelete = () => {
  showDeleteModal.value = false
  tableToDelete.value = null
}

// Navigation
const scrollToUpload = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handlePageChange = (url) => {
  emit('page-change', url)
}

// Click outside to close dropdown
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    closeDropdown()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

.group:hover {
  transform: translateY(-2px);
}
</style>