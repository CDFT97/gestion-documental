
import axios from 'axios'
import { useNotifications  } from '@/composables/useNotifications'

const { notificationsActions } = useNotifications()
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

// Interceptor for requests - automatically add token
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

// invalid or expired token
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {

    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 401:
          localStorage.removeItem('token')
          delete apiClient.defaults.headers.common['Authorization']

          // Only show toast if we are not in login/register
          if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
            notificationsActions.error('Sesión expirada. Por favor, inicia sesión nuevamente.')
            setTimeout(() => {
              window.location.href = '/login'
            }, 1500)
          }
          break

        case 403:
          notificationsActions.error('No tienes permisos para realizar esta acción.')
          break

        case 404:
          notificationsActions.error('Recurso no encontrado.')
          break

        case 422:
          break

        case 429:
          notificationsActions.error('Demasiadas solicitudes. Intenta nuevamente en unos minutos.')
          break

        case 500:
          notificationsActions.error('Error interno del servidor. Intenta nuevamente más tarde.')
          break

        default:
          notificationsActions.error(data?.message || 'Ha ocurrido un error inesperado.')
      }
    } else if (error.request) {
      // Red error
      notificationsActions.error('Error de conexión. Verifica tu conexión a internet.')
    } else {
      // Other errors
      notificationsActions.error('Ha ocurrido un error inesperado.')
    }

    return Promise.reject(error)
  }
)

export const api = {
  get: (url, config = {}) => apiClient.get(url, config),

  post: (url, data = {}, config = {}) => apiClient.post(url, data, config),

  put: (url, data = {}, config = {}) => apiClient.put(url, data, config),

  patch: (url, data = {}, config = {}) => apiClient.patch(url, data, config),

  delete: (url, config = {}) => apiClient.delete(url, config),

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