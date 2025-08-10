<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Nombre -->
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
        Nombre completo
      </label>
      <input id="name" v-model="form.name" type="text" required :disabled="loading"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors disabled:bg-gray-50 disabled:text-gray-500"
        :class="{
          'border-red-300 focus:ring-red-500': errors.name
        }" placeholder="Ingresa tu nombre completo" />
      <div v-if="errors.name" class="mt-2 text-sm text-red-600">
        {{ errors.name[0] }}
      </div>
    </div>

    <!-- Email -->
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
        Correo electrónico
      </label>
      <input id="email" v-model="form.email" type="email" required :disabled="loading"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors disabled:bg-gray-50 disabled:text-gray-500"
        :class="{
          'border-red-300 focus:ring-red-500': errors.email
        }" placeholder="correo@ejemplo.com" />
      <div v-if="errors.email" class="mt-2 text-sm text-red-600">
        {{ errors.email[0] }}
      </div>
    </div>

    <!-- Errores generales -->
    <div v-if="errors.general" class="rounded-lg bg-red-50 p-4 border border-red-200">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">
            Error al actualizar el perfil
          </h3>
          <div class="mt-2 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
              <li v-for="error in errors.general" :key="error">{{ error }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Botones -->
    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
      <button type="button" @click="resetForm" :disabled="loading"
        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 order-2 sm:order-1">
        Cancelar
      </button>
      <button type="submit" :disabled="loading || !hasChanges"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center order-1 sm:order-2">
        <LoadingSpinner v-if="loading" size="sm" class="mr-2" />
        {{ loading ? 'Guardando...' : 'Guardar cambios' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useProfile } from '../../../composables/useProfile'
import { useAuthStore } from '../../../stores/auth'
import { LoadingSpinner } from '../../../components/ui'

// Props
const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

// Stores y composables
const authStore = useAuthStore()
const { loading, errors, updateProfile, clearErrors } = useProfile()

// State local
const originalForm = ref({})

// Form data
const form = reactive({
  name: '',
  email: ''
})

// Computed
const hasChanges = computed(() => {
  return JSON.stringify(form) !== JSON.stringify(originalForm.value)
})

// Methods
const initializeForm = () => {
  if (props.user) {
    form.name = props.user.name || ''
    form.email = props.user.email || ''

    // Guardar copia del estado inicial
    originalForm.value = { ...form }
  }
}

const resetForm = () => {
  Object.assign(form, originalForm.value)
  clearErrors()
}

const handleSubmit = async () => {
  if (!hasChanges.value) return

  const result = await updateProfile(form)

  if (result.success) {
    // Actualizar el estado inicial con los nuevos datos
    originalForm.value = { ...form }

    // Actualizar el usuario en el authStore
    authStore.updateUser(result.user)
  }
}

// Watch for user prop changes
watch(() => props.user, (newUser) => {
  if (newUser) {
    initializeForm()
  }
}, { immediate: true, deep: true })
</script>