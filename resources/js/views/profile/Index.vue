<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div
                class="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                {{ userInitials }}
              </div>
            </div>
            <div class="ml-6">
              <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>
              <p class="text-sm text-gray-500">Gestiona tu información personal y configuración de cuenta</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información del Usuario -->
        <div class="lg:col-span-1">
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900">Información Personal</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Nombre Completo</label>
                <p class="mt-1 text-sm text-gray-900">{{ user?.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1 text-sm text-gray-900">{{ user?.email }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Fecha de Registro</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(user?.created_at) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Estado</label>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  <span class="w-1.5 h-1.5 mr-1.5 bg-green-400 rounded-full"></span>
                  Activo
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulario de Edición -->
        <div class="lg:col-span-2">
          <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900">Editar Perfil</h2>
              <p class="text-sm text-gray-500">Actualiza tu información personal</p>
            </div>
            <div class="px-6 py-4">
              <ProfileForm :user="user"/>
            </div>
          </div>

          <!-- Cambio de Contraseña -->
          <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900">Cambiar Contraseña</h2>
              <p class="text-sm text-gray-500">Actualiza tu contraseña para mantener tu cuenta segura</p>
            </div>
            <div class="px-6 py-4">
              <PasswordForm />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import ProfileForm from './components/ProfileForm.vue'
import PasswordForm from './components/PasswordForm.vue'
import { formatDate } from '../../utils/formatters'

const authStore = useAuthStore()

const user = computed(() => authStore.user)
const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})
</script>