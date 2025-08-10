<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            Vista previa del archivo
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            {{ uploadedFile.original_name }}
          </p>
        </div>

        <!-- File Info -->
        <div class="text-right text-sm text-gray-500">
          <p>{{ formatFileSize(uploadedFile.file.size) }}</p>
          <p>{{ previewData.total_rows }} filas</p>
        </div>
      </div>
    </div>

    <!-- Preview Table -->
    <div class="p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th v-for="(header, index) in previewData.headers" :key="index"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ header }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(row, rowIndex) in previewData.rows" :key="rowIndex">
              <td v-for="(cell, cellIndex) in row" :key="cellIndex"
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span v-if="cell !== null && cell !== ''" :class="getCellClass(cell)">
                  {{ formatCellValue(cell) }}
                </span>
                <span v-else class="text-gray-400 italic">vacío</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Show more indicator -->
      <div v-if="previewData.total_rows > previewData.rows.length" class="mt-4 text-center text-sm text-gray-500">
        ... y {{ previewData.total_rows - previewData.rows.length }} filas más
      </div>
    </div>

    <!-- Actions -->
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          <p><strong>Nota:</strong> Se detectarán automáticamente los tipos de datos</p>
        </div>

        <div class="flex space-x-3">
          <button @click="$emit('cancel')" :disabled="processing" class="btn-secondary">
            Cancelar
          </button>

          <button @click="$emit('process')" :disabled="processing" class="btn-primary flex items-center">
            <LoadingSpinner v-if="processing" class="mr-2" />
            {{ processing ? 'Procesando...' : 'Procesar archivo' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const props = defineProps({
  previewData: {
    type: Object,
    required: true
  },
  uploadedFile: {
    type: Object,
    required: true
  },
  processing: {
    type: Boolean,
    default: false
  }
})

defineEmits(['process', 'cancel'])

// Format file size
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Format cell value for display
const formatCellValue = (value) => {
  if (typeof value === 'boolean') {
    return value ? 'Verdadero' : 'Falso'
  }

  if (typeof value === 'number') {
    return value.toLocaleString()
  }

  return value
}

// Get cell styling based on content
const getCellClass = (value) => {
  if (typeof value === 'number') {
    return 'text-blue-600 font-mono'
  }

  if (typeof value === 'boolean') {
    return value ? 'text-green-600' : 'text-red-600'
  }

  if (typeof value === 'string' && value.includes('@')) {
    return 'text-purple-600'
  }

  return 'text-gray-900'
}
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 disabled:opacity-50;
}

.btn-secondary {
  @apply bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}
</style>