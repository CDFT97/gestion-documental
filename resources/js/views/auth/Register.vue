<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <div class="text-6xl mb-4">📄</div>
      </div>
      <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900">
        Crear Cuenta
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Únete al sistema de gestión documental
      </p>
    </div>

    <RegisterForm />

    <Footer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import RegisterForm from '@/views/auth/components/RegisterForm.vue'
import Footer from '@/views/auth/components/Footer.vue'

onMounted(() => {
  document.title = 'Crear Cuenta - Gestión Documental'
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>