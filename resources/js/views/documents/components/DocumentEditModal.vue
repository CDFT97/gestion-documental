<template>
  <div v-if="show"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
    @click="handleBackdropClick">
    <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <PencilIcon class="h-6 w-6 text-primary-600" />
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              Editar Documento
            </h3>
            <p class="text-sm text-gray-500">
              {{ document.original_filename }}
            </p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- Nombre del documento -->
          <div>
            <label for="document-name" class="block text-sm font-medium text-gray-700 mb-2">
              Nombre del documento
              <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input id="document-name" v-model="form.name" type="text" :placeholder="document.original_filename"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name }" maxlength="255" />
            <div v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ Array.isArray(errors.name) ? errors.name.join(', ') : errors.name }}
            </div>
            <p class="mt-1 text-xs text-gray-500">
              Máximo 255 caracteres. Si se deja vacío, se usará el nombre original del archivo.
            </p>
          </div>

          <!-- Categoría -->
          <div>
            <label for="document-category" class="block text-sm font-medium text-gray-700 mb-2">
              Categoría
              <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <div class="relative">
              <input id="document-category" v-model="form.category" type="text"
                placeholder="Ej: Contratos, Facturas, Legal, Reportes..." list="categories-list"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.category }"
                maxlength="100" />
              <datalist id="categories-list">
                <option v-for="category in availableCategories" :key="category" :value="category" />
              </datalist>
            </div>
            <div v-if="errors.category" class="mt-1 text-sm text-red-600">
              {{ Array.isArray(errors.category) ? errors.category.join(', ') : errors.category }}
            </div>
            <p class="mt-1 text-xs text-gray-500">
              Máximo 100 caracteres. Ayuda a organizar y filtrar tus documentos.
            </p>
          </div>

          <!-- Descripción -->
          <div>
            <label for="document-description" class="block text-sm font-medium text-gray-700 mb-2">
              Descripción
              <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <textarea id="document-description" v-model="form.description" rows="4"
              placeholder="Descripción detallada del documento, notas adicionales, etc."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-vertical"
              :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.description }"
              maxlength="1000" />
            <div v-if="errors.description" class="mt-1 text-sm text-red-600">
              {{ Array.isArray(errors.description) ? errors.description.join(', ') : errors.description }}
            </div>
            <div class="mt-1 flex justify-between text-xs text-gray-500">
              <span>Máximo 1000 caracteres</span>
              <span>{{ form.description?.length || 0 }}/1000</span>
            </div>
          </div>

          <!-- Información actual del documento (solo lectura) -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Información actual</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-500">Archivo original:</span>
                <p class="text-gray-900 truncate" :title="document.original_filename">
                  {{ document.original_filename }}
                </p>
              </div>
              <div>
                <span class="text-gray-500">Tamaño:</span>
                <p class="text-gray-900">{{ formatFileSize(document.file_size) }}</p>
              </div>
              <div>
                <span class="text-gray-500">Subido:</span>
                <p class="text-gray-900">{{ formatDate(document.created_at) }}</p>
              </div>
              <div v-if="document.updated_at !== document.created_at">
                <span class="text-gray-500">Última modificación:</span>
                <p class="text-gray-900">{{ formatDate(document.updated_at) }}</p>
              </div>
            </div>

            <!-- Metadatos del PDF si existen -->
            <div v-if="document.metadata?.pdf_info" class="mt-4 pt-4 border-t border-gray-200">
              <span class="text-gray-500 text-sm">Información del PDF:</span>
              <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <div v-if="document.metadata.pdf_info.pages">
                  <span class="text-gray-500">Páginas:</span>
                  <span class="text-gray-900 ml-1">{{ document.metadata.pdf_info.pages }}</span>
                </div>
                <div v-if="document.metadata.pdf_info.title">
                  <span class="text-gray-500">Título:</span>
                  <span class="text-gray-900 ml-1 truncate" :title="document.metadata.pdf_info.title">
                    {{ document.metadata.pdf_info.title }}
                  </span>
                </div>
                <div v-if="document.metadata.pdf_info.author">
                  <span class="text-gray-500">Autor:</span>
                  <span class="text-gray-900 ml-1 truncate" :title="document.metadata.pdf_info.author">
                    {{ document.metadata.pdf_info.author }}
                  </span>
                </div>
                <div v-if="document.metadata.pdf_info.creator">
                  <span class="text-gray-500">Creador:</span>
                  <span class="text-gray-900 ml-1 truncate" :title="document.metadata.pdf_info.creator">
                    {{ document.metadata.pdf_info.creator }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Errores generales -->
        <div v-if="errors.general" class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error al actualizar</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="space-y-1">
                  <li v-for="error in (Array.isArray(errors.general) ? errors.general : [errors.general])" :key="error">
                    {{ error }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="mt-8 flex justify-end space-x-3">
          <button type="button" @click="$emit('close')" class="btn-secondary" :disabled="loading">
            Cancelar
          </button>
          <button type="submit" class="btn-primary" :disabled="loading || !hasChanges">
            <LoadingSpinner v-if="loading" size="sm" color="text-white" class="mr-2" />
            <CheckIcon v-else class="h-4 w-4 mr-2" />
            {{ loading ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import {
  PencilIcon,
  XMarkIcon,
  CheckIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import { useDocuments } from '@/composables/useDocuments'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
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
const emit = defineEmits(['close', 'updated'])

// Composable
const { updateDocument, categories, getCategories, loading, errors, clearErrors } = useDocuments()

// Estado del formulario
const form = reactive({
  name: '',
  category: '',
  description: ''
})

// Estado original para detectar cambios
const originalForm = ref({})

// Flag para controlar inicialización
const isInitialized = ref(false)

// Computed
const availableCategories = computed(() => categories.value || [])

const hasChanges = computed(() => {
  if (!isInitialized.value) return false

  return (
    form.name !== originalForm.value.name ||
    form.category !== originalForm.value.category ||
    form.description !== originalForm.value.description
  )
})

// Función para inicializar el formulario
function initializeForm() {
  const doc = props.document
  if (!doc) return

  form.name = doc.name || ''
  form.category = doc.category || ''
  form.description = doc.description || ''

  // Guardar estado original
  originalForm.value = {
    name: form.name,
    category: form.category,
    description: form.description
  }

  isInitialized.value = true
}

// Función para cargar categorías
async function loadCategories() {
  try {
    await getCategories()
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

// Función para manejar el envío del formulario
async function handleSubmit() {
  if (!hasChanges.value) {
    emit('close')
    return
  }

  clearErrors()

  try {
    // Preparar datos para envío - solo enviar campos que han cambiado
    const updateData = {}

    if (form.name !== originalForm.value.name) {
      updateData.name = form.name || null
    }

    if (form.category !== originalForm.value.category) {
      updateData.category = form.category || null
    }

    if (form.description !== originalForm.value.description) {
      updateData.description = form.description || null
    }

    const result = await updateDocument(props.document.id, updateData)

    if (result.success) {
      emit('updated', result.document)
    }
  } catch (error) {
    console.error('Error updating document:', error)
  }
}

// Función para manejar click en backdrop
function handleBackdropClick() {
  if (hasChanges.value) {
    if (confirm('¿Estás seguro de que quieres cerrar? Se perderán los cambios no guardados.')) {
      emit('close')
    }
  } else {
    emit('close')
  }
}

// Función para manejar atajos de teclado
function handleKeydown(event) {
  if (!props.show) return

  switch (event.key) {
    case 'Escape':
      if (hasChanges.value) {
        if (confirm('¿Estás seguro de que quieres cerrar? Se perderán los cambios no guardados.')) {
          emit('close')
        }
      } else {
        emit('close')
      }
      break
    case 'Enter':
      if (event.ctrlKey || event.metaKey) {
        event.preventDefault()
        if (hasChanges.value && !loading.value) {
          handleSubmit()
        }
      }
      break
  }
}

// Watchers
watch(() => props.document, (newDocument) => {
  if (newDocument) {
    nextTick(() => {
      initializeForm()
    })
  }
}, { immediate: true })

watch(() => props.show, (newShow) => {
  if (newShow) {
    clearErrors()
    loadCategories()
    document.body.style.overflow = 'hidden'

    // Reinicializar el formulario cuando se muestra el modal
    if (props.document) {
      nextTick(() => {
        initializeForm()
      })
    }
  } else {
    document.body.style.overflow = ''
    isInitialized.value = false
  }
})

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)

  if (props.show) {
    document.body.style.overflow = 'hidden'
    loadCategories()

    if (props.document) {
      nextTick(() => {
        initializeForm()
      })
    }
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed;
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

/* Form styling */
input:focus,
textarea:focus {
  outline: none;
}

/* Custom scrollbar */
textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  background: #f1f5f9;
}

textarea::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>