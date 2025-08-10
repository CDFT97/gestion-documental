<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Documentos</h3>
        <div class="flex items-center space-x-2">
          <!-- Vista de lista/grid toggle -->
          <div class="flex bg-gray-100 rounded-lg p-1">
            <button @click="viewMode = 'list'" :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewMode === 'list'
                ? 'bg-white text-gray-900 shadow-sm'
                : 'text-gray-500 hover:text-gray-700'
            ]">
              <Bars3Icon class="h-4 w-4" />
            </button>
            <button @click="viewMode = 'grid'" :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewMode === 'grid'
                ? 'bg-white text-gray-900 shadow-sm'
                : 'text-gray-500 hover:text-gray-700'
            ]">
              <Squares2X2Icon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="p-6">
      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <LoadingSpinner size="lg" color="text-primary-600" />
        <span class="ml-3 text-gray-600">Cargando documentos...</span>
      </div>

      <!-- Sin documentos -->
      <div v-else-if="!documents || documents.length === 0" class="text-center py-12">
        <DocumentIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay documentos</h3>
        <p class="text-gray-500 mb-6">
          Sube tu primer documento PDF para comenzar
        </p>
        <button @click="$emit('refresh')" class="btn-primary">
          <ArrowPathIcon class="h-4 w-4 mr-2" />
          Actualizar
        </button>
      </div>

      <!-- Lista de documentos -->
      <div v-else>
        <!-- Vista de lista -->
        <div v-if="viewMode === 'list'" class="space-y-4">
          <div v-for="document in documents" :key="document.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4 flex-1">
                <div class="flex-shrink-0">
                  <DocumentIcon class="h-10 w-10 text-red-600" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-lg font-medium text-gray-900 truncate">
                    {{ document.name || document.original_filename }}
                  </h4>
                  <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500">
                    <span>{{ formatFileSize(document.file_size) }}</span>
                    <span>•</span>
                    <span>{{ formatDate(document.created_at) }}</span>
                    <span v-if="document.category"
                      class="bg-primary-100 text-primary-800 px-2 py-1 rounded-full text-xs">
                      {{ document.category }}
                    </span>
                  </div>
                  <p v-if="document.description" class="text-sm text-gray-600 mt-1 truncate">
                    {{ document.description }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button @click="$emit('view', document)" class="btn-icon text-blue-600 hover:bg-blue-50"
                  title="Ver documento">
                  <EyeIcon class="h-4 w-4" />
                </button>
                <button @click="$emit('edit', document)" class="btn-icon text-gray-600 hover:bg-gray-50" title="Editar">
                  <PencilIcon class="h-4 w-4" />
                </button>
                <button @click="handleDelete(document)" class="btn-icon text-red-600 hover:bg-red-50" title="Eliminar">
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Vista de grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="document in documents" :key="document.id"
            class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
            <!-- Preview area -->
            <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center cursor-pointer"
              @click="$emit('view', document)">
              <DocumentIcon class="h-16 w-16 text-red-600" />
            </div>

            <!-- Información del documento -->
            <div class="p-4">
              <h4 class="font-medium text-gray-900 truncate mb-1" :title="document.name || document.original_filename">
                {{ document.name || document.original_filename }}
              </h4>
              <div class="text-sm text-gray-500 mb-2">
                <div class="flex items-center justify-between">
                  <span>{{ formatFileSize(document.file_size) }}</span>
                  <span v-if="document.category" class="bg-primary-100 text-primary-800 px-2 py-1 rounded-full text-xs">
                    {{ document.category }}
                  </span>
                </div>
                <div class="mt-1">
                  {{ formatDate(document.created_at) }}
                </div>
              </div>

              <p v-if="document.description" class="text-xs text-gray-600 mb-3 line-clamp-2">
                {{ document.description }}
              </p>

              <!-- Botones de acción -->
              <div class="flex items-center justify-between">
                <button @click="$emit('view', document)" class="btn-small text-blue-600 hover:bg-blue-50">
                  <EyeIcon class="h-3 w-3 mr-1" />
                  Ver
                </button>

                <div class="flex items-center space-x-1">
                  <button @click="$emit('edit', document)" class="btn-icon-small text-gray-600 hover:bg-gray-50"
                    title="Editar">
                    <PencilIcon class="h-3 w-3" />
                  </button>
                  <button @click="handleDelete(document)" class="btn-icon-small text-red-600 hover:bg-red-50"
                    title="Eliminar">
                    <TrashIcon class="h-3 w-3" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-8">
          <nav class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:hidden">
              <button @click="$emit('page-change', pagination.prev_page_url)" :disabled="!pagination.prev_page_url"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Anterior
              </button>
              <button @click="$emit('page-change', pagination.next_page_url)" :disabled="!pagination.next_page_url"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Siguiente
              </button>
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Mostrando
                  <span class="font-medium">{{ pagination.from }}</span>
                  a
                  <span class="font-medium">{{ pagination.to }}</span>
                  de
                  <span class="font-medium">{{ pagination.total }}</span>
                  documentos
                </p>
              </div>

              <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                  <!-- Botón anterior -->
                  <button @click="$emit('page-change', pagination.prev_page_url)" :disabled="!pagination.prev_page_url"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed">
                    <ChevronLeftIcon class="h-5 w-5" />
                  </button>

                  <!-- Números de página -->
                  <template v-for="page in getVisiblePages()" :key="page">
                    <button v-if="page !== '...'" @click="goToPage(page)" :class="[
                      'relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
                      page === pagination.current_page
                        ? 'z-10 bg-primary-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600'
                        : 'text-gray-900'
                    ]">
                      {{ page }}
                    </button>
                    <span v-else
                      class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                      ...
                    </span>
                  </template>

                  <!-- Botón siguiente -->
                  <button @click="$emit('page-change', pagination.next_page_url)" :disabled="!pagination.next_page_url"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed">
                    <ChevronRightIcon class="h-5 w-5" />
                  </button>
                </nav>
              </div>
            </div>
          </nav>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <ConfirmDialog v-if="showDeleteModal"
      :title="`Eliminar ${documentToDelete?.name || documentToDelete?.original_filename}`"
      :message="`¿Estás seguro de que quieres eliminar el documento '${documentToDelete?.name || documentToDelete?.original_filename}'? Esta acción no se puede deshacer.`"
      confirm-text="Eliminar" cancel-text="Cancelar" danger :loading="deleting" @confirm="confirmDelete"
      @cancel="cancelDelete" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  DocumentIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  ArrowPathIcon,
  Bars3Icon,
  Squares2X2Icon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import { formatDate } from '@/utils/formatters'
