<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ table.original_filename }}
          </h3>
          <span class="text-sm text-gray-500">
            {{ totalRecords }} registros
          </span>
        </div>

        <div class="flex items-center space-x-3">
          <div class="relative">
            <input v-model="searchInput" @input="handleSearchInput" type="text" placeholder="Buscar..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
          </div>

          <!--Per Page -->
          <div class="flex items-center space-x-2">
            <label for="perPage" class="text-sm text-gray-700 whitespace-nowrap">Por página:</label>
            <select id="perPage" v-model="perPageValue" @change="handlePerPageChange"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="200">200</option>
            </select>
          </div>

          <button @click="showCreateForm = true" class="btn-primary flex items-center">
            <PlusIcon class="h-4 w-4 mr-2" />
            Agregar
          </button>

          <button @click="handleExport" :disabled="!hasRecords" class="btn-secondary flex items-center">
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            Exportar
          </button>

          <div v-if="hasSelection" class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">
              {{ selectedRecords.length }} seleccionados
            </span>
            <button @click="confirmBulkDelete" :disabled="deleting" class="btn-danger flex items-center">
              <TrashIcon class="h-4 w-4 mr-1" />
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="hasColumnFilters" class="bg-gray-50 px-6 py-3 border-b border-gray-200">
      <div class="flex items-center space-x-4">
        <span class="text-sm font-medium text-gray-700">Filtros:</span>
        <div class="flex flex-wrap gap-2">
          <div v-for="column in table.columns" :key="column.name" class="flex items-center">
            <label class="text-xs text-gray-600 mr-1">{{ column.original_name }}:</label>
            <input v-model="columnFilters[column.name]" @input="handleColumnFilter(column.name, $event.target.value)"
              type="text"
              class="w-24 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-primary-500"
              :placeholder="column.original_name" />
          </div>
        </div>
        <button @click="clearAllFilters" class="text-xs text-primary-600 hover:text-primary-700">
          Limpiar filtros
        </button>
      </div>
    </div>

    <div v-if="loading" class="p-8 text-center">
      <LoadingSpinner size="lg" color="text-primary-600" />
      <p class="text-gray-600 mt-2">Cargando registros...</p>
    </div>

    <div v-else-if="!hasRecords" class="p-8 text-center">
      <div class="text-4xl mb-4">📋</div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        No hay registros
      </h3>
      <p class="text-gray-500 mb-4">
        {{ search ? 'No se encontraron registros con ese criterio' : 'Agrega el primer registro' }}
      </p>
      <button v-if="!search" @click="showCreateForm = true" class="btn-primary">
        Agregar registro
      </button>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <input type="checkbox" :checked="records?.data?.length && selectedRecords.length === records.data.length"
                @change="toggleSelectAll" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
            </th>

            <th v-for="column in table.columns" :key="column.name"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="handleSort(column.name)">
              <div class="flex items-center space-x-1">
                <span>{{ column.original_name }}</span>
                <div class="flex flex-col">
                  <ChevronUpIcon :class="[
                    'h-3 w-3',
                    sortBy === column.name && sortDirection === 'asc'
                      ? 'text-primary-600'
                      : 'text-gray-300'
                  ]" />
                  <ChevronDownIcon :class="[
                    'h-3 w-3 -mt-1',
                    sortBy === column.name && sortDirection === 'desc'
                      ? 'text-primary-600'
                      : 'text-gray-300'
                  ]" />
                </div>
              </div>
            </th>

            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="record in records.data" :key="record.id"
            :class="selectedRecords.includes(record.id) ? 'bg-primary-50' : 'hover:bg-gray-50'">
            <td class="px-6 py-4 whitespace-nowrap">
              <input type="checkbox" :checked="selectedRecords.includes(record.id)"
                @change="toggleRecordSelection(record.id)"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
            </td>

            <td v-for="column in table.columns" :key="column.name"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <span :class="getCellClass(record.data[column.name], column.type)">
                {{ formatCellValue(record.data[column.name], column.type) }}
              </span>
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end space-x-2">
                <button @click="editRecord(record)" class="text-primary-600 hover:text-primary-900">
                  <PencilIcon class="h-4 w-4" />
                </button>
                <button @click="confirmDelete(record)" :disabled="deleting" class="text-red-600 hover:text-red-900">
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="records?.links && records.last_page > 1" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando {{ records.from || 0 }} a {{ records.to || 0 }} de {{ totalRecords }} registros
        </div>
        <Pagination :links="records" @page-change="handlePageChange" />
      </div>
    </div>

    <CrudForm v-if="showCreateForm || showEditForm" :table="table" :record="recordToEdit" :creating="creating"
      :updating="updating" :errors="errors" @submit="handleFormSubmit" @cancel="closeForm" />

    <ConfirmDialog v-if="showDeleteModal" :title="recordToDelete ? 'Eliminar registro' : 'Eliminar registros'"
      :message="deleteMessage" confirm-text="Eliminar" cancel-text="Cancelar" danger @confirm="handleDelete"
      @cancel="cancelDelete" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  MagnifyingGlassIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'
