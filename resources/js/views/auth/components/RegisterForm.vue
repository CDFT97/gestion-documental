<!-- resources/js/views/auth/components/RegisterForm.vue -->
<template>
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">
            Nombre completo
          </label>
          <div class="mt-1">
            <input id="name" v-model="form.name" type="text" autocomplete="name" required :disabled="loading" :class="[
              'input-field',
              errors.name ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]" placeholder="Tu nombre completo" />
            <div v-if="errors.name" class="error-text">
              {{ errors.name[0] }}
            </div>
          </div>
        </div>

        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Correo electrónico
          </label>
          <div class="mt-1">
            <input id="email" v-model="form.email" type="email" autocomplete="email" required :disabled="loading"
              :class="[
                'input-field',
                errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
              ]" placeholder="tu@email.com" />
            <div v-if="errors.email" class="error-text">
              {{ errors.email[0] }}
            </div>
          </div>
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            Contraseña
          </label>
          <div class="mt-1 relative">
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
            <div v-if="errors.password" class="error-text">
              {{ errors.password[0] }}
            </div>
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            Confirmar contraseña
          </label>
          <div class="mt-1">
            <div class="relative">
              <input id="password_confirmation" v-model="form.password_confirmation"
                :type="showPasswordConfirm ? 'text' : 'password'" autocomplete="new-password" required
                :disabled="loading" :class="[
                  'input-field pr-10',
                  (errors.password || passwordMismatch) ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                ]" placeholder="Repite tu contraseña" />
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

        <!-- Password Strength Indicator -->
        <PasswordStrength :password="form.password" />

        <!-- Terms Checkbox -->
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input id="terms" v-model="form.terms" type="checkbox" required :disabled="loading"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
          </div>
          <div class="ml-3 text-sm">
            <label for="terms" class="text-gray-700">
              Acepto los
              <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                términos de servicio
              </a>
              y la
              <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                política de privacidad
              </a>
            </label>
          </div>
        </div>

        <!-- General Error -->
        <div v-if="errors.general" class="error-text text-center">
          {{ errors.general[0] }}
        </div>

        <div>
          <button type="submit" :disabled="loading || !isFormValid"
            class="btn-primary w-full flex justify-center items-center">
            <template v-if="loading">
              <LoadingSpinner class="mr-3" />
              Creando cuenta...
            </template>
            <template v-else>
              Crear cuenta
            </template>
          </button>
        </div>
      </form>

      <!-- Login Link -->
      <div class="mt-6">
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300" />
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">¿Ya tienes cuenta?</span>
          </div>
        </div>

        <div class="mt-6">
          <router-link to="/login"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            Iniciar sesión
          </router-link>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PasswordStrength from '@/views/auth/components/PasswordStrength.vue'
import { useToast } from 'vue-toastification'

const toast = useToast()
const router = useRouter()
const { register, loading, errors, clearErrors } = useAuth()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const isFormValid = computed(() => {
  return (
    form.value.name.length > 0 &&
    form.value.email.length > 0 &&
    form.value.password.length >= 8 &&
    form.value.password === form.value.password_confirmation &&
    form.value.terms
  )
})

const passwordMismatch = computed(() => {
  return (
    form.value.password_confirmation.length > 0 &&
    form.value.password !== form.value.password_confirmation
  )
})

const handleSubmit = async () => {
  clearErrors()

  const result = await register({
    name: form.value.name,
    email: form.value.email,
    password: form.value.password,
    password_confirmation: form.value.password_confirmation
  })

  if (result.success) {
    return router.push('/dashboard')
  }

  toast.error(result.message || 'Error al crear la cuenta')
}


onMounted(() => {
  const nameInput = document.getElementById('name')
  if (nameInput) {
    nameInput.focus()
  }
  clearErrors()
})

</script>

<style scoped>
input:focus {
  transform: scale(1.01);
  transition: transform 0.1s ease;
}

/* Hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0);
}
</style>