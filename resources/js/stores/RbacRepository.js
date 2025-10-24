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

  // Permissions Builder: resources and ensure/delete specific action
  const fetchResources = async () => (await axios.get('/rbac/resources')).data
  const createResource = async (resource) => (await axios.post('/rbac/permissions/resource', { resource })).data
  const deleteResource = async (resource) => (await axios.delete(`/rbac/permissions/resource/${resource}`)).data
  const ensurePermission = async (resource, action) => (await axios.post('/rbac/permissions', { name: `${resource}.${action}` })).data
  const deletePermissionByName = async (resource, action) => {
    const list = await fetchPermissions()
    const name = `${resource}.${action}`
    const hit = list.find(p => p.name === name)
    if (!hit) return null
    return await deletePermission(hit.id)
  }

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
    fetchResources, createResource, deleteResource,
    ensurePermission, deletePermissionByName,
    getRolePermissions, syncRolePermissions,
    fetchUsers, syncUserRoles, syncUserPermissions,
  }
})
