<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Contraseña actual -->
    <div>
      <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
        Contraseña actual
      </label>
      <div class="relative">
        <input id="current_password" v-model="form.current_password" :type="showCurrentPassword ? 'text' : 'password'"
          required :disabled="loading"
          class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors disabled:bg-gray-50 disabled:text-gray-500"
          :class="{
            'border-red-300 focus:ring-red-500': errors.current_password
          }" placeholder="Ingresa tu contraseña actual" />
        <button type="button" @click="showCurrentPassword = !showCurrentPassword"
          class="absolute inset-y-0 right-0 pr-4 flex items-center" :disabled="loading">
          <svg v-if="!showCurrentPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
          </svg>
        </button>
      </div>
      <div v-if="errors.current_password" class="mt-2 text-sm text-red-600">
        {{ errors.current_password[0] }}
      </div>
    </div>

    <!-- Nueva contraseña -->
    <div>
      <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
        Nueva contraseña
      </label>
      <div class="relative">
        <input id="password" v-model="form.password" :type="showNewPassword ? 'text' : 'password'" required
          :disabled="loading"
          class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors disabled:bg-gray-50 disabled:text-gray-500"
          :class="{
            'border-red-300 focus:ring-red-500': errors.password
          }" placeholder="Ingresa una nueva contraseña" />
        <button type="button" @click="showNewPassword = !showNewPassword"
          class="absolute inset-y-0 right-0 pr-4 flex items-center" :disabled="loading">
          <svg v-if="!showNewPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
          </svg>
        </button>
      </div>
      <div v-if="errors.password" class="mt-2 text-sm text-red-600">
        {{ errors.password[0] }}
      </div>

      <PasswordStrength v-if="form.password" :password="form.password" class="mt-3" />
    </div>

    <!-- Confirmar nueva contraseña -->
    <div>
      <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
        Confirmar nueva contraseña
      </label>
      <div class="relative">
        <input id="password_confirmation" v-model="form.password_confirmation"
          :type="showConfirmPassword ? 'text' : 'password'" required :disabled="loading"
          class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors disabled:bg-gray-50 disabled:text-gray-500"
          :class="{
            'border-red-300 focus:ring-red-500': errors.password_confirmation || passwordMismatch
          }" placeholder="Confirma tu nueva contraseña" />
        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
          class="absolute inset-y-0 right-0 pr-4 flex items-center" :disabled="loading">
          <svg v-if="!showConfirmPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
          </svg>
        </button>
      </div>
      <div v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600">
        {{ errors.password_confirmation[0] }}
      </div>
      <div v-else-if="passwordMismatch && form.password_confirmation" class="mt-2 text-sm text-red-600">
        Las contraseñas no coinciden
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
            Error al cambiar la contraseña
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
      <button type="submit" :disabled="loading || !canSubmit"
        class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center order-1 sm:order-2">
        <LoadingSpinner v-if="loading" size="sm" class="mr-2" />
        {{ loading ? 'Cambiando...' : 'Cambiar contraseña' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useProfile } from '../../../composables/useProfile'
import { LoadingSpinner } from '../../../components/ui'
import PasswordStrength from '../../auth/components/PasswordStrength.vue'

const emit = defineEmits(['password-updated'])

const { changePassword, loading, errors, clearErrors } = useProfile()

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const passwordMismatch = computed(() => {
  return form.password && form.password_confirmation && form.password !== form.password_confirmation
})

const canSubmit = computed(() => {
  return form.current_password &&
    form.password &&
    form.password_confirmation &&
    !passwordMismatch.value &&
    form.password.length >= 8
})

const resetForm = () => {
  form.current_password = ''
  form.password = ''
  form.password_confirmation = ''
  clearErrors()
}

const handleSubmit = async () => {
  if (!canSubmit.value) return

  const result = await changePassword({
    current_password: form.current_password,
    password: form.password,
    password_confirmation: form.password_confirmation
  })

  if (result.success) {
    resetForm()
    emit('password-updated')
  }
}
</script>