import { reactive, computed } from 'vue'
import api from '../api/axios'

const state = reactive({
  user: JSON.parse(localStorage.getItem('user') || 'null'),
  token: localStorage.getItem('token') || null,
  loading: false,
})

export const useAuth = () => {
  const isAuthenticated = computed(() => !!state.token)
  const isAdmin    = computed(() => state.user?.role === 'admin')
  const isTeacher  = computed(() => state.user?.role === 'teacher')
  const isStudent  = computed(() => state.user?.role === 'student')

  const login = async (credentials) => {
    state.loading = true
    try {
      const { data } = await api.post('/login', credentials)
      state.token = data.access_token
      state.user  = data.user
      localStorage.setItem('token', data.access_token)
      localStorage.setItem('user', JSON.stringify(data.user))
      return data
    } finally {
      state.loading = false
    }
  }

  const register = async (payload) => {
    state.loading = true
    try {
      const { data } = await api.post('/register', payload)
      state.token = data.access_token
      state.user  = data.user
      localStorage.setItem('token', data.access_token)
      localStorage.setItem('user', JSON.stringify(data.user))
      return data
    } finally {
      state.loading = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/logout')
    } catch {}
    state.token = null
    state.user  = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  const refreshUser = async () => {
    const { data } = await api.get('/me')
    state.user = data
    localStorage.setItem('user', JSON.stringify(data))
  }

  return {
    state,
    isAuthenticated,
    isAdmin,
    isTeacher,
    isStudent,
    login,
    register,
    logout,
    refreshUser,
  }
}
