// stores/RbacRepository.js
import { defineStore } from 'pinia';
import { ref, reactive, computed } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useRbacRepository = defineStore('RbacRepository', () => {
  // ---- shared state
  const loading = ref(false);
  const error = ref(null);

  // ---- roles state
  const roles = ref([]);
  const rolesTotal = ref(0);
  const rolesPage = ref(1);
  const rolesPerPage = ref(10);
  const rolesSearch = ref('');

  // current role for edit/assign flows
  const currentRole = reactive({
    id: null,
    name: '',
    permissions: [], // array of permission names
  });

  // ---- permissions state
  const permissions = ref([]);
  const permissionsTotal = ref(0);
  const permissionsPage = ref(1);
  const permissionsPerPage = ref(10);
  const permissionsSearch = ref('');

  // for Assign dialog (multi-select)
  const allPermissionNames = computed(() => permissions.value.map((p) => p.name));

  // ---------- Roles ----------
  const fetchRoles = async () => {
    try {
      loading.value = true;
      const params = {
        page: rolesPage.value,
        perPage: rolesPerPage.value,
        search: rolesSearch.value || '',
      };
      // Your RoleController@index returns ResourceCollection or plain; unwrap robustly
      const { data } = await axios.get('/roles', { params });
      roles.value = data?.data ?? data?.items ?? data ?? [];
      rolesTotal.value = Number.isFinite(data?.total)
        ? data.total
        : data?.meta?.total ?? roles.value.length;
    } catch (e) {
      console.error(e);
      error.value = 'Failed to load roles';
      toast.error(error.value);
    } finally {
      loading.value = false;
    }
  };

  const createRole = async (name) => {
    try {
      loading.value = true;
      await axios.post('/roles', { name });
      toast.success('Role created');
      await fetchRoles();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Create role failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const updateRole = async (id, name) => {
    try {
      loading.value = true;
      await axios.put(`/roles/${id}`, { name });
      toast.success('Role updated');
      await fetchRoles();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Update role failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const deleteRole = async (id) => {
    try {
      loading.value = true;
      await axios.delete(`/roles/${id}`);
      toast.success('Role deleted');
      await fetchRoles();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Delete role failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const fetchRole = async (id) => {
    try {
      loading.value = true;
      const { data } = await axios.get(`/roles/${id}`);
      const payload = data?.data ?? data;
      currentRole.id = payload?.id;
      currentRole.name = payload?.name ?? '';
      // accept either full permission objects or names[]
      const perms = payload?.permissions ?? [];
      currentRole.permissions = perms
        .map((p) => (typeof p === 'string' ? p : p.name))
        .filter(Boolean);
    } catch (e) {
      console.error(e);
      toast.error('Failed to load role');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const assignPermissionsToRole = async (roleId, permissionNames) => {
    try {
      loading.value = true;
      await axios.post(`/rbac/roles/${roleId}/permissions/sync`, { permissions: permissionNames });
      toast.success('Permissions updated for role');
      await fetchRole(roleId);
      await fetchRoles();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Assign permissions failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };
  // ---------- Permissions ----------
  const fetchPermissions = async () => {
    try {
      loading.value = true;
      const params = {
        page: permissionsPage.value,
        perPage: permissionsPerPage.value,
        search: permissionsSearch.value || '',
      };
      const { data } = await axios.get('/permissions', { params });
      permissions.value = data?.data ?? data?.items ?? data ?? [];
      permissionsTotal.value = Number.isFinite(data?.total)
        ? data.total
        : data?.meta?.total ?? permissions.value.length;
    } catch (e) {
      console.error(e);
      error.value = 'Failed to load permissions';
      toast.error(error.value);
    } finally {
      loading.value = false;
    }
  };

  const createPermission = async (name, guard_name = 'web') => {
    try {
      loading.value = true;
      await axios.post('/permissions', { name, guard_name });
      toast.success('Permission created');
      await fetchPermissions();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Create permission failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const updatePermission = async (id, name) => {
    try {
      loading.value = true;
      await axios.put(`/permissions/${id}`, { name });
      toast.success('Permission updated');
      await fetchPermissions();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Update permission failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const deletePermission = async (id) => {
    try {
      loading.value = true;
      await axios.delete(`/permissions/${id}`);
      toast.success('Permission deleted');
      await fetchPermissions();
    } catch (e) {
      console.error(e);
      toast.error(e.response?.data?.message || 'Delete permission failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  // quick bare lists for selects (fallback to /rbac/* if available)
  const fetchAllPermissionNames = async () => {
    try {
      const { data } = await axios
        .get('/rbac/permissions')
        .catch(() => axios.get('/permissions', { params: { perPage: 9999 } }));
      const list = Array.isArray(data) ? data : data?.data ?? data?.items ?? [];
      permissions.value = list.map((p) => (typeof p === 'string' ? { name: p } : p));
    } catch (e) {
      // ignore
    }
  };

  return {
    // state
    loading,
    error,

    roles,
    rolesTotal,
    rolesPage,
    rolesPerPage,
    rolesSearch,
    currentRole,
    permissions,
    permissionsTotal,
    permissionsPage,
    permissionsPerPage,
    permissionsSearch,
    allPermissionNames,

    // roles api
    fetchRoles,
    createRole,
    updateRole,
    deleteRole,
    fetchRole,
    assignPermissionsToRole,

    // permissions api
    fetchPermissions,
    createPermission,
    updatePermission,
    deletePermission,
    fetchAllPermissionNames,
  };
});
