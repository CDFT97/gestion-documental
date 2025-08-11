<template>
  <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
    <div :class="iconClasses">
      <span class="text-sm">{{ activity.icon }}</span>
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-900">
          {{ activity.title }}
        </p>
        <time class="text-xs text-gray-500">
          {{ formatTime(activity.timestamp) }}
        </time>
      </div>

      <p class="text-sm text-gray-600 mt-1">
        {{ activity.description }}
      </p>

      <div v-if="activity.metadata" class="flex items-center mt-2 text-xs text-gray-500 space-x-4">
        <span v-if="activity.metadata.fileSize">
          📁 {{ activity.metadata.fileSize }}
        </span>
        <span v-if="activity.metadata.recordCount">
          📊 {{ activity.metadata.recordCount }} registros
        </span>
        <span v-if="activity.metadata.status" :class="statusClasses">
          {{ activity.metadata.status }}
        </span>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  activity: {
    type: Object,
    required: true
  }
})

defineEmits(['action'])

const iconClasses = computed(() => [
  'w-8 h-8 rounded-full flex items-center justify-center',
  {
    'bg-blue-100': props.activity.type === 'excel_upload',
    'bg-green-100': props.activity.type === 'pdf_upload',
    'bg-purple-100': props.activity.type === 'data_edit',
    'bg-orange-100': props.activity.type === 'export',
    'bg-red-100': props.activity.type === 'delete',
    'bg-gray-100': !['excel_upload', 'pdf_upload', 'data_edit', 'export', 'delete'].includes(props.activity.type)
  }
])

const statusClasses = computed(() => [
  'px-2 py-1 rounded-full text-xs font-medium',
  {
    'bg-green-100 text-green-800': props.activity.metadata?.status === 'Completado',
    'bg-yellow-100 text-yellow-800': props.activity.metadata?.status === 'Procesando',
    'bg-red-100 text-red-800': props.activity.metadata?.status === 'Error',
    'bg-gray-100 text-gray-800': !['Completado', 'Procesando', 'Error'].includes(props.activity.metadata?.status)
  }
])

const formatTime = (timestamp) => {
  const now = new Date()
  const time = new Date(timestamp)
  const diffMs = now - time
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Ahora'
  if (diffMins < 60) return `Hace ${diffMins}m`
  if (diffHours < 24) return `Hace ${diffHours}h`
  if (diffDays < 7) return `Hace ${diffDays}d`

  return time.toLocaleDateString('es-ES', {
    day: 'numeric',
    month: 'short'
  })
}
</script>