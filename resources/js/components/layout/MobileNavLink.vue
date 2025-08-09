<template>
  <router-link :to="to" :class="[
    'flex items-center px-3 py-2 rounded-md text-base font-medium transition-colors duration-200',
    'hover:text-primary-600 hover:bg-primary-50',
    isActive ? 'text-primary-600 bg-primary-50' : 'text-gray-700'
  ]">
    <span v-if="icon" class="mr-3">{{ icon }}</span>
    <slot />
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  to: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    default: ''
  }
})

const route = useRoute()

const isActive = computed(() => {
  if (props.to === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/'
  }
  return route.path.startsWith(props.to)
})
</script>