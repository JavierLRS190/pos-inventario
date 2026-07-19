import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isAuthenticated: state => !!state.token,
    userRoles: state => state.user?.roles || [],
    isAdmin: state => state.user?.roles?.includes('admin'),
    isCajero: state => state.user?.roles?.includes('cajero'),
    isBodeguero: state => state.user?.roles?.includes('bodeguero'),
  },

  actions: {
    async login(email, password) {
      const { data } = await api.post('/login', { email, password })
      this.token = data.token
      this.user = data.user
      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
    },

    async logout() {
      await api.post('/logout')
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }
})