import { formatFileSize } from '@/utils/documentUtils'

// Props
const props = defineProps({
  documents: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['refresh', 'delete', 'page-change', 'view', 'edit'])

// Estado local
const viewMode = ref('list') // 'list' o 'grid'
const showDeleteModal = ref(false)
const documentToDelete = ref(null)
const deleting = ref(false)

// Métodos
const handleDelete = (document) => {
  documentToDelete.value = document
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!documentToDelete.value) return

  deleting.value = true
  try {
    emit('delete', documentToDelete.value.id)
  } finally {
    deleting.value = false
    cancelDelete()
  }
}

const cancelDelete = () => {
  documentToDelete.value = null
  showDeleteModal.value = false
}

const goToPage = (page) => {
  if (page !== props.pagination?.current_page) {
    const url = new URL(window.location.origin)
    url.searchParams.set('page', page)
    emit('page-change', url.toString())
  }
}

const getVisiblePages = () => {
  if (!props.pagination) return []

  const current = props.pagination.current_page
  const last = props.pagination.last_page
  const pages = []

  if (last <= 7) {
    // Mostrar todas las páginas si son pocas
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    // Mostrar páginas con elipsis
    pages.push(1)

    if (current > 4) {
      pages.push('...')
    }

    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    if (current < last - 3) {
      pages.push('...')
    }

    if (last > 1) {
      pages.push(last)
    }
  }

  return pages
}
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center;
}

.btn-icon {
  @apply p-2 rounded-lg transition-colors duration-200;
}

.btn-icon-small {
  @apply p-1 rounded transition-colors duration-200;
}

.btn-small {
  @apply text-xs font-medium py-1 px-2 rounded transition-colors duration-200 flex items-center;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>