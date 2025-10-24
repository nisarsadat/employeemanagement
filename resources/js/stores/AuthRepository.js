import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/axios'

export const useAuthRepository = defineStore('AuthRepository', () => {
  const user = ref(JSON.parse(sessionStorage.getItem('user') || 'null'))
  const roles = ref(user.value?.roles || [])
  const permissions = ref(user.value?.permissions || [])
  const token = ref(sessionStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => roles.value?.includes('admin'))

  function setSession(payload) {
    token.value = payload.token
    sessionStorage.setItem('token', token.value)

    user.value = payload.user
    roles.value = payload.user?.roles || []
    permissions.value = payload.user?.permissions || []
    sessionStorage.setItem('user', JSON.stringify(user.value))
  }

  async function login({ username, password }) {
    const { data } = await axios.post('/auth/login', { username, password })
    setSession(data)
    return data
  }

  async function fetchMe() {
    const { data } = await axios.get('/auth/me')
    // server returns { user: {...} }
    const payload = { token: sessionStorage.getItem('token'), user: data.user }
    setSession(payload)
    return data.user
  }

  async function logout() {
    try {
      await axios.post('/auth/logout')
    } catch (_) {}
    sessionStorage.removeItem('token')
    sessionStorage.removeItem('user')
    token.value = null
    user.value = null
    roles.value = []
    permissions.value = []
  }

  return {
    // state
    user, roles, permissions, token,
    // getters
    isAuthenticated, isAdmin,
    // actions
    login, logout, fetchMe,
  }
})
