<template>
  <div :class="cardClasses">
    <div class="flex items-center">
      <div :class="iconClasses">
        <span class="text-2xl">{{ icon }}</span>
      </div>

      <div class="ml-4 flex-1">
        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">
          {{ title }}
        </p>
        <p :class="valueClasses">
          {{ formattedValue }}
        </p>
        <p class="text-xs text-gray-500 mt-1">
          {{ description }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
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
  description: {
    type: String,
    default: ''
  },
  trend: {
    type: Object,
    default: null
  }
})

const cardClasses = computed(() => [
  'bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200',
  'border-l-4',
  {
    'border-blue-500': props.color === 'blue',
    'border-green-500': props.color === 'green',
    'border-purple-500': props.color === 'purple',
    'border-orange-500': props.color === 'orange',
    'border-red-500': props.color === 'red'
  }
])

const iconClasses = computed(() => [
  'p-3 rounded-full',
  {
    'bg-blue-100': props.color === 'blue',
    'bg-green-100': props.color === 'green',
    'bg-purple-100': props.color === 'purple',
    'bg-orange-100': props.color === 'orange',
    'bg-red-100': props.color === 'red'
  }
])

const valueClasses = computed(() => [
  'text-2xl font-bold',
  {
    'text-blue-600': props.color === 'blue',
    'text-green-600': props.color === 'green',
    'text-purple-600': props.color === 'purple',
    'text-orange-600': props.color === 'orange',
    'text-red-600': props.color === 'red'
  }
])

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString()
  }
  return props.value
})
</script>

<style scoped>
.hover\:shadow-lg:hover {
  transform: translateY(-2px);
}
</style>