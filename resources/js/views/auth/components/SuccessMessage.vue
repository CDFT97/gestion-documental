<template>
  <div class="text-center space-y-4">
    <div class="text-6xl mb-4">📧</div>
    <h3 class="text-lg font-medium text-gray-900">
      ¡Email enviado!
    </h3>
    <p class="text-sm text-gray-600">
      Hemos enviado un link de recuperación a:
      <br>
      <strong class="text-primary-600">{{ email }}</strong>
    </p>
    <p class="text-xs text-gray-500">
      Revisa tu bandeja de entrada y carpeta de spam
    </p>

    <!-- Resend Button -->
    <button @click="$emit('resend')" :disabled="cooldown > 0" :class="[
      'text-sm font-medium transition-colors',
      cooldown > 0
        ? 'text-gray-400 cursor-not-allowed'
        : 'text-primary-600 hover:text-primary-500 cursor-pointer'
    ]">
      {{ cooldown > 0 ? `Reenviar en ${cooldown}s` : 'Reenviar email' }}
    </button>

    <div v-if="cooldown > 0" class="w-full bg-gray-200 rounded-full h-1">
      <div class="bg-primary-600 h-1 rounded-full transition-all duration-1000"
        :style="{ width: `${((60 - cooldown) / 60) * 100}%` }"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  email: {
    type: String,
    required: true
  },
  cooldown: {
    type: Number,
    default: 0
  }
})

defineEmits(['resend'])
</script>

<style scoped>
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
  animation: fadeInUp 0.5s ease-out;
}
</style>