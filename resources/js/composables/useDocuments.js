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

const loading = ref(false)
const uploading = ref(false)
const deleting = ref(false)
const errors = ref({})
const documents = ref([])
const currentDocument = ref(null)
const categories = ref([])

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

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

  const clearErrors = () => {
    errors.value = {}
  }

  const handleValidationErrors = (error) => {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      const message = extractErrorMessage(error)
      errors.value = { general: [message] }
    }
  }

  const clearFilters = () => {
    Object.assign(filters, {
      search: '',
      category: '',
      date_from: '',
      date_to: '',
      per_page: 15
    })
  }

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

      // Update pagination information
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

  const uploadDocument = async (file, metadata = {}) => {
    const fileValidation = validateFile(file)
    if (!fileValidation.isValid) {
      errors.value = { file: fileValidation.errors }
      return { success: false, errors: fileValidation.errors }
    }

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

      documents.value.unshift(response.data.document)

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

  const updateDocument = async (id, data) => {
    const validation = validateDocumentMetadata(data)
    if (!validation.isValid) {
      errors.value = { update: validation.errors }
      return { success: false, errors: validation.errors }
    }

    loading.value = true
    clearErrors()

    try {
      const response = await api.put(`/api/documents/${id}`, validation.cleanedData)

      const index = documents.value.findIndex(doc => doc.id === id)
      if (index !== -1) {
        documents.value[index] = response.data.document
      }

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

  const deleteDocument = async (id) => {
    deleting.value = true
    clearErrors()

    try {
      const response = await api.delete(`/api/documents/${id}`)

      documents.value = documents.value.filter(doc => doc.id !== id)

      if (currentDocument.value?.id === id) {
        currentDocument.value = null
      }

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

  const getPreviewUrl = (id) => {
    const urlBase =  window.location.origin;
    const token = localStorage.getItem('token')
    return `${urlBase}/api/documents/${id}/preview?token=${encodeURIComponent(token)}`
  }

  const downloadDocument = async (id, filename) => {
    try {
      const response = await api.get(`/api/documents/${id}/preview/download`, {
        responseType: 'blob'
      })

      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)

      const link = document.createElement('a')
      link.href = url
      link.download = filename || 'documento.pdf'
      document.body.appendChild(link)
      link.click()

      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)

      return { success: true }
    } catch (error) {
      console.error('Error downloading document:', error)
      return { success: false, message: 'Error al descargar el documento' }
    }
  }

  const searchDocuments = async (searchTerm) => {
    filters.search = searchTerm
    return await getDocuments(1) 
  }

  const filterByCategory = async (category) => {
    filters.category = category
    return await getDocuments(1)
  }

  const filterByDateRange = async (dateFrom, dateTo) => {
    const validation = validateDateRange(dateFrom, dateTo)
    if (!validation.isValid) {
      errors.value = { dateRange: validation.errors }
      return { success: false, errors: validation.errors }
    }

    filters.date_from = formatDateForFilter(dateFrom)
    filters.date_to = formatDateForFilter(dateTo)
    return await getDocuments(1)
  }

  const goToPage = async (page) => {
    if (page >= 1 && page <= pagination.last_page) {
      return await getDocuments(page)
    }
    return { success: false, message: 'Página inválida' }
  }

  const nextPage = async () => {
    if (!isLastPage.value) {
      return await goToPage(pagination.current_page + 1)
    }
    return { success: false, message: 'Ya está en la última página' }
  }

  const previousPage = async () => {
    if (!isFirstPage.value) {
      return await goToPage(pagination.current_page - 1)
    }
    return { success: false, message: 'Ya está en la primera página' }
  }

  const changePerPage = async (perPage) => {
    filters.per_page = perPage
    pagination.per_page = perPage
    return await getDocuments(1)
  }

  const refreshDocuments = async () => {
    return await getDocuments(pagination.current_page)
  }

  const validateDocumentFile = (file) => {
    const validation = validateFile(file)
    if (!validation.isValid) {
      errors.value = { file: validation.errors }
    }
    return validation
  }

  return {
    // State
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

    // Methods
    getDocuments,
    getDocument,
    uploadDocument,
    updateDocument,
    deleteDocument,
    getCategories,

    // Utilities
    getPreviewUrl,
    downloadDocument,
    validateDocumentFile,
    formatFileSize,

    // Filters and search
    searchDocuments,
    filterByCategory,
    filterByDateRange,
    clearFilters,

    // Pagination
    goToPage,
    nextPage,
    previousPage,
    changePerPage,

    // Others
    refreshDocuments,
    clearState,
    clearErrors
  }
}