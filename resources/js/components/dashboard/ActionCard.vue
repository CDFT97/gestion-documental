<template>
  <div :class="cardClasses">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <div class="flex items-center mb-3">
          <div :class="iconClasses">
            <span class="text-2xl">{{ icon }}</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 ml-3">
            {{ title }}
          </h3>
        </div>

        <p class="text-gray-600 mb-4 leading-relaxed">
          {{ description }}
        </p>

        <!-- Features list -->
        <ul v-if="features.length" class="text-sm text-gray-500 mb-4 space-y-1">
          <li v-for="feature in features" :key="feature" class="flex items-center">
            <span class="text-green-500 mr-2">✓</span>
            {{ feature }}
          </li>
        </ul>
      </div>
    </div>

    <button @click="$emit('action')" :disabled="disabled" :class="buttonClasses">
      <span v-if="!loading">{{ buttonText }}</span>
      <div v-else class="flex items-center">
        <LoadingSpinner class="mr-2" :size="'16px'" />
        Procesando...
      </div>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'purple', 'orange', 'red'].includes(value)
  },
  buttonText: {
    type: String,
    default: 'Comenzar'
  },
  features: {
    type: Array,
    default: () => []
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['action'])

const cardClasses = computed(() => [
  'bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200',
  'border border-gray-200 hover:border-gray-300'
])

const iconClasses = computed(() => [
  'p-3 rounded-lg',
  {
    'bg-blue-100': props.color === 'blue',
    'bg-green-100': props.color === 'green',
    'bg-purple-100': props.color === 'purple',
    'bg-orange-100': props.color === 'orange',
    'bg-red-100': props.color === 'red'
  }
])

const buttonClasses = computed(() => [
  'w-full py-3 px-4 rounded-lg font-medium transition-colors duration-200',
  'focus:outline-none focus:ring-2 focus:ring-offset-2',
  'disabled:opacity-50 disabled:cursor-not-allowed',
  {
    'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500': props.color === 'blue',
    'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500': props.color === 'green',
    'bg-purple-600 hover:bg-purple-700 text-white focus:ring-purple-500': props.color === 'purple',
    'bg-orange-600 hover:bg-orange-700 text-white focus:ring-orange-500': props.color === 'orange',
    'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500': props.color === 'red'
  }
])
</script>