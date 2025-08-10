<template>
  <div class="max-w-4xl mx-auto">
    <!-- Upload Area -->
    <div v-if="!isUploaded" class="mb-8">
      <div @drop="handleDrop" @dragover.prevent @dragenter.prevent @dragleave="isDragging = false"
        @dragover="isDragging = true" :class="[
          'border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200',
          isDragging
            ? 'border-primary-500 bg-primary-50'
            : 'border-gray-300 hover:border-gray-400'
        ]">
        <div class="space-y-4">
          <!-- Upload Icon -->
          <div class="mx-auto w-16 h-16 text-gray-400">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
          </div>

          <!-- Upload Text -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              Sube tu archivo Excel
            </h3>
            <p class="text-gray-600 mb-4">
              Arrastra y suelta tu archivo aquí o haz clic para seleccionar
            </p>

            <!-- File Requirements -->
            <div class="text-sm text-gray-500 space-y-1">
              <p>📊 Formatos soportados: .xlsx, .xls, .xlsx</p>
              <p>📏 Tamaño máximo: 10MB</p>
              <p>📋 Primera fila debe contener los encabezados</p>
            </div>
          </div>

          <!-- Upload Button -->
          <div>
            <input ref="fileInput" type="file" accept=".xlsx,.xls, .xlsx" @change="handleFileSelect" class="hidden" />
            <button @click="$refs.fileInput.click()" :disabled="uploading" class="btn-primary inline-flex items-center">
              <svg v-if="!uploading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <LoadingSpinner v-else class="mr-2" />
              {{ uploading ? 'Subiendo...' : 'Seleccionar archivo' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Errors -->
      <div v-if="errors.file" class="mt-4">
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Error en el archivo
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li v-for="error in errors.file" :key="error">{{ error }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ExcelPreview v-if="isUploaded && hasPreview" :preview-data="previewData" :uploaded-file="uploadedFile"
      :processing="processing" @process="handleProcess" @cancel="handleCancel" />

    <!-- Success Section -->
    <div v-if="isProcessed" class="mt-8">
      <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-center">
          <CheckCircleIcon class="h-8 w-8 text-green-600" />
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-green-900">
              ¡Archivo procesado exitosamente!
            </h3>
            <p class="text-green-700 mt-1">
              Se creó la tabla "{{ processedTable.name }}" con {{ processedTable.total_records }} registros
            </p>
          </div>
        </div>

        <div class="mt-4 flex space-x-3">
          <router-link :to="`/tables/${processedTable.id}`" class="btn-primary">
            Ver tabla
          </router-link>

          <button @click="handleNewUpload" class="btn-secondary">
            Subir otro archivo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useExcelUpload } from '@/composables/useExcelUpload'
import {
  ExclamationTriangleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ExcelPreview from './ExcelPreview.vue'

const {
  uploading,
  processing,
  errors,
  uploadedFile,
  previewData,
  processedTable,
  isUploaded,
  isProcessed,
  hasPreview,
  uploadFile,
  processFile,
  validateFile,
  clearState
} = useExcelUpload()

const isDragging = ref(false)

const emit = defineEmits(['upload-success'])

const handleDrop = (e) => {
  e.preventDefault()
  isDragging.value = false

  const files = e.dataTransfer.files
  if (files.length > 0) {
    handleFile(files[0])
  }
}

const handleFileSelect = (e) => {
  const files = e.target.files
  if (files.length > 0) {
    handleFile(files[0])
  }
}

const handleFile = async (file) => {
  const validation = validateFile(file)
  if (!validation.isValid) {
    errors.value = { file: validation.errors }
    return
  }

  await uploadFile(file)
}

const handleProcess = async () => {
  await processFile()
  emit('upload-success') 

}

const handleCancel = () => {
  clearState()
}

const handleNewUpload = () => {
  clearState()
}
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
  @apply bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

/* Drag and drop animations */
.drag-enter {
  transform: scale(1.02);
}
</style>