<template>
  <div class="flex items-center justify-center" :class="containerClass">
    <svg 
      :class="spinnerClass"
      :style="{ width: size, height: size }"
      fill="none" 
      viewBox="0 0 24 24"
    >
      <circle 
        class="opacity-25" 
        cx="12" 
        cy="12" 
        r="10" 
        stroke="currentColor" 
        stroke-width="4"
      ></circle>
      <path 
        class="opacity-75" 
        fill="currentColor" 
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <span v-if="text" :class="textClass">{{ text }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: '20px'
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

const spinnerClass = computed(() => [
  'animate-spin',
  props.color
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