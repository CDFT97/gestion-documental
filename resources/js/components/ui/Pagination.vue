<template>
  <div v-if="shouldShowPagination" class="flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
      <button @click="goToPrevious" :disabled="!hasPrevious"
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
        Anterior
      </button>
      <button @click="goToNext" :disabled="!hasNext"
        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
        Siguiente
      </button>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Mostrando
          <span class="font-medium">{{ fromRecord }}</span>
          a
          <span class="font-medium">{{ toRecord }}</span>
          de
          <span class="font-medium">{{ totalRecords }}</span>
          resultados
        </p>
      </div>

      <div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <button @click="goToPrevious" :disabled="!hasPrevious"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <span class="sr-only">Anterior</span>
            <ChevronLeftIcon class="h-5 w-5" />
          </button>

          <template v-for="(page, index) in visiblePages" :key="index">
            <button v-if="page.type === 'current'" :aria-current="page"
              class="z-10 bg-primary-50 border-primary-500 text-primary-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors">
              {{ page.number }}
            </button>

            <button v-else-if="page.type === 'page'" @click="goToPage(page.number)"
              class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors">
              {{ page.number }}
            </button>

            <span v-else-if="page.type === 'dots'"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
              ...
            </span>
          </template>

          <button @click="goToNext" :disabled="!hasNext"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <span class="sr-only">Siguiente</span>
            <ChevronRightIcon class="h-5 w-5" />
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  links: {
    type: Object,
    required: true,
    validator: (links) => {
      return links && typeof links === 'object' && Array.isArray(links.data)
    }
  },
  maxVisiblePages: {
    type: Number,
    default: 7
  }
})

const emit = defineEmits(['page-change'])

const currentPage = computed(() => props.links.current_page || 1)
const lastPage = computed(() => props.links.last_page || 1)
const totalRecords = computed(() => props.links.total || 0)

const fromRecord = computed(() => {
  return props.links.from || 0
})

const toRecord = computed(() => {
  return props.links.to || 0
})

const hasPrevious = computed(() => !!props.links.prev_page_url)
const hasNext = computed(() => !!props.links.next_page_url)

const shouldShowPagination = computed(() => {
  return lastPage.value > 1
})

// Generate visible pages with smart truncation
const visiblePages = computed(() => {
  const pages = []
  const current = currentPage.value
  const last = lastPage.value
  const maxVisible = props.maxVisiblePages

  if (last <= maxVisible) {
    // Show all pages if total pages <= maxVisible
    for (let i = 1; i <= last; i++) {
      pages.push({
        type: i === current ? 'current' : 'page',
        number: i
      })
    }
  } else {
    // Smart truncation logic
    const sidePages = Math.floor((maxVisible - 3) / 2) // Reserve space for first, last, and current

    if (current <= sidePages + 2) {
      // Current page is near the beginning
      for (let i = 1; i <= maxVisible - 2; i++) {
        pages.push({
          type: i === current ? 'current' : 'page',
          number: i
        })
      }
      pages.push({ type: 'dots' })
      pages.push({ type: 'page', number: last })
    } else if (current >= last - sidePages - 1) {
      // Current page is near the end
      pages.push({ type: 'page', number: 1 })
      pages.push({ type: 'dots' })
      for (let i = last - maxVisible + 3; i <= last; i++) {
        pages.push({
          type: i === current ? 'current' : 'page',
          number: i
        })
      }
    } else {
      // Current page is in the middle
      pages.push({ type: 'page', number: 1 })
      pages.push({ type: 'dots' })

      for (let i = current - sidePages; i <= current + sidePages; i++) {
        pages.push({
          type: i === current ? 'current' : 'page',
          number: i
        })
      }

      pages.push({ type: 'dots' })
      pages.push({ type: 'page', number: last })
    }
  }

  return pages
})

// Methods
const goToPage = (page) => {
  if (page !== currentPage.value && page >= 1 && page <= lastPage.value) {
    // Create URL with page parameter
    const baseUrl = window.location.origin + window.location.pathname
    const url = `${baseUrl}?page=${page}`
    emit('page-change', url)
  }
}

const goToPrevious = () => {
  if (hasPrevious.value) {
    emit('page-change', props.links.prev_page_url)
  }
}

const goToNext = () => {
  if (hasNext.value) {
    emit('page-change', props.links.next_page_url)
  }
}
</script>

<style scoped>
/* Additional responsive styles */
@media (max-width: 640px) {
  .hidden {
    display: none !important;
  }
}

/* Focus styles for accessibility */
button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
  box-shadow: 0 0 0 2px #3b82f6;
}

/* Hover transitions */
button {
  transition: all 0.15s ease-in-out;
}

/* Disabled state improvements */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

button:disabled:hover {
  background-color: inherit;
  transform: none;
}
</style>