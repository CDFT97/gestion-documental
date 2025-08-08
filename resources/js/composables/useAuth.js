import { ref, computed } from 'vue'
import { api, setAuthToken } from '../api/axios'
import { useToast } from 'vue-toastification'

const user = ref(null)
const token = ref(localStorage.getItem('token'))
const loading = ref(false)
const errors = ref({})

export function useAuth() {
  const toast = useToast()

  // Computed properties
  const isAuthenticated = computed(() => !!user.value)

  // Limpiar errores
  const clearErrors = () => {
    errors.value = {}
  }

  // Manejar errores de validación
  const handleValidationErrors = (error) => {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      const message = error.response?.data?.message || 'Error del servidor'
      errors.value = { general: [message] }
    }
  }

  const checkAuth = async () => {
    if (!token.value) {
      user.value = null
      return false
    }

    try {
      const response = await api.get('/api/user')
      user.value = response.data
      return true
    } catch (error) {
      logout()
      return false
    }
  }

  const login = async (credentials) => {
    loading.value = true
    clearErrors()

    try {
      const response = await api.post('/api/auth/login', credentials)

      // Guardar token y usuario
      token.value = response.data.token
      user.value = response.data.user

      // Configurar token en axios
      setAuthToken(token.value)

      toast.success(response.data.message || 'Login exitoso')
      return { success: true, message: response.data.message }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error en el login' }
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    clearErrors()

    try {
      const response = await api.post('/api/auth/register', userData)

      token.value = response.data.token
      user.value = response.data.user

      // Configurar token en axios
      setAuthToken(token.value)

      toast.success(response.data.message || 'Registro exitoso')
      return { success: true, message: response.data.message }

    } catch (error) {
      handleValidationErrors(error)
      return { success: false, message: 'Error en el registro' }
    } finally {
      loading.value = false
    }
  }

  // Logout
  const logout = async () => {
    loading.value = true

    try {
      if (token.value) {
        await api.post('/api/auth/logout')
      }
      toast.info('Sesión cerrada exitosamente')
    } catch (error) {
      console.log('Error en logout:', error)
    } finally {
      // Limpiar estado local
      user.value = null
      token.value = null
      setAuthToken(null)
      loading.value = false
    }
  }

  return {
    // State
    user,
    token,
    loading,
    errors,
    isAuthenticated,

    // Methods
    login,
    register,
    logout,
    checkAuth,
    clearErrors
  }
}