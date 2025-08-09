<template>
  <div class="mt-6">
    <div class="relative">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-300" />
      </div>
      <div class="relative flex justify-center text-sm">
        <span class="px-2 bg-white text-gray-500">
          <slot name="divider-text">O continúa con</slot>
        </span>
      </div>
    </div>

    <div class="mt-6">
      <slot name="link">
        <!-- Contenido por defecto si no se proporciona slot -->
        <div class="text-center text-sm text-gray-500">
          No hay enlaces disponibles
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.relative {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>