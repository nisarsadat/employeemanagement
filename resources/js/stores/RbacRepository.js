import { defineStore } from 'pinia'
import axios from '@/axios'

export const useRbacRepository = defineStore('RbacRepository', () => {
  // Roles
  const fetchRoles = async () => (await axios.get('/rbac/roles')).data
  const createRole = async (name) => (await axios.post('/rbac/roles', { name })).data
  const updateRole = async (id, name) => (await axios.put(`/rbac/roles/${id}`, { name })).data
  const deleteRole = async (id) => (await axios.delete(`/rbac/roles/${id}`)).data

  // Permissions
  const fetchPermissions = async () => (await axios.get('/rbac/permissions')).data
  const createPermission = async (name) => (await axios.post('/rbac/permissions', { name })).data
  const updatePermission = async (id, name) => (await axios.put(`/rbac/permissions/${id}`, { name })).data
  const deletePermission = async (id) => (await axios.delete(`/rbac/permissions/${id}`)).data

  // Role permissions
  const getRolePermissions = async (id) => (await axios.get(`/rbac/roles/${id}/permissions`)).data
  const syncRolePermissions = async (id, permissions) => (await axios.post(`/rbac/roles/${id}/permissions/sync`, { permissions })).data

  // Users listing & assignments
  const fetchUsers = async () => (await axios.get('/rbac/users')).data
  const syncUserRoles = async (id, roles) => (await axios.post(`/rbac/users/${id}/roles/sync`, { roles })).data
  const syncUserPermissions = async (id, permissions) => (await axios.post(`/rbac/users/${id}/permissions/sync`, { permissions })).data

  return {
    fetchRoles, createRole, updateRole, deleteRole,
    fetchPermissions, createPermission, updatePermission, deletePermission,
    getRolePermissions, syncRolePermissions,
    fetchUsers, syncUserRoles, syncUserPermissions,
  }
})
