<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto" @click.stop>
      <div class="p-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" :class="iconContainerClass">
          <component :is="iconComponent" :class="iconClass" />
        </div>

        <div class="text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
            {{ title }}
          </h3>

          <div class="text-sm text-gray-500 mb-6">
            <p>{{ message }}</p>
          </div>
        </div>

        <div class="flex gap-3 justify-center">
          <button type="button" @click="handleCancel"
            class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            {{ cancelText }}
          </button>

          <button type="button" @click="handleConfirm" :disabled="loading" :class="confirmButtonClass">
           <LoadingSpinner 
              v-if="loading" 
              size="sm" 
              color="text-white" 
              class="mr-2" 
            />
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['warning', 'danger', 'info', 'success', 'question'].includes(value)
  },
  danger: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const dialogType = computed(() => {
  return props.danger ? 'danger' : props.type
})

const iconConfig = computed(() => {
  const configs = {
    warning: {
      component: ExclamationTriangleIcon,
      containerClass: 'bg-yellow-100',
      iconClass: 'h-6 w-6 text-yellow-600'
    },
    danger: {
      component: ExclamationTriangleIcon,
      containerClass: 'bg-red-100',
      iconClass: 'h-6 w-6 text-red-600'
    },
    info: {
      component: InformationCircleIcon,
      containerClass: 'bg-blue-100',
      iconClass: 'h-6 w-6 text-blue-600'
    },
    success: {
      component: CheckCircleIcon,
      containerClass: 'bg-green-100',
      iconClass: 'h-6 w-6 text-green-600'
    },
    question: {
      component: QuestionMarkCircleIcon,
      containerClass: 'bg-gray-100',
      iconClass: 'h-6 w-6 text-gray-600'
    }
  }
  return configs[dialogType.value]
})

const iconComponent = computed(() => iconConfig.value.component)
const iconContainerClass = computed(() => iconConfig.value.containerClass)
const iconClass = computed(() => iconConfig.value.iconClass)

const confirmButtonClass = computed(() => {
  const baseClass = 'inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed'

  const variants = {
    warning: 'text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    danger: 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
    info: 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    success: 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500',
    question: 'text-white bg-primary-600 hover:bg-primary-700 focus:ring-primary-500'
  }

  return `${baseClass} ${variants[dialogType.value]}`
})

const handleConfirm = () => {
  if (!props.loading) {
    emit('confirm')
  }
}

const handleCancel = () => {
  if (!props.loading) {
    emit('cancel')
  }
}

// Close on escape key
const handleKeydown = (event) => {
  if (event.key === 'Escape' && !props.loading) {
    handleCancel()
  }
}

// Add event listener for escape key
import { onMounted, onUnmounted } from 'vue'

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  // Focus the confirm button for better accessibility
  document.querySelector('[data-confirm-button]')?.focus()
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
@keyframes modalEnter {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.bg-white {
  animation: modalEnter 0.15s ease-out;
}

@keyframes backdropEnter {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

.fixed.inset-0 {
  animation: backdropEnter 0.15s ease-out;
}

button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

body:has(.fixed.inset-0) {
  overflow: hidden;
}
</style>