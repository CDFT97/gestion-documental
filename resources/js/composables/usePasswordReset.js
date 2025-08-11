import { ref } from 'vue'
import { api } from '../api/axios'
import { useNotifications  } from '@/composables/useNotifications'
const loading = ref(false)
const errors = ref({})

export function usePasswordReset() {
  const { notificationsActions } = useNotifications()

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

  const requestReset = async (email) => {
    loading.value = true
    clearErrors()

    try {
      const response = await api.post('/api/auth/forgot-password', { email })

      notificationsActions.success(response.data.message || 'Link enviado a tu email')
      return { success: true, message: response.data.message }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al enviar el link' }
    } finally {
      loading.value = false
    }
  }

  const resetPassword = async (data) => {
    loading.value = true
    clearErrors()

    try {
      const response = await api.post('/api/auth/reset-password', data)

      notificationsActions.success(response.data.message || 'Contraseña restablecida exitosamente')
      return { success: true, message: response.data.message }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error al restablecer contraseña' }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    loading,
    errors,

    // Methods
    requestReset,
    resetPassword,
    clearErrors
  }
}