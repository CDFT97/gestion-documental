<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">
        Subir Documento PDF
      </h3>
      <p class="text-gray-600 text-sm">
        Arrastra y suelta tu archivo PDF aquí, o haz clic para seleccionar. Tamaño máximo: 50MB
      </p>
    </div>

    <!-- Área de drop -->
    <div @drop="handleDrop" @dragover.prevent @dragenter.prevent @dragleave="isDragOver = false"
      @dragover="isDragOver = true" :class="[
        'border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200',
        isDragOver ? 'border-primary-400 bg-primary-50' : 'border-gray-300',
        uploading ? 'pointer-events-none opacity-50' : 'cursor-pointer hover:border-primary-400 hover:bg-gray-50'
      ]" @click="triggerFileInput">
      <input ref="fileInput" type="file" accept=".pdf,application/pdf" @change="handleFileSelect" class="hidden" />

      <div v-if="!uploading && !selectedFile">
        <DocumentPlusIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
        <p class="text-lg font-medium text-gray-900 mb-2">
          {{ isDragOver ? 'Suelta el archivo aquí' : 'Selecciona un archivo PDF' }}
        </p>
        <p class="text-gray-500">
          o arrastra y suelta
        </p>
      </div>

      <div v-else-if="selectedFile && !uploading">
        <DocumentIcon class="mx-auto h-12 w-12 text-primary-600 mb-4" />
        <p class="text-lg font-medium text-gray-900 mb-2">
          {{ selectedFile.name }}
        </p>
        <p class="text-gray-500 mb-4">
          {{ formatFileSize(selectedFile.size) }}
        </p>
        <div class="flex justify-center space-x-3">
          <button @click.stop="clearFile" class="btn-secondary">
            <XMarkIcon class="h-4 w-4 mr-2" />
            Cancelar
          </button>
          <button @click.stop="uploadFile" class="btn-primary">
            <CloudArrowUpIcon class="h-4 w-4 mr-2" />
            Subir
          </button>
        </div>
      </div>

      <div v-else-if="uploading">
        <CloudArrowUpIcon class="mx-auto h-12 w-12 text-primary-600 mb-4 animate-pulse" />
        <p class="text-lg font-medium text-gray-900 mb-2">
          Subiendo archivo...
        </p>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
          <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" :style="`width: ${uploadProgress}%`">
          </div>
        </div>
        <p class="text-gray-500">
          {{ uploadProgress }}% completado
        </p>
      </div>
    </div>

    <!-- Metadatos del documento -->
    <div v-if="selectedFile && !uploading" class="mt-6 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="document-name" class="block text-sm font-medium text-gray-700 mb-1">
            Nombre del documento (opcional)
          </label>
          <input id="document-name" v-model="metadata.name" type="text" :placeholder="defaultName"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            maxlength="255" />
        </div>

        <div>
          <label for="document-category" class="block text-sm font-medium text-gray-700 mb-1">
            Categoría (opcional)
          </label>
          <input id="document-category" v-model="metadata.category" type="text"
            placeholder="Ej: Contratos, Facturas, Legal..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            maxlength="100" />
        </div>
      </div>

      <div>
        <label for="document-description" class="block text-sm font-medium text-gray-700 mb-1">
          Descripción (opcional)
        </label>
        <textarea id="document-description" v-model="metadata.description" rows="3"
          placeholder="Descripción del documento..."
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          maxlength="1000"></textarea>
      </div>
    </div>

    <!-- Errores -->
    <div v-if="errors && Object.keys(errors).length > 0" class="mt-4">
      <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error al subir el archivo</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="space-y-1">
                <li v-for="(errorList, field) in errors" :key="field">
                  <span v-if="Array.isArray(errorList)">
                    {{ errorList.join(', ') }}
                  </span>
                  <span v-else>{{ errorList }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mensaje de éxito -->
    <div v-if="uploadSuccess" class="mt-4">
      <div class="bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <CheckCircleIcon class="h-5 w-5 text-green-400" />
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">
              ¡Documento subido exitosamente!
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  DocumentPlusIcon,
  DocumentIcon,
  CloudArrowUpIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import { useDocuments } from '@/composables/useDocuments'
import { getFileNameWithoutExtension } from '@/utils/documentUtils'

// Emits
const emit = defineEmits(['upload-success'])

// Composable
const { uploadDocument, uploading, errors, clearErrors, validateDocumentFile, formatFileSize } = useDocuments()

// Estado local
const fileInput = ref(null)
const selectedFile = ref(null)
const isDragOver = ref(false)
const uploadProgress = ref(0)
const uploadSuccess = ref(false)

// Metadatos del documento
const metadata = ref({
  name: '',
  category: '',
  description: ''
})

// Computed
const defaultName = computed(() => {
  return selectedFile.value ? getFileNameWithoutExtension(selectedFile.value.name) : ''
})

// Watchers
watch(uploading, (isUploading) => {
  if (isUploading) {
    uploadProgress.value = 0
    uploadSuccess.value = false
    simulateProgress()
  }
})

// Métodos
const triggerFileInput = () => {
  if (!uploading.value) {
    fileInput.value?.click()
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    handleFile(file)
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false

  const files = event.dataTransfer.files
  if (files.length > 0) {
    handleFile(files[0])
  }
}

const handleFile = (file) => {
  clearErrors()
  uploadSuccess.value = false

  // Validar archivo
  const validation = validateDocumentFile(file)
  if (!validation.isValid) {
    return
  }

  selectedFile.value = file

  // Auto-rellenar nombre si está vacío
  if (!metadata.value.name) {
    metadata.value.name = defaultName.value
  }
}

const clearFile = () => {
  selectedFile.value = null
  metadata.value = {
    name: '',
    category: '',
    description: ''
  }
  clearErrors()
  uploadSuccess.value = false
  uploadProgress.value = 0

  // Limpiar input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const uploadFile = async () => {
  if (!selectedFile.value) return

  clearErrors()
  uploadSuccess.value = false

  try {
    const result = await uploadDocument(selectedFile.value, metadata.value)

    if (result.success) {
      uploadSuccess.value = true
      emit('upload-success', result.document)

      // Limpiar formulario después de 2 segundos
      setTimeout(() => {
        clearFile()
        uploadSuccess.value = false
      }, 2000)
    }
  } catch (error) {
    console.error('Error uploading document:', error)
  }
}

const simulateProgress = () => {
  const interval = setInterval(() => {
    if (uploadProgress.value < 90) {
      uploadProgress.value += Math.random() * 10
    } else if (!uploading.value) {
      uploadProgress.value = 100
      clearInterval(interval)
    }
  }, 200)
}
</script>
