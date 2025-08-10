<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    @click="handleBackdropClick">
    <!-- Modal container -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-7xl h-full max-h-[95vh] flex flex-col" @click.stop>
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <DocumentIcon class="h-6 w-6 text-red-600" />
          <div>
            <h2 class="text-lg font-semibold text-gray-900 truncate max-w-md">
              {{ document.name || document.original_filename }}
            </h2>
            <div class="flex items-center space-x-4 text-sm text-gray-500">
              <span>{{ formatFileSize(document.file_size) }}</span>
              <span v-if="document.category" class="bg-primary-100 text-primary-800 px-2 py-1 rounded-full text-xs">
                {{ document.category }}
              </span>
              <span>{{ formatDate(document.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <!-- Toggle panel info -->
          <button @click="toggleInfoPanel" class="btn-secondary" title="Toggle información">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>

          <!-- Botón de editar -->
          <button @click="handleEdit" class="btn-secondary" title="Editar información">
            <PencilIcon class="h-4 w-4 mr-2" />
            Editar
          </button>

          <!-- Botón de descargar -->
          <button @click="handleDownload" class="btn-secondary" title="Descargar documento">
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            Descargar
          </button>

          <!-- Botón de cerrar -->
          <button @click="$emit('close')" class="btn-secondary" title="Cerrar">
            <XMarkIcon class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="flex-1 flex overflow-hidden">
        <!-- Panel principal del PDF -->
        <div class="flex-1 bg-gray-100">
          <!-- Loading state -->
          <div v-if="loading" class="h-full flex items-center justify-center">
            <div class="text-center">
              <LoadingSpinner size="lg" color="text-primary-600" />
              <p class="mt-2 text-gray-600">Cargando documento...</p>
              <p class="text-xs text-gray-500 mt-1">{{ loadingProgress }}</p>
            </div>
          </div>

          <!-- Error state -->
          <div v-else-if="error" class="h-full flex items-center justify-center">
            <div class="text-center max-w-md p-6">
              <ExclamationTriangleIcon class="h-12 w-12 text-red-500 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Error al cargar el documento</h3>
              <p class="text-gray-600 mb-4">{{ error }}</p>

              <div class="space-y-2">
                <button @click="loadPdf" class="btn-primary">
                  <ArrowPathIcon class="h-4 w-4 mr-2" />
                  Reintentar
                </button>

                <button @click="openInNewTab" class="btn-secondary">
                  <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  Abrir en nueva pestaña
                </button>
              </div>

              <!-- Debug info -->
              <details class="mt-4 text-left">
                <summary class="text-xs text-gray-500 cursor-pointer">Debug info</summary>
                <pre class="text-xs bg-gray-100 p-2 rounded mt-2 text-left overflow-auto">{{ debugInfo }}</pre>
              </details>
            </div>
          </div>

          <!-- PDF Viewer -->
          <div v-else class="h-full">
            <VPdfViewer :src="pdfUrl" :style="{ width: '100%', height: '100%' }" @loading="handlePdfLoading"
              @loaded="handlePdfLoaded" @error="handlePdfError" />
          </div>
        </div>

        <!-- Panel lateral de información (opcional y colapsable) -->
        <div v-if="showInfoPanel" class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
          <div class="p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900">Información</h3>
              <button @click="toggleInfoPanel" class="btn-icon" title="Ocultar panel">
                <XMarkIcon class="h-4 w-4" />
              </button>
            </div>

            <!-- URL del PDF para debug -->
            <div class="mb-4 p-3 bg-gray-50 rounded text-xs">
              <p class="font-medium mb-1">URL del PDF:</p>
              <a :href="pdfUrl" target="_blank" class="text-blue-600 hover:underline break-all">
                {{ pdfUrl }}
              </a>
              <div class="mt-2">
                <button @click="testPdfUrl" class="btn-secondary text-xs">
                  Probar URL
                </button>
              </div>
            </div>

            <!-- Información básica -->
            <div class="space-y-4">
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Detalles del archivo</h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-500">Nombre original:</span>
                    <span class="text-gray-900 text-right max-w-48 truncate" :title="document.original_filename">
                      {{ document.original_filename }}
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Tamaño:</span>
                    <span class="text-gray-900">{{ formatFileSize(document.file_size) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Tipo:</span>
                    <span class="text-gray-900">{{ document.mime_type || 'application/pdf' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Subido:</span>
                    <span class="text-gray-900">{{ formatDate(document.created_at) }}</span>
                  </div>
                  <div v-if="document.updated_at !== document.created_at" class="flex justify-between">
                    <span class="text-gray-500">Modificado:</span>
                    <span class="text-gray-900">{{ formatDate(document.updated_at) }}</span>
                  </div>
                </div>
              </div>

              <!-- Categoría y descripción -->
              <div v-if="document.category || document.description">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Información adicional</h4>
                <div class="space-y-2 text-sm">
                  <div v-if="document.category">
                    <span class="text-gray-500">Categoría:</span>
                    <span class="ml-2 bg-primary-100 text-primary-800 px-2 py-1 rounded-full text-xs">
                      {{ document.category }}
                    </span>
                  </div>
                  <div v-if="document.description">
                    <span class="text-gray-500">Descripción:</span>
                    <p class="text-gray-900 mt-1">{{ document.description }}</p>
                  </div>
                </div>
              </div>

              <!-- Metadatos del PDF -->
              <div v-if="document.metadata?.pdf_info" class="pt-4 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Información del PDF</h4>
                <div class="space-y-2 text-sm">
                  <div v-if="document.metadata.pdf_info.pages" class="flex justify-between">
                    <span class="text-gray-500">Páginas:</span>
                    <span class="text-gray-900">{{ document.metadata.pdf_info.pages }}</span>
                  </div>
                  <div v-if="document.metadata.pdf_info.title" class="flex justify-between">
                    <span class="text-gray-500">Título:</span>
                    <span class="text-gray-900 text-right max-w-32 truncate" :title="document.metadata.pdf_info.title">
                      {{ document.metadata.pdf_info.title }}
                    </span>
                  </div>
                  <div v-if="document.metadata.pdf_info.author" class="flex justify-between">
                    <span class="text-gray-500">Autor:</span>
                    <span class="text-gray-900 text-right max-w-32 truncate" :title="document.metadata.pdf_info.author">
                      {{ document.metadata.pdf_info.author }}
                    </span>
                  </div>
                  <div v-if="document.metadata.pdf_info.creator" class="flex justify-between">
                    <span class="text-gray-500">Creador:</span>
                    <span class="text-gray-900 text-right max-w-32 truncate"
                      :title="document.metadata.pdf_info.creator">
                      {{ document.metadata.pdf_info.creator }}
                    </span>
                  </div>
                  <div v-if="document.metadata.pdf_info.producer" class="flex justify-between">
                    <span class="text-gray-500">Productor:</span>
                    <span class="text-gray-900 text-right max-w-32 truncate"
                      :title="document.metadata.pdf_info.producer">
                      {{ document.metadata.pdf_info.producer }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Acciones adicionales -->
              <div class="pt-4 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Acciones</h4>
                <div class="space-y-2">
                  <button @click="handleEdit" class="w-full btn-secondary text-left justify-start">
                    <PencilIcon class="h-4 w-4 mr-2" />
                    Editar información
                  </button>
                  <button @click="handleDownload" class="w-full btn-secondary text-left justify-start">
                    <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                    Descargar documento
                  </button>
                  <button @click="openInNewTab" class="w-full btn-secondary text-left justify-start">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Abrir en nueva pestaña
                  </button>
                  <button @click="confirmDelete" class="w-full btn-danger text-left justify-start">
                    <TrashIcon class="h-4 w-4 mr-2" />
                    Eliminar documento
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de edición -->
    <DocumentEditModal v-if="showEditModal" :document="document" :show="showEditModal" @close="closeEditModal"
      @updated="handleDocumentUpdated" />

    <!-- Modal de confirmación de eliminación -->
    <ConfirmDialog v-if="showDeleteModal" :title="`Eliminar ${document.name || document.original_filename}`"
      :message="'¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.'"
      confirm-text="Eliminar" cancel-text="Cancelar" danger :loading="deleting" @confirm="handleDelete"
      @cancel="cancelDelete" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import {
  DocumentIcon,
  PencilIcon,
  ArrowDownTrayIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ArrowPathIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'
import { VPdfViewer } from '@vue-pdf-viewer/viewer'
import { useDocuments } from '@/composables/useDocuments'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import DocumentEditModal from './DocumentEditModal.vue'
import { formatFileSize } from '@/utils/documentUtils'
import { formatDate } from '@/utils/formatters'

// Props
const props = defineProps({
  document: {
    type: Object,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close', 'updated', 'deleted'])

// Composable
const { getPreviewUrl, deleteDocument, downloadDocument, deleting } = useDocuments()

// Estado
const loading = ref(false)
const error = ref(null)
const showInfoPanel = ref(true)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const loadingProgress = ref('')
const debugInfo = ref({})

// Computed
const pdfUrl = computed(() => {
  if (!props.document?.id) return ''
  return getPreviewUrl(props.document.id)
})

// Watchers
watch(() => props.show, (newShow) => {
  if (newShow) {
    document.body.style.overflow = 'hidden'
    loadPdf()
  } else {
    document.body.style.overflow = ''
  }
})

watch(() => props.document, (newDoc) => {
  if (newDoc) {
    loadPdf()
  }
})

// Métodos
const loadPdf = () => {
  loading.value = true
  error.value = null
  loadingProgress.value = 'Iniciando carga...'
  debugInfo.value = {
    documentId: props.document?.id,
    url: pdfUrl.value,
    timestamp: new Date().toISOString()
  }

  console.log('🔄 Loading PDF:', pdfUrl.value)
}

const handlePdfLoading = () => {
  console.log('⏳ PDF is loading...')
  loading.value = true
  loadingProgress.value = 'Cargando PDF...'
}

const handlePdfLoaded = () => {
  console.log('✅ PDF loaded successfully')
  loading.value = false
  error.value = null
  loadingProgress.value = ''
}

const handlePdfError = (errorEvent) => {
  console.error('❌ PDF loading failed:', errorEvent)
  loading.value = false
  error.value = 'Error al cargar el PDF. Verifique que el archivo no esté corrupto.'

  debugInfo.value = {
    ...debugInfo.value,
    error: errorEvent,
    errorType: typeof errorEvent,
    errorMessage: errorEvent?.message || 'Unknown error'
  }
}

const testPdfUrl = async () => {
  console.log('🧪 Testing PDF URL:', pdfUrl.value)

  try {
    const response = await fetch(pdfUrl.value, { method: 'HEAD' })
    console.log('📡 Response status:', response.status)
    console.log('📋 Response headers:', Object.fromEntries(response.headers.entries()))

    if (response.ok) {
      alert('✅ URL responde correctamente')
    } else {
      alert(`❌ Error HTTP: ${response.status}`)
    }
  } catch (err) {
    console.error('🚫 Fetch error:', err)
    alert(`❌ Error de red: ${err.message}`)
  }
}

const openInNewTab = () => {
  window.open(pdfUrl.value, '_blank')
}

const handleBackdropClick = () => {
  emit('close')
}

const handleEdit = () => {
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
}

const handleDocumentUpdated = (updatedDocument) => {
  showEditModal.value = false
  emit('updated', updatedDocument)
}

const handleDownload = async () => {
  if (!props.document?.id) return

  const result = await downloadDocument(
    props.document.id,
    props.document.original_filename
  )

  if (!result.success) {
    console.error('Error downloading document:', result.message)
  }
}

const toggleInfoPanel = () => {
  showInfoPanel.value = !showInfoPanel.value
}

const confirmDelete = () => {
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
}

const handleDelete = async () => {
  try {
    const result = await deleteDocument(props.document.id)
    if (result.success) {
      showDeleteModal.value = false
      emit('deleted', props.document)
      emit('close')
    }
  } catch (error) {
    console.error('Error deleting document:', error)
  }
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  if (!props.show) return

  switch (event.key) {
    case 'Escape':
      if (!showEditModal.value && !showDeleteModal.value) {
        emit('close')
      }
      break
    case 'F5':
      event.preventDefault()
      loadPdf()
      break
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  if (props.show) {
    document.body.style.overflow = 'hidden'
    loadPdf()
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center;
}

.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center;
}

.btn-danger {
  @apply bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center;
}

.btn-icon {
  @apply p-2 rounded-lg transition-colors duration-200 hover:bg-gray-100;
}

.btn-primary:disabled,
.btn-secondary:disabled,
.btn-danger:disabled {
  @apply opacity-50 cursor-not-allowed;
}

/* Modal animations */
@keyframes modalEnter {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.bg-white {
  animation: modalEnter 0.15s ease-out;
}

/* Custom scrollbar for info panel */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>