import { ref, computed } from 'vue'
import { api } from '../api/axios'
import { useNotifications  } from '@/composables/useNotifications'
const loading = ref(false)
const errors = ref({})
const stats = ref({
  tables: 0,
  documents: 0,
  storage_used: 0,
  last_login: null
})

export function useProfile() {
  const { notificationsActions } = useNotifications()

  const isLoading = computed(() => loading.value)

  const clearErrors = () => {
    errors.value = {}
  }

  const handleValidationErrors = (error) => {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      const message = error.response?.data?.message || 'Error del servidor'
      errors.value = { general: [message] }
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    clearErrors()

    try {
      const response = await api.put('/api/user/profile', profileData)
      notificationsActions.success('Perfil actualizado exitosamente')
      return {
        success: true,
        user: response.data.user,
        message: response.data.message
      }

    } catch (error) {
      handleValidationErrors(error)
      const message = error.response?.data?.message || 'Error al actualizar el perfil'
      notificationsActions.error(message)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const changePassword = async (passwordData) => {
    loading.value = true
    clearErrors()

    try {
      await api.put('/api/user/password', passwordData)

      notificationsActions.success('Contraseña actualizada exitosamente')
      return { success: true, message: 'Contraseña actualizada exitosamente' }

    } catch (error) {
      handleValidationErrors(error)
      const message = error.response?.data?.message || 'Error al cambiar la contraseña'
      notificationsActions.error(message)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }


  return {
    // State
    loading: isLoading,
    errors,
    stats,

    // Methods
    updateProfile,
    changePassword,
    clearErrors
  }
}