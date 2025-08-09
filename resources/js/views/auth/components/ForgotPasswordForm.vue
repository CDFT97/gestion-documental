<template>
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      <form v-if="!emailSent" @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Correo electrónico
          </label>
          <div class="mt-1">
            <input id="email" v-model="email" type="email" autocomplete="email" required :disabled="loading" :class="[
              'input-field',
              errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]" placeholder="tu@email.com" />
            <div v-if="errors.email" class="error-text">
              {{ errors.email[0] }}
            </div>
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
              Enviando...
            </template>
            <template v-else>
              Enviar link de recuperación
            </template>
          </button>
        </div>
      </form>

      <SuccessMessage v-else :email="email" :cooldown="cooldown" @resend="handleResend" />

      <AuthNavigation>
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
import { usePasswordReset } from '@/composables/usePasswordReset'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import SuccessMessage from './SuccessMessage.vue'
import AuthNavigation from './AuthNavigation.vue'

const { requestReset, loading, errors, clearErrors } = usePasswordReset()

const email = ref('')
const emailSent = ref(false)
const cooldown = ref(0)
let cooldownInterval = null

const isFormValid = computed(() => email.value.length > 0)

const handleSubmit = async () => {
  const result = await requestReset(email.value)

  if (result.success) {
    emailSent.value = true
    startCooldown()
  }
}

const handleResend = async () => {
  if (cooldown.value > 0) return

  const result = await requestReset(email.value)
  if (result.success) {
    startCooldown()
  }
}

const startCooldown = () => {
  cooldown.value = 60
  cooldownInterval = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) {
      clearInterval(cooldownInterval)
    }
  }, 1000)
}


onMounted(() => {
  const emailInput = document.getElementById('email')
  if (emailInput) {
    emailInput.focus()
  }
  clearErrors()
})

onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval)
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
</style>