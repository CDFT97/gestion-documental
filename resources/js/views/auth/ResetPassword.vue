<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <AuthHeader icon="🔑" title="Restablecer Contraseña"
      subtitle="Ingresa tu nueva contraseña para recuperar el acceso" />
    <ResetPasswordForm />
    <Footer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Header from '@/views/auth/components/Header.vue'
import ResetPasswordForm from '@/views/auth/components/ResetPasswordForm.vue'
import Footer from '@/views/auth/components/Footer.vue'

onMounted(() => {
  document.title = 'Restablecer Contraseña - Gestión Documental'
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>