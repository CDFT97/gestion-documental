<template>
  <div class="text-center space-y-6">
    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
      <CheckCircleIcon class="h-12 w-12 text-green-600 animate-bounce" />
    </div>

    <div class="space-y-3">
      <h3 class="text-xl font-semibold text-gray-900">
        ¡Contraseña restablecida! 🎉
      </h3>
      <p class="text-sm text-gray-600">
        Tu contraseña ha sido actualizada exitosamente.
        <br>
        Ya puedes iniciar sesión con tu nueva contraseña.
      </p>
    </div>

    <div class="pt-4">
      <button @click="$emit('continue')" class="w-full btn-primary flex justify-center items-center">
        <ArrowRightIcon class="h-4 w-4 mr-2" />
        Continuar al login
      </button>
    </div>

  </div>
</template>

<script setup>
import {
  CheckCircleIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline'

defineEmits(['continue'])
</script>

<style scoped>
@keyframes successPulse {

  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.05);
  }
}

.animate-bounce {
  animation: successPulse 2s ease-in-out infinite;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

div {
  animation: fadeInUp 0.6s ease-out;
}

h3 {
  animation-delay: 0.1s;
}

p {
  animation-delay: 0.2s;
}

.bg-blue-50 {
  animation-delay: 0.3s;
}

button {
  animation-delay: 0.4s;
}
</style>