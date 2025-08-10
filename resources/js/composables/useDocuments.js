import { ref, reactive, computed } from 'vue'
import { api } from '../api/axios'
import { useToast } from 'vue-toastification'
import {
  validateFile,
  formatFileSize,
  validateDocumentMetadata,
  formatDateForFilter,
  createDocumentFormData,
  cleanQueryParams,
  extractErrorMessage,
  validateDateRange
} from '@/utils/documentUtils'

// Estado global del composable
const loading = ref(false)
const uploading = ref(false)
const deleting = ref(false)
const errors = ref({})
const documents = ref([])
const currentDocument = ref(null)
const categories = ref([])

// Estado de paginación
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

// Estado de filtros
const filters = reactive({
  search: '',
  category: '',
  date_from: '',
  date_to: '',
  per_page: 15
})

export function useDocuments() {
  const toast = useToast()

  // Computed properties
  const hasDocuments = computed(() => documents.value.length > 0)
  const totalDocuments = computed(() => pagination.total)
  const isLastPage = computed(() => pagination.current_page >= pagination.last_page)
  const isFirstPage = computed(() => pagination.current_page <= 1)
  const isLoading = computed(() => loading.value || uploading.value || deleting.value)

  // Función para limpiar errores
  const clearErrors = () => {
    errors.value = {}
  }

  // Función para manejar errores de validación
  const handleValidationErrors = (error) => {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      const message = extractErrorMessage(error)
      errors.value = { general: [message] }
    }
  }

  // Función para limpiar filtros
  const clearFilters = () => {
    Object.assign(filters, {
      search: '',
      category: '',
      date_from: '',
      date_to: '',
      per_page: 15
    })
  }

  // Limpiar estado completo
  const clearState = () => {
    documents.value = []
    currentDocument.value = null
    categories.value = []
    clearErrors()
    Object.assign(pagination, {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    })
    clearFilters()
  }

  // Obtener lista de documentos con filtros y paginación
  const getDocuments = async (page = 1) => {
    loading.value = true
    clearErrors()

    try {
      const params = cleanQueryParams({
        page,
        ...filters
      })

      const response = await api.get('/api/documents', { params })

      documents.value = response.data.data

      // Actualizar información de paginación
      Object.assign(pagination, {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
        from: response.data.from,
        to: response.data.to
      })

      return { success: true, data: response.data }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al obtener documentos' }
    } finally {
      loading.value = false
    }
  }

  // Obtener un documento específico
  const getDocument = async (id) => {
    loading.value = true
    clearErrors()

    try {
      const response = await api.get(`/api/documents/${id}`)
      currentDocument.value = response.data.document

      return { success: true, document: response.data.document }
    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al obtener el documento' }
    } finally {
      loading.value = false
    }
  }

  // Subir documento
  const uploadDocument = async (file, metadata = {}) => {
    // Validar archivo antes de subir
    const fileValidation = validateFile(file)
    if (!fileValidation.isValid) {
      errors.value = { file: fileValidation.errors }
      return { success: false, errors: fileValidation.errors }
    }

    // Validar metadatos
    const metadataValidation = validateDocumentMetadata(metadata)
    if (!metadataValidation.isValid) {
      errors.value = { metadata: metadataValidation.errors }
      return { success: false, errors: metadataValidation.errors }
    }

    uploading.value = true
    clearErrors()

    try {
      const formData = createDocumentFormData(file, metadataValidation.cleanedData)

      const response = await api.upload('/api/documents/upload', formData)

      // Agregar el nuevo documento al inicio de la lista
      documents.value.unshift(response.data.document)

      // Actualizar el total
      pagination.total += 1

      toast.success(response.data.message || 'Documento subido exitosamente')

      return {
        success: true,
        document: response.data.document,
        message: response.data.message
      }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al subir el documento' }
    } finally {
      uploading.value = false
    }
  }

  // Actualizar documento
  const updateDocument = async (id, data) => {
    // Validar datos de actualización
    const validation = validateDocumentMetadata(data)
    if (!validation.isValid) {
      errors.value = { update: validation.errors }
      return { success: false, errors: validation.errors }
    }

    loading.value = true
    clearErrors()

    try {
      const response = await api.put(`/api/documents/${id}`, validation.cleanedData)

      // Actualizar el documento en la lista
      const index = documents.value.findIndex(doc => doc.id === id)
      if (index !== -1) {
        documents.value[index] = response.data.document
      }

      // Actualizar documento actual si coincide
      if (currentDocument.value?.id === id) {
        currentDocument.value = response.data.document
      }

      toast.success(response.data.message || 'Documento actualizado exitosamente')

      return { success: true, document: response.data.document }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al actualizar el documento' }
    } finally {
      loading.value = false
    }
  }

  // Eliminar documento
  const deleteDocument = async (id) => {
    deleting.value = true
    clearErrors()

    try {
      const response = await api.delete(`/api/documents/${id}`)

      // Remover de la lista
      documents.value = documents.value.filter(doc => doc.id !== id)

      // Limpiar documento actual si coincide
      if (currentDocument.value?.id === id) {
        currentDocument.value = null
      }

      // Actualizar total
      pagination.total = Math.max(0, pagination.total - 1)

      toast.success(response.data.message || 'Documento eliminado exitosamente')

      return { success: true }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al eliminar el documento' }
    } finally {
      deleting.value = false
    }
  }

  // Obtener categorías disponibles
  const getCategories = async () => {
    try {
      const response = await api.get('/api/documents/categories')
      categories.value = response.data.categories

      return { success: true, categories: response.data.categories }
    } catch (error) {
      console.error('Error fetching categories:', error)
      return { success: false, categories: [] }
    }
  }

  // Obtener URL de preview del documento con token
  const getPreviewUrl = (id) => {
    const urlBase =  window.location.origin;
    const token = localStorage.getItem('token')
    // Agregar token como parámetro de consulta para iframe
    return `${urlBase}/api/documents/${id}/preview?token=${encodeURIComponent(token)}`
  }

  // Descargar documento
  const downloadDocument = async (id, filename) => {
    try {
      const response = await api.get(`/api/documents/${id}/preview/download`, {
        responseType: 'blob' // Importante para archivos binarios
      })

      // Crear blob URL
      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)

      // Crear enlace temporal y hacer click
      const link = document.createElement('a')
      link.href = url
      link.download = filename || 'documento.pdf'
      document.body.appendChild(link)
      link.click()

      // Limpiar
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)

      return { success: true }
    } catch (error) {
      console.error('Error downloading document:', error)
      return { success: false, message: 'Error al descargar el documento' }
    }
  }

  // Buscar documentos
  const searchDocuments = async (searchTerm) => {
    filters.search = searchTerm
    return await getDocuments(1) // Resetear a página 1
  }

  // Filtrar por categoría
  const filterByCategory = async (category) => {
    filters.category = category
    return await getDocuments(1)
  }

  // Filtrar por rango de fechas
  const filterByDateRange = async (dateFrom, dateTo) => {
    // Validar rango de fechas
    const validation = validateDateRange(dateFrom, dateTo)
    if (!validation.isValid) {
      errors.value = { dateRange: validation.errors }
      return { success: false, errors: validation.errors }
    }

    filters.date_from = formatDateForFilter(dateFrom)
    filters.date_to = formatDateForFilter(dateTo)
    return await getDocuments(1)
  }

  // Cambiar página
  const goToPage = async (page) => {
    if (page >= 1 && page <= pagination.last_page) {
      return await getDocuments(page)
    }
    return { success: false, message: 'Página inválida' }
  }

  // Página siguiente
  const nextPage = async () => {
    if (!isLastPage.value) {
      return await goToPage(pagination.current_page + 1)
    }
    return { success: false, message: 'Ya está en la última página' }
  }

  // Página anterior
  const previousPage = async () => {
    if (!isFirstPage.value) {
      return await goToPage(pagination.current_page - 1)
    }
    return { success: false, message: 'Ya está en la primera página' }
  }

  // Cambiar elementos por página
  const changePerPage = async (perPage) => {
    filters.per_page = perPage
    pagination.per_page = perPage
    return await getDocuments(1)
  }

  // Refrescar lista actual
  const refreshDocuments = async () => {
    return await getDocuments(pagination.current_page)
  }

  // Validar archivo (función helper expuesta)
  const validateDocumentFile = (file) => {
    const validation = validateFile(file)
    if (!validation.isValid) {
      errors.value = { file: validation.errors }
    }
    return validation
  }

  return {
    // Estado
    loading: computed(() => isLoading.value),
    uploading,
    deleting,
    errors,
    documents,
    currentDocument,
    categories,
    pagination: computed(() => pagination),
    filters: computed(() => filters),

    // Computed
    hasDocuments,
    totalDocuments,
    isLastPage,
    isFirstPage,

    // Métodos principales
    getDocuments,
    getDocument,
    uploadDocument,
    updateDocument,
    deleteDocument,
    getCategories,

    // Utilidades
    getPreviewUrl,
    downloadDocument,
    validateDocumentFile,
    formatFileSize,

    // Filtros y búsqueda
    searchDocuments,
    filterByCategory,
    filterByDateRange,
    clearFilters,

    // Paginación
    goToPage,
    nextPage,
    previousPage,
    changePerPage,

    // Otros
    refreshDocuments,
    clearState,
    clearErrors
  }
}