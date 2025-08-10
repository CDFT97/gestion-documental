import { ref, computed } from 'vue'
import { api } from '../api/axios'
import { useToast } from 'vue-toastification'

const loading = ref(false)
const uploading = ref(false)
const processing = ref(false)
const errors = ref({})
const uploadedFile = ref(null)
const previewData = ref(null)
const processedTable = ref(null)

export function useExcelUpload() {
  const toast = useToast()

  const isUploaded = computed(() => !!uploadedFile.value)
  const isProcessed = computed(() => !!processedTable.value)
  const hasPreview = computed(() => !!previewData.value)

  const clearState = () => {
    uploadedFile.value = null
    previewData.value = null
    processedTable.value = null
    errors.value = {}
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const handleValidationErrors = (error) => {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      const message = error.response?.data?.message || 'Error del servidor'
      errors.value = { general: [message] }
    }
  }

  const uploadFile = async (file) => {
    uploading.value = true
    clearErrors()

    try {
      const formData = new FormData()
      formData.append('file', file)

      const response = await api.upload('/api/excel/upload', formData)

      uploadedFile.value = {
        file_path: response.data.file_path,
        original_name: response.data.original_name,
        file: file
      }

      previewData.value = response.data.preview

      toast.success('Archivo cargado exitosamente')
      return { success: true, data: response.data }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al cargar el archivo' }
    } finally {
      uploading.value = false
    }
  }

  const processFile = async () => {
    if (!uploadedFile.value) {
      toast.error('No hay archivo para procesar')
      return { success: false }
    }

    processing.value = true
    clearErrors()

    try {
      const response = await api.post('/api/excel/process', {
        file_path: uploadedFile.value.file_path,
        original_name: uploadedFile.value.original_name
      })

      processedTable.value = response.data.table

      toast.success('Archivo procesado exitosamente')
      return { success: true, table: response.data.table }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al procesar el archivo' }
    } finally {
      processing.value = false
    }
  }

  const validateFile = (file) => {
    const errors = []

    // Check file type
    const allowedTypes = [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
      'application/vnd.ms-excel' // .xls
    ]

    if (!allowedTypes.includes(file.type)) {
      errors.push('Solo se permiten archivos Excel (.xlsx, .xls)')
    }

    // Check file size (10MB max)
    const maxSize = 10 * 1024 * 1024 // 10MB
    if (file.size > maxSize) {
      errors.push('El archivo no puede superar los 10MB')
    }

    return {
      isValid: errors.length === 0,
      errors
    }
  }

  // Format file size
  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'

    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  }

  return {
    // State
    loading: computed(() => uploading.value || processing.value),
    uploading,
    processing,
    errors,
    uploadedFile,
    previewData,
    processedTable,
    isUploaded,
    isProcessed,
    hasPreview,

    // Methods
    uploadFile,
    processFile,
    validateFile,
    formatFileSize,
    clearState,
    clearErrors
  }
}