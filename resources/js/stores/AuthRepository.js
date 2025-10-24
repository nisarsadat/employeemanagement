import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/axios'
import router from '@/router/router'

export const useAuthRepository = defineStore('AuthRepository', () => {
  const user = ref(JSON.parse(sessionStorage.getItem('user') || 'null'))
  const roles = ref(user.value?.roles || [])
  const permissions = ref(user.value?.permissions || [])
  const token = ref(sessionStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => roles.value?.includes('admin'))

  function setSession(payload) {
    token.value = payload.token
    if (token.value) sessionStorage.setItem('token', token.value)

    user.value = payload.user
    roles.value = payload.roles || payload.user?.roles || []
    permissions.value = payload.permissions || payload.user?.permissions || []
    sessionStorage.setItem('user', JSON.stringify(user.value))
  }

  async function login({ username, password }) {
    const { data } = await axios.post('/auth/login', { username, password })
    setSession(data)
    // redirect to first allowed route
    try { router.replace(firstAllowedRoute()) } catch (_) {}
    return data
  }

  async function fetchMe() {
    const { data } = await axios.get('/auth/me')
    // server returns { user, roles, permissions }
    const payload = { token: sessionStorage.getItem('token'), ...data }
    setSession(payload)
    return data.user
  }

  // Permission helpers
  const can = (p) => Array.isArray(permissions.value) && permissions.value.includes(p)
  const any = (arr) => Array.isArray(arr) && arr.some((p) => can(p))
  const hasRole = (r) => Array.isArray(roles.value) && roles.value.includes(r)

  // first allowed route by priority
  function firstAllowedRoute() {
    const order = [
      '/rbac/roles',
      '/rbac/permissions',
      '/rbac/permissions-builder',
      '/rbac/assign',
      '/employees',
      '/users',
      '/resources',
    ]
    for (const p of order) {
      // role-gated
      if (p.startsWith('/rbac/') && hasRole('admin')) return p
      // permission-gated examples
      if (p === '/employees' && can('employees.view')) return p
      if (p === '/users' && can('users.view')) return p
      if (p === '/resources' && can('resources.view')) return p
    }
    return '/'
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
    can, any, hasRole, firstAllowedRoute,
    // actions
    login, logout, fetchMe,
  }
})
