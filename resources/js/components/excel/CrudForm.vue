<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
      <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Editar registro' : 'Nuevo registro' }}
        </h3>
        <button @click="handleCancel" class="text-gray-400 hover:text-gray-600 transition-colors">
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="column in table.columns" :key="column.name" :class="getFieldClass(column)">
            <label :for="column.name" class="block text-sm font-medium text-gray-700 mb-1">
              {{ column.original_name }}
              <span v-if="isRequired(column)" class="text-red-500">*</span>
            </label>

            <input v-if="column.type === 'string'" :id="column.name" v-model="formData[column.name]" type="text"
              :class="getInputClass(column.name)" :placeholder="`Ingresa ${column.original_name.toLowerCase()}`"
              :required="isRequired(column)" />

            <input v-else-if="column.type === 'number'" :id="column.name" v-model.number="formData[column.name]"
              type="number" step="any" :class="getInputClass(column.name)"
              :placeholder="`Ingresa ${column.original_name.toLowerCase()}`" :required="isRequired(column)" />

            <input v-else-if="column.type === 'email'" :id="column.name" v-model="formData[column.name]" type="email"
              :class="getInputClass(column.name)" :placeholder="`Ingresa ${column.original_name.toLowerCase()}`"
              :required="isRequired(column)" />

            <input v-else-if="column.type === 'date'" :id="column.name" v-model="formData[column.name]" type="date"
              :class="getInputClass(column.name)" :required="isRequired(column)" />

            <input v-else-if="column.type === 'datetime'" :id="column.name" v-model="formData[column.name]"
              type="datetime-local" :class="getInputClass(column.name)" :required="isRequired(column)" />

            <div v-else-if="column.type === 'boolean'" class="flex items-center space-x-3">
              <label class="flex items-center">
                <input :id="`${column.name}_true`" v-model="formData[column.name]" type="radio" :value="true"
                  :name="column.name" class="text-primary-600 focus:ring-primary-500" />
                <span class="ml-2 text-sm text-gray-700">Sí</span>
              </label>
              <label class="flex items-center">
                <input :id="`${column.name}_false`" v-model="formData[column.name]" type="radio" :value="false"
                  :name="column.name" class="text-primary-600 focus:ring-primary-500" />
                <span class="ml-2 text-sm text-gray-700">No</span>
              </label>
            </div>

            <textarea v-else-if="column.type === 'text'" :id="column.name" v-model="formData[column.name]" rows="3"
              :class="getInputClass(column.name)" :placeholder="`Ingresa ${column.original_name.toLowerCase()}`"
              :required="isRequired(column)"></textarea>

            <input v-else :id="column.name" v-model="formData[column.name]" type="text"
              :class="getInputClass(column.name)" :placeholder="`Ingresa ${column.original_name.toLowerCase()}`"
              :required="isRequired(column)" />

            <div v-if="errors[column.name]" class="mt-1">
              <p v-for="error in errors[column.name]" :key="error" class="text-sm text-red-600">
                {{ error }}
              </p>
            </div>

            <p v-if="getHelpText(column)" class="mt-1 text-xs text-gray-500">
              {{ getHelpText(column) }}
            </p>
          </div>
        </div>

        <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-md p-3">
          <div class="flex">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Error al procesar el formulario
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                  <li v-for="error in errors.general" :key="error">
                    {{ error }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <button type="button" @click="handleCancel" class="btn-secondary" :disabled="creating || updating">
            Cancelar
          </button>

          <button type="submit" class="btn-primary" :disabled="creating || updating || !isFormValid">
            <LoadingSpinner v-if="creating || updating" class="mr-2" size="sm" />
            {{ isEditing ? 'Actualizar' : 'Crear' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const props = defineProps({
  table: {
    type: Object,
    required: true
  },
  record: {
    type: Object,
    default: null
  },
  creating: {
    type: Boolean,
    default: false
  },
  updating: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['submit', 'cancel'])

const formData = ref({})

const isEditing = computed(() => !!props.record)

const isFormValid = computed(() => {
  const requiredFields = props.table.columns.filter(column => isRequired(column))
  return requiredFields.every(column => {
    const value = formData.value[column.name]
    return value !== null && value !== undefined && value !== ''
  })
})

const initializeForm = () => {
  const data = {}

  props.table.columns.forEach(column => {
    if (props.record) {
      // Editing - populate with existing data
      data[column.name] = props.record.data[column.name]
    } else {
      // Creating - set default values
      data[column.name] = getDefaultValue(column)
    }
  })

  formData.value = data
}

// Get default value for column type
const getDefaultValue = (column) => {
  switch (column.type) {
    case 'boolean':
      return false
    case 'number':
      return null
    case 'date':
    case 'datetime':
      return null
    default:
      return ''
  }
}

// Check if field is required
const isRequired = (column) => {
  // For now, assume all non-nullable fields are required
  return !column.nullable && column.name !== 'id'
}

// Get field CSS class
const getFieldClass = (column) => {
  // Full width for text areas and certain types
  if (column.type === 'text') {
    return 'md:col-span-2'
  }
  return ''
}

// Get input CSS class
const getInputClass = (fieldName) => {
  const baseClass = 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500'

  if (props.errors[fieldName]) {
    return baseClass + ' border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
  }

  return baseClass
}

const getHelpText = (column) => {
  const helpTexts = {
    email: 'Formato: ejemplo@correo.com',
    date: 'Formato: DD/MM/AAAA',
    datetime: 'Fecha y hora',
    number: 'Solo números',
    boolean: 'Selecciona Sí o No'
  }

  return helpTexts[column.type] || null
}

// Handle form submission
const handleSubmit = () => {
  const submitData = { ...formData.value }

  // Convert empty strings to null for certain types
  props.table.columns.forEach(column => {
    if (submitData[column.name] === '') {
      if (column.type === 'number' || column.type === 'date' || column.type === 'datetime') {
        submitData[column.name] = null
      }
    }
  })

  emit('submit', submitData)
}

// Handle cancel
const handleCancel = () => {
  emit('cancel')
}

// Initialize form when component mounts or props change
onMounted(() => {
  initializeForm()
})

watch(() => [props.record, props.table], () => {
  initializeForm()
}, { deep: true })
</script>

<style scoped>
.btn-primary {
  @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200;
}

.btn-primary:disabled,
.btn-secondary:disabled {
  @apply opacity-50 cursor-not-allowed;
}

/* Modal backdrop animation */
.modal-backdrop {
  animation: fadeIn 0.2s ease-out;
}

.modal-content {
  animation: slideInDown 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translate3d(0, -100%, 0);
  }

  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}
</style>