import { useCrudOperations } from '@/composables/useCrudOperations'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import CrudForm from '@/components/excel/CrudForm.vue'

const props = defineProps({
  table: {
    type: Object,
    required: true
  }
})

const {
  records,
  selectedRecords,
  loading,
  creating,
  updating,
  deleting,
  errors,
  currentPage,
  perPage,
  search,
  sortBy,
  sortDirection,
  columnFilters,
  hasRecords,
  hasSelection,
  totalRecords,
  loadRecords,
  createRecord,
  updateRecord,
  deleteRecord,
  bulkDelete,
  exportRecords,
  toggleRecordSelection,
  selectAllRecords,
  clearSelection,
  setSearch,
  setColumnFilter,
  clearFilters,
  setSorting,
  setPerPage,
  clearErrors
} = useCrudOperations(props.table.id)

const searchInput = ref('')
const showCreateForm = ref(false)
const showEditForm = ref(false)
const recordToEdit = ref(null)
const showDeleteModal = ref(false)
const recordToDelete = ref(null)
const searchTimeout = ref(null)
const perPageValue = ref(perPage.value || 15)

const hasColumnFilters = computed(() => {
  return props.table.columns?.length > 0
})

const deleteMessage = computed(() => {
  if (recordToDelete.value) {
    return `¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.`
  }
  return `¿Estás seguro de que quieres eliminar ${selectedRecords.value.length} registros? Esta acción no se puede deshacer.`
})

const handleSearchInput = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(() => {
    setSearch(searchInput.value)
    loadRecords()
  }, 300)
}

const handleColumnFilter = (column, value) => {
  setColumnFilter(column, value)

  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(() => {
    loadRecords()
  }, 300)
}

const clearAllFilters = () => {
  searchInput.value = ''
  Object.keys(columnFilters.value).forEach(key => {
    columnFilters.value[key] = ''
  })
  clearFilters()
  loadRecords()
}

const handleSort = (column) => {
  setSorting(column)
  loadRecords()
}

const handlePageChange = (url) => {
  const urlObj = new URL(url)
  const page = urlObj.searchParams.get('page')
  loadRecords({ page: parseInt(page) })
}

const handlePerPageChange = () => {
  setPerPage(perPageValue.value)
  loadRecords({ page: 1 }) //  Reset to the first page when perPage changes
}

const toggleSelectAll = () => {
  if (selectedRecords.value.length === records.value?.data?.length) {
    clearSelection()
  } else {
    selectAllRecords()
  }
}

const editRecord = (record) => {
  recordToEdit.value = record
  showEditForm.value = true
  clearErrors()
}

const closeForm = () => {
  showCreateForm.value = false
  showEditForm.value = false
  recordToEdit.value = null
  clearErrors()
}

const handleFormSubmit = async (data) => {
  let result

  if (recordToEdit.value) {
    result = await updateRecord(recordToEdit.value.id, data)
  } else {
    result = await createRecord(data)
  }

  if (result.success) {
    closeForm()
  }
}

const confirmDelete = (record) => {
  recordToDelete.value = record
  showDeleteModal.value = true
}

const confirmBulkDelete = () => {
  recordToDelete.value = null
  showDeleteModal.value = true
}

const handleDelete = async () => {
  if (recordToDelete.value) {
    await deleteRecord(recordToDelete.value.id)
  } else {
    await bulkDelete()
  }
  cancelDelete()
}

const cancelDelete = () => {
  showDeleteModal.value = false
  recordToDelete.value = null
}

const handleExport = async () => {
  await exportRecords()
}

const formatCellValue = (value, type) => {
  if (value === null || value === undefined || value === '') {
    return '-'
  }

  switch (type) {
    case 'date':
      return new Date(value).toLocaleDateString('es-ES')
    case 'datetime':
      return new Date(value).toLocaleString('es-ES')
    case 'number':
      return typeof value === 'number' ? value.toLocaleString('es-ES') : value
    case 'boolean':
      return value ? 'Sí' : 'No'
    default:
      return value
  }
}

const getCellClass = (value, type) => {
  const baseClass = ''

  if (value === null || value === undefined || value === '') {
    return baseClass + ' text-gray-400 italic'
  }

  switch (type) {
    case 'number':
      return baseClass + ' font-mono text-right'
    case 'boolean':
      return baseClass + ' font-medium ' + (value ? 'text-green-600' : 'text-red-600')
    case 'date':
    case 'datetime':
      return baseClass + ' font-mono'
    default:
      return baseClass
  }
}
watch(() => perPage.value, (newValue) => {
  perPageValue.value = newValue
})

onMounted(() => {
  perPageValue.value = perPage.value || 25
  loadRecords()
})

watch(() => props.table.id, () => {
  loadRecords()
})
</script>