import { useToast } from 'vue-toastification'

export function useNotifications() {
  const toast = useToast()

  const success = (message, options = {}) => {
    toast.success(message, {
      timeout: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      ...options
    })
  }

  const error = (message, options = {}) => {
    toast.error(message, {
      timeout: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      ...options
    })
  }

  const warning = (message, options = {}) => {
    toast.warning(message, {
      timeout: 4000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      ...options
    })
  }

  const info = (message, options = {}) => {
    toast.info(message, {
      timeout: 3000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      ...options
    })
  }

  const clear = () => {
    toast.clear()
  }

  const custom = (type, message, options = {}) => {
    switch (type) {
      case 'success':
        return success(message, options)
      case 'error':
        return error(message, options)
      case 'warning':
        return warning(message, options)
      case 'info':
        return info(message, options)
      default:
        return toast(message, options)
    }
  }

  const notificationsActions = {
    success,
    error,
    warning,
    info,
    clear,
    custom,
  }

  return {
    notificationsActions,
    success,
    error,
    warning,
    info,
    clear,
    custom,
    toast
  }
}