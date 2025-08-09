<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <Header icon="📄" title="Crear Cuenta" subtitle="Únete al sistema de gestión documental" />
    <RegisterForm />
    <Footer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Header from '@/views/auth/components/Header.vue'
import RegisterForm from '@/views/auth/components/RegisterForm.vue'
import Footer from '@/views/auth/components/Footer.vue'

onMounted(() => {
  document.title = 'Crear Cuenta - Gestión Documental'
})
</script>