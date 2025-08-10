<template>
  <div class="flex items-center justify-center" :class="containerClass">
    <svg :class="spinnerClass" :style="customSize ? { width: size, height: size } : {}" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
      </path>
    </svg>
    <span v-if="text" :class="textClass">{{ text }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => {
      const predefinedSizes = ['xs', 'sm', 'md', 'lg', 'xl']
      return predefinedSizes.includes(value) || value.includes('px')
    }
  },
  color: {
    type: String,
    default: 'text-white'
  },
  text: {
    type: String,
    default: ''
  },
  fullscreen: {
    type: Boolean,
    default: false
  }
})

// Determine whether it is a custom size (with px) or a predefined size.
const customSize = computed(() => props.size.includes('px'))

// Predefined sizes
const predefinedSizes = {
  xs: 'h-3 w-3',
  sm: 'h-4 w-4',
  md: 'h-5 w-5',
  lg: 'h-6 w-6',
  xl: 'h-8 w-8' 
}

const spinnerClass = computed(() => [
  'animate-spin',
  props.color,
  // Only apply size class if it is not custom
  !customSize.value ? predefinedSizes[props.size] : ''
])

const containerClass = computed(() =>
  props.fullscreen ? 'fixed inset-0 bg-black bg-opacity-50 z-50' : ''
)

const textClass = computed(() => [
  'ml-2 text-sm',
  props.color
])
</script>

<style scoped>
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>