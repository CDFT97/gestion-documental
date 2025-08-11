<!-- resources/js/views/auth/components/LoginForm.vue -->
<template>
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <form @submit.prevent="handleSubmit" class="space-y-6">
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

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            Contraseña
          </label>
          <div class="mt-1 relative">
            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password" required :disabled="loading" :class="[
                'input-field pr-10',
                errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
              ]" placeholder="Tu contraseña" />
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

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember" v-model="form.remember" type="checkbox" :disabled="loading"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              Recordarme
            </label>
          </div>

          <div class="text-sm">
            <router-link to="/forgot-password"
              class="font-medium text-primary-600 hover:text-primary-500 transition-colors">
              ¿Olvidaste tu contraseña?
            </router-link>
          </div>
        </div>

        <div v-if="errors.general" class="error-text text-center">
          {{ errors.general[0] }}
        </div>

        <div>
          <button type="submit" :disabled="loading || !isFormValid"
            class="btn-primary w-full flex justify-center items-center">
            <template v-if="loading">
              <LoadingSpinner class="mr-3" />
              Iniciando sesión...
            </template>
            <template v-else>
              Iniciar Sesión
            </template>
          </button>
        </div>
      </form>

      <div class="mt-6">
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300" />
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">¿No tienes cuenta?</span>
          </div>
        </div>

        <div class="mt-6">
          <router-link to="/register"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            Crear cuenta nueva
          </router-link>
        </div>
      </div>

      <div v-if="isDevelopment" class="mt-4">
        <button type="button" @click="fillDemoCredentials"
          class="w-full py-2 px-4 border border-dashed border-gray-300 rounded-md text-sm text-gray-500 hover:text-gray-700 hover:border-gray-400 transition-colors">
          🧪 Llenar credenciales demo (Ctrl+D)
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()
const route = useRoute()
const { login, loading, errors, clearErrors } = useAuth()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const showPassword = ref(false)

const isDevelopment = computed(() => {
  return import.meta.env.DEV
})

const isFormValid = computed(() => {
  return form.value.email.length > 0 && form.value.password.length > 0
})

const handleSubmit = async () => {
  clearErrors()

  const result = await login({
    email: form.value.email,
    password: form.value.password
  })

  if (result.success) {
    const redirectTo = route.query.redirect || '/dashboard'
    router.push(redirectTo)
  }
}

const fillDemoCredentials = () => {
  form.value.email = 'test@example.com'
  form.value.password = 'password123'
}

const handleKeydown = (event) => {
  if (event.ctrlKey && event.key === 'd') {
    event.preventDefault()
    fillDemoCredentials()
  }
}

onMounted(() => {
  const emailInput = document.getElementById('email')
  if (emailInput) {
    emailInput.focus()
  }

  clearErrors()

  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
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
</style>