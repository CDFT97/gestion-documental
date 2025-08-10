<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">
        Gestión de Documentos PDF
      </h1>
      <p class="mt-2 text-gray-600">
        Sube archivos PDF para almacenar, organizar y visualizar documentos
      </p>
    </div>

    <div class="mb-12">
      <DocumentUploader @upload-success="handleUploadSuccess" />
    </div>

    <div>
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 space-y-4 lg:space-y-0">
        <h2 class="text-xl font-semibold text-gray-900">
          Mis Documentos ({{ totalDocuments }})
        </h2>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
          <!-- Buscador -->
          <div class="relative">
            <input v-model="searchQuery" @input="handleSearch" type="text"
              placeholder="Buscar documentos..."
              class="pl-10 pr-4 py-2 w-full sm:w-80 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
          </div>

          <!-- Filtros -->
          <div class="flex items-center space-x-2">
            <!-- Filtro por categoría -->
            <select v-model="selectedCategory" @change="handleCategoryFilter"
              class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
              <option value="">Todas las categorías</option>
              <option v-for="category in categories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>

            <!-- Botón de actualizar -->
            <button @click="refreshDocuments" :disabled="loading" class="btn-secondary" title="Actualizar">
              <ArrowPathIcon :class="['h-4 w-4', loading && 'animate-spin']" />
            </button>

            <!-- Botón de limpiar filtros -->
            <button v-if="hasActiveFilters" @click="clearAllFilters" class="btn-secondary" title="Limpiar filtros">
              <XMarkIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Filtros de fecha -->
      <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
          <label class="text-sm font-medium text-gray-700">Filtrar por fecha:</label>
          <div class="flex items-center space-x-2">
            <input v-model="dateFrom" @change="handleDateFilter" type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Desde" />
            <span class="text-gray-500">-</span>
            <input v-model="dateTo" @change="handleDateFilter" type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Hasta" />
          </div>
        </div>
      </div>

      <!-- Mensaje de error -->
      <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error al cargar los documentos</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <div class="mt-3">
              <button @click="refreshDocuments" class="btn-primary">
                Reintentar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de documentos -->
      <DocumentsList :documents="documents" :loading="loading" :pagination="pagination" @refresh="refreshDocuments"
        @delete="handleDeleteDocument" @page-change="handlePageChange" @view="handleViewDocument"
        @edit="handleEditDocument" />
    </div>

    <!-- Modal de visualización -->
    <DocumentViewer v-if="selectedDocument" :document="selectedDocument" :show="showViewer" @close="closeViewer"
      @updated="handleDocumentUpdated" />

    <!-- Modal de edición -->
    <DocumentEditModal v-if="editingDocument" :document="editingDocument" :show="showEditModal" @close="closeEditModal"
      @updated="handleDocumentUpdated" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  MagnifyingGlassIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import { useDocuments } from '@/composables/useDocuments'
import DocumentUploader from '@/views/documents/components/DocumentUploader.vue'
import DocumentsList from '@/views/documents/components/DocumentList.vue'
import DocumentViewer from '@/views/documents/components/DocumentViewer.vue'
import DocumentEditModal from '@/views/documents/components/DocumentEditModal.vue'

const {
  documents,
  loading,
  error,
  categories,
  pagination,
  hasDocuments,
  totalDocuments,
  getDocuments,
  getCategories,
  deleteDocument,
  searchDocuments,
  filterByCategory,
  filterByDateRange,
  clearFilters,
  goToPage,
  clearErrors
} = useDocuments()

// Estado local
const searchQuery = ref('')
const selectedCategory = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const searchTimeout = ref(null)

// Estado de modales
const selectedDocument = ref(null)
const showViewer = ref(false)
const editingDocument = ref(null)
const showEditModal = ref(false)

// Computed
const hasActiveFilters = computed(() => {
  return searchQuery.value || selectedCategory.value || dateFrom.value || dateTo.value
})

// Métodos
const refreshDocuments = async () => {
  clearErrors()
  await getDocuments()
}

const handleDeleteDocument = async (documentId) => {
  const result = await deleteDocument(documentId)
  if (result.success) {
    // Si quedamos sin documentos en la página actual y no es la primera, ir a la anterior
    if (documents.value?.data?.length === 0 && pagination.value?.current_page > 1) {
      await goToPage(pagination.value.current_page - 1)
    }
  }
}

const handleUploadSuccess = async (uploadedDocument) => {
  await refreshDocuments()
  // Actualizar categorías por si se agregó una nueva
  await getCategories()
}

const handlePageChange = async (url) => {
  const urlObj = new URL(url)
  const page = urlObj.searchParams.get('page')
  await goToPage(parseInt(page))
}

const handleViewDocument = (document) => {
  selectedDocument.value = document
  showViewer.value = true
}

const handleEditDocument = (document) => {
  editingDocument.value = document
  showEditModal.value = true
}

const closeViewer = () => {
  selectedDocument.value = null
  showViewer.value = false
}

const closeEditModal = () => {
  editingDocument.value = null
  showEditModal.value = false
}

const handleDocumentUpdated = async (updatedDocument) => {
  await refreshDocuments()
  closeViewer()
  closeEditModal()
}

// Búsqueda con debounce
const handleSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    const currentSearchValue = searchQuery.value.trim()
    
    if (currentSearchValue) {
      await searchDocuments(currentSearchValue)
    } else {
      // Si está vacío, usar searchDocuments con string vacío para limpiar el filtro
      await searchDocuments('')
    }
  }, 500)
}

// Filtro por categoría
const handleCategoryFilter = async () => {
  if (selectedCategory.value) {
    await filterByCategory(selectedCategory.value)
  } else {
    await filterByCategory('')
  }
}

// Filtro por fecha
const handleDateFilter = async () => {
  if (dateFrom.value || dateTo.value) {
    await filterByDateRange(dateFrom.value, dateTo.value)
  } else {
   await filterByDateRange('', '')
  }
}

// Limpiar todos los filtros
const clearAllFilters = async () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  clearFilters()
  await getDocuments()
}

// Lifecycle
onMounted(async () => {
  await getDocuments()
  await getCategories()
  document.title = 'Gestión de Documentos - Gestión Documental'
})

onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})
</script>