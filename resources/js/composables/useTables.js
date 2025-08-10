import { ref, computed } from 'vue'
import { api } from '../api/axios'
import { useToast } from 'vue-toastification'

export function useTables() {
  const toast = useToast()

  const tables = ref(null)
  const currentTable = ref(null)
  const loading = ref(false)
  const tableLoading = ref(false)
  const error = ref(null)
  const tableError = ref(null)

  const hasTables = computed(() => tables.value?.data?.length > 0)
  const totalTables = computed(() => tables.value?.total || 0)

  const clearErrors = () => {
    error.value = null
    tableError.value = null
  }

  const loadTables = async (page = 1, perPage = 10) => {
    loading.value = true
    clearErrors()

    try {
      const params = { page, per_page: perPage }
      const response = await api.get('/api/tables', { params })

      tables.value = response.data
      return { success: true, data: response.data }

    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar las tablas'
      toast.error(error.value)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const loadTable = async (tableId) => {
    tableLoading.value = true
    clearErrors()

    try {
      const response = await api.get(`/api/tables/${tableId}`)

      currentTable.value = response.data
      return { success: true, table: response.data }

    } catch (err) {
      tableError.value = err.response?.data?.message || 'Error al cargar la tabla'
      toast.error(tableError.value)
      return { success: false, message: tableError.value }
    } finally {
      tableLoading.value = false
    }
  }

  const deleteTable = async (tableId) => {
    try {
      await api.delete(`/api/tables/${tableId}`)

      toast.success('Tabla eliminada exitosamente')

      if (tables.value?.data) {
        const index = tables.value.data.findIndex(table => table.id === tableId)
        if (index > -1) {
          tables.value.data.splice(index, 1)
          tables.value.total = Math.max(0, tables.value.total - 1)
        }
      }

      return { success: true }

    } catch (err) {
      const message = err.response?.data?.message || 'Error al eliminar la tabla'
      toast.error(message)
      return { success: false, message }
    }
  }

  const updateTable = async (tableId, data) => {
    try {
      const response = await api.put(`/api/tables/${tableId}`, data)

      toast.success('Tabla actualizada exitosamente')

      if (currentTable.value?.id === tableId) {
        currentTable.value = { ...currentTable.value, ...response.data.table }
      }

      if (tables.value?.data) {
        const index = tables.value.data.findIndex(table => table.id === tableId)
        if (index > -1) {
          tables.value.data[index] = { ...tables.value.data[index], ...response.data.table }
        }
      }

      return { success: true, table: response.data.table }

    } catch (err) {
      const message = err.response?.data?.message || 'Error al actualizar la tabla'
      toast.error(message)
      return { success: false, message }
    }
  }

  const getTableStats = async (tableId) => {
    try {
      const response = await api.get(`/api/tables/${tableId}/stats`)
      return { success: true, stats: response.data.stats }

    } catch (err) {
      const message = err.response?.data?.message || 'Error al obtener estadísticas'
      return { success: false, message }
    }
  }

  const duplicateTable = async (tableId) => {
    try {
      const response = await api.post(`/api/tables/${tableId}/duplicate`)

      toast.success('Tabla duplicada exitosamente')

      if (tables.value?.data) {
        tables.value.data.unshift(response.data)
        tables.value.total += 1
      }

      return { success: true, table: response.data }

    } catch (err) {
      const message = err.response?.data?.message || 'Error al duplicar la tabla'
      toast.error(message)
      return { success: false, message }
    }
  }

  const exportTable = async (tableId, format = 'xlsx') => {
    try {
      const response = await api.post(`/api/tables/${tableId}/export`,
        { format },
        { responseType: 'blob' }
      )

      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url

      // Get filename from response headers or use default
      const contentDisposition = response.headers['content-disposition']
      let filename = `tabla_${tableId}.${format}`

      if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename="(.+)"/)
        if (filenameMatch) {
          filename = filenameMatch[1]
        }
      }

      link.setAttribute('download', filename)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)

      toast.success('Tabla exportada exitosamente')
      return { success: true }

    } catch (err) {
      const message = err.response?.data?.message || 'Error al exportar la tabla'
      toast.error(message)
      return { success: false, message }
    }
  }

  const searchTables = async (query, page = 1) => {
    loading.value = true
    clearErrors()

    try {
      const params = { search: query, page }
      const response = await api.get('/api/tables/search', { params })

      tables.value = response.data
      return { success: true, data: response.data }

    } catch (err) {
      error.value = err.response?.data?.message || 'Error en la búsqueda'
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const resetState = () => {
    tables.value = null
    currentTable.value = null
    clearErrors()
  }

  const refreshTable = async () => {
    if (currentTable.value?.id) {
      return await loadTable(currentTable.value.id)
    }
    return { success: false, message: 'No hay tabla actual para refrescar' }
  }

  return {
    // State
    tables,
    currentTable,
    loading,
    tableLoading,
    error,
    tableError,

    // Computed
    hasTables,
    totalTables,

    // Methods
    loadTables,
    loadTable,
    deleteTable,
    updateTable,
    getTableStats,
    duplicateTable,
    exportTable,
    searchTables,
    refreshTable,
    resetState,
    clearErrors
  }
}