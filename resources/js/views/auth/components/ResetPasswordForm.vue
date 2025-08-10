<template>
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <form v-if="!passwordReset" @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Correo electrónico
          </label>
          <div class="mt-1">
            <input id="email" v-model="form.email" type="email" readonly
              class="input-field bg-gray-50 text-gray-500 cursor-not-allowed" />
            <div class="mt-1 text-xs text-gray-500">
              Este campo no se puede modificar
            </div>
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            Nueva contraseña
          </label>
          <div class="mt-1">
            <div class="relative">
              <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password" required :disabled="loading" :class="[
                  'input-field pr-10',
                  errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                ]" placeholder="Mínimo 8 caracteres" />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center" :disabled="loading">
                <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" />
              </button>
            </div>
            <div v-if="errors.password" class="error-text">
              {{ errors.password[0] }}
            </div>
          </div>
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            Confirmar nueva contraseña
          </label>
          <div class="mt-1">
            <div class="relative">
              <input id="password_confirmation" v-model="form.password_confirmation"
                :type="showPasswordConfirm ? 'text' : 'password'" autocomplete="new-password" required
                :disabled="loading" :class="[
                  'input-field pr-10',
                  (errors.password || passwordMismatch) ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                ]" placeholder="Repite tu nueva contraseña" />
              <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                class="absolute inset-y-0 right-0 pr-3 flex items-center" :disabled="loading">
                <EyeIcon v-if="!showPasswordConfirm" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" />
              </button>
            </div>
            <div v-if="passwordMismatch" class="error-text">
              Las contraseñas no coinciden
            </div>
          </div>
        </div>

        <PasswordStrength :password="form.password" />

        <div v-if="tokenError" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Token inválido o expirado</h3>
              <div class="mt-2 text-sm text-red-700">
                <p>El enlace de recuperación no es válido o ha expirado. Por favor, solicita uno nuevo.</p>
              </div>
              <div class="mt-4">
                <router-link to="/forgot-password"
                  class="text-sm font-medium text-red-800 underline hover:text-red-900">
                  Solicitar nuevo enlace →
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div v-if="errors.general" class="error-text text-center">
          {{ errors.general[0] }}
        </div>

        <div>
          <button type="submit" :disabled="loading || !isFormValid || tokenError"
            class="btn-primary w-full flex justify-center items-center">
            <template v-if="loading">
              <LoadingSpinner class="mr-3" />
              Restableciendo...
            </template>
            <template v-else>
              Restablecer contraseña
            </template>
          </button>
        </div>
      </form>

      <ResetPasswordSuccess v-else @continue="handleContinue" />

      <AuthNavigation v-if="!passwordReset">
        <template #divider-text>¿Recordaste tu contraseña?</template>
        <template #link>
          <router-link to="/login"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            Volver al login
          </router-link>
        </template>
      </AuthNavigation>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePasswordReset } from '@/composables/usePasswordReset'
import { EyeIcon, EyeSlashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PasswordStrength from './PasswordStrength.vue'
import ResetPasswordSuccess from './ResetPasswordSuccess.vue'
import AuthNavigation from './AuthNavigation.vue'

const route = useRoute()
const router = useRouter()
const { resetPassword, loading, errors, clearErrors } = usePasswordReset()

const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const passwordReset = ref(false)
const tokenError = ref(false)

const isFormValid = computed(() => {
  return (
    form.value.password.length >= 8 &&
    form.value.password === form.value.password_confirmation &&
    form.value.token.length > 0 &&
    form.value.email.length > 0
  )
})

const passwordMismatch = computed(() => {
  return (
    form.value.password_confirmation.length > 0 &&
    form.value.password !== form.value.password_confirmation
  )
})

const handleSubmit = async () => {
  const result = await resetPassword({
    token: form.value.token,
    email: form.value.email,
    password: form.value.password,
    password_confirmation: form.value.password_confirmation
  })

  if (result.success) {
    passwordReset.value = true
  } else {
    // Check if it's a token error
    if (result.message && result.message.includes('Token')) {
      tokenError.value = true
    }
  }
}

const handleContinue = () => {
  router.push('/login')
}


// Parse URL parameters
const parseUrlParams = () => {
  // Get token and email from URL query parameters
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''

  // Validate required parameters
  if (!form.value.token || !form.value.email) {
    tokenError.value = true
  }
}

onMounted(() => {
  parseUrlParams()
  clearErrors()

  // Focus on the password field
  const passwordInput = document.getElementById('password')
  if (passwordInput && !tokenError.value) {
    passwordInput.focus()
  }

})

</script>

<style scoped>
input:focus {
  transform: scale(1.01);
  transition: transform 0.1s ease;
}

button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0);
}

input[readonly] {
  background-color: #f9fafb;
  color: #6b7280;
  cursor: not-allowed;
}
</style>