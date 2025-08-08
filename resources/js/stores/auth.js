import { defineStore } from 'pinia'
import { useAuth } from '../composables/useAuth'

export const useAuthStore = defineStore('auth', () => {
  const auth = useAuth()

  return {
    // State
    user: auth.user,
    token: auth.token,
    loading: auth.loading,
    errors: auth.errors,
    isAuthenticated: auth.isAuthenticated,

    // Actions
    login: auth.login,
    register: auth.register,
    logout: auth.logout,
    checkAuth: auth.checkAuth,
    clearErrors: auth.clearErrors,
  }
})