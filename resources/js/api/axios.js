
import axios from 'axios'
import { useToast } from 'vue-toastification'

const apiClient = axios.create({
  baseURL: window.location.origin,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
})

// Interceptor para requests - agregar token automáticamente
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para responses - manejar errores globalmente
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    const toast = useToast()

    // Manejar diferentes tipos de errores
    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 401:
          // Token inválido o expirado
          localStorage.removeItem('token')
          delete apiClient.defaults.headers.common['Authorization']

          // Solo mostrar toast si no estamos en login/register
          if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
            toast.error('Sesión expirada. Por favor, inicia sesión nuevamente.')
            // Redireccionar al login después de un delay
            setTimeout(() => {
              window.location.href = '/login'
            }, 1500)
          }
          break

        case 403:
          toast.error('No tienes permisos para realizar esta acción.')
          break

        case 404:
          toast.error('Recurso no encontrado.')
          break

        case 422:
          // Errores de validación - se manejan en cada componente
          break

        case 429:
          toast.error('Demasiadas solicitudes. Intenta nuevamente en unos minutos.')
          break

        case 500:
          toast.error('Error interno del servidor. Intenta nuevamente más tarde.')
          break

        default:
          toast.error(data?.message || 'Ha ocurrido un error inesperado.')
      }
    } else if (error.request) {
      // Error de red
      toast.error('Error de conexión. Verifica tu conexión a internet.')
    } else {
      // Otros errores
      toast.error('Ha ocurrido un error inesperado.')
    }

    return Promise.reject(error)
  }
)

// Funciones helper para diferentes tipos de requests
export const api = {
  // GET request
  get: (url, config = {}) => apiClient.get(url, config),

  // POST request
  post: (url, data = {}, config = {}) => apiClient.post(url, data, config),

  // PUT request
  put: (url, data = {}, config = {}) => apiClient.put(url, data, config),

  // PATCH request
  patch: (url, data = {}, config = {}) => apiClient.patch(url, data, config),

  // DELETE request
  delete: (url, config = {}) => apiClient.delete(url, config),

  // Upload file
  upload: (url, formData, config = {}) => {
    return apiClient.post(url, formData, {
      ...config,
      headers: {
        ...config.headers,
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  download: (url, config = {}) => {
    return apiClient.get(url, {
      ...config,
      responseType: 'blob'
    })
  }
}

export const setAuthToken = (token) => {
  if (token) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`
    localStorage.setItem('token', token)
  } else {
    delete apiClient.defaults.headers.common['Authorization']
    localStorage.removeItem('token')
  }
}

export const getAuthToken = () => {
  return localStorage.getItem('token')
}

export default apiClient