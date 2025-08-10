import { ref, computed } from 'vue'
import { api } from '../api/axios'
import { useToast } from 'vue-toastification'

export function useCrudOperations(tableId) {
  const toast = useToast()

  // State
  const records = ref(null)
  const selectedRecords = ref([])
  const loading = ref(false)
  const creating = ref(false)
  const updating = ref(false)
  const deleting = ref(false)
  const errors = ref({})

  // Filters and pagination
  const currentPage = ref(1)
  const perPage = ref(15)
  const search = ref('')
  const sortBy = ref('id')
  const sortDirection = ref('asc')
  const columnFilters = ref({})

  // Computed
  const hasRecords = computed(() => records.value?.data?.length > 0)
  const hasSelection = computed(() => selectedRecords.value.length > 0)
  const totalRecords = computed(() => records.value?.total || 0)

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

  const loadRecords = async (options = {}) => {
    loading.value = true
    clearErrors()

    try {
      const params = {
        page: options.page || currentPage.value,
        per_page: options.perPage || perPage.value,
        search: options.search || search.value,
        sort_by: options.sortBy || sortBy.value,
        sort_direction: options.sortDirection || sortDirection.value,
        column_filters: options.columnFilters || columnFilters.value
      }

      // Remove empty filters
      Object.keys(params.column_filters).forEach(key => {
        if (!params.column_filters[key]) {
          delete params.column_filters[key]
        }
      })

      const response = await api.get(`/api/tables/${tableId}/records`, { params })
      records.value = response.data.records

      // Update state
      if (options.page) currentPage.value = options.page
      if (options.perPage) perPage.value = options.perPage
      if (options.search !== undefined) search.value = options.search
      if (options.sortBy) sortBy.value = options.sortBy
      if (options.sortDirection) sortDirection.value = options.sortDirection
      if (options.columnFilters) columnFilters.value = options.columnFilters

      return { success: true, data: response.data }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al cargar los registros' }
    } finally {
      loading.value = false
    }
  }

  const createRecord = async (data) => {
    creating.value = true
    clearErrors()

    try {
      const response = await api.post(`/api/tables/${tableId}/records`, data)

      toast.success('Registro creado exitosamente')
      await loadRecords() // Reload to show new record

      return { success: true, record: response.data.record }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al crear el registro' }
    } finally {
      creating.value = false
    }
  }

  const updateRecord = async (recordId, data) => {
    updating.value = true
    clearErrors()

    try {
      const response = await api.put(`/api/tables/${tableId}/records/${recordId}`, data)

      toast.success('Registro actualizado exitosamente')
      await loadRecords() // Reload to show updated record

      return { success: true, record: response.data.record }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al actualizar el registro' }
    } finally {
      updating.value = false
    }
  }

  const deleteRecord = async (recordId) => {
    deleting.value = true

    try {
      await api.delete(`/api/tables/${tableId}/records/${recordId}`)

      toast.success('Registro eliminado exitosamente')
      await loadRecords()

      return { success: true }

    } catch (error) {
      toast.error('Error al eliminar el registro')
      return { success: false, message: 'Error al eliminar el registro' }
    } finally {
      deleting.value = false
    }
  }

  // Bulk delete
  const bulkDelete = async (recordIds = null) => {
    const idsToDelete = recordIds || selectedRecords.value

    if (!idsToDelete.length) {
      toast.error('No hay registros seleccionados')
      return { success: false }
    }

    deleting.value = true

    try {
      const response = await api.post(`/api/tables/${tableId}/records/bulk-delete`, {
        record_ids: idsToDelete
      })

      toast.success(`${response.data.deleted_count} registros eliminados`)
      selectedRecords.value = []
      await loadRecords()

      return { success: true, deletedCount: response.data.deleted_count }

    } catch (error) {
      toast.error('Error al eliminar los registros')
      return { success: false, message: 'Error al eliminar los registros' }
    } finally {
      deleting.value = false
    }
  }

  // Export records
  const exportRecords = async () => {
    try {
      const response = await api.post(`/api/tables/${tableId}/export`)

      // TODO: Handle actual file download
      toast.success('Exportación iniciada')
      return { success: true, data: response.data }

    } catch (error) {
      toast.error('Error al exportar los registros')
      return { success: false, message: 'Error al exportar' }
    }
  }

  // Selection methods
  const toggleRecordSelection = (recordId) => {
    const index = selectedRecords.value.indexOf(recordId)
    if (index > -1) {
      selectedRecords.value.splice(index, 1)
    } else {
      selectedRecords.value.push(recordId)
    }
  }

  const selectAllRecords = () => {
    if (records.value?.data) {
      selectedRecords.value = records.value.data.map(record => record.id)
    }
  }

  const clearSelection = () => {
    selectedRecords.value = []
  }

  // Search and filter methods
  const setSearch = (searchTerm) => {
    search.value = searchTerm
    currentPage.value = 1 // Reset to first page
  }

  const setColumnFilter = (column, value) => {
    columnFilters.value[column] = value
    currentPage.value = 1 // Reset to first page
  }

  const clearFilters = () => {
    search.value = ''
    columnFilters.value = {}
    currentPage.value = 1
  }

  const setSorting = (column, direction = null) => {
    if (sortBy.value === column && !direction) {
      // Toggle direction if same column
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortBy.value = column
      sortDirection.value = direction || 'asc'
    }
    currentPage.value = 1 // Reset to first page
  }

  const setPerPage = (newPerPage) => {
    perPage.value = parseInt(newPerPage)
    currentPage.value = 1 // Reset to first page when changing per page
  }

  return {
    // State
    records,
    selectedRecords,
    loading,
    creating,
    updating,
    deleting,
    errors,

    // Pagination & Filters
    currentPage,
    perPage,
    search,
    sortBy,
    sortDirection,
    columnFilters,

    // Computed
    hasRecords,
    hasSelection,
    totalRecords,

    // Methods
    loadRecords,
    createRecord,
    updateRecord,
    deleteRecord,
    bulkDelete,
    exportRecords,

    // Selection
    toggleRecordSelection,
    selectAllRecords,
    clearSelection,

    // Filters
    setSearch,
    setColumnFilter,
    clearFilters,
    setSorting,
    setPerPage,
    clearErrors
  }
}