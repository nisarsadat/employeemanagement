import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import axios from '@/axios';
import { toast } from 'vue3-toastify';

export const useUsersRepository = defineStore('UsersRepository', () => {
  const loading = ref(false);

  // Table state
  const Users = ref([]);
  const totalItems = ref(0);
  const page = ref(1);
  const itemsPerPage = ref(10);
  const Search = ref('');

  // Dialogs
  const createDialog = ref(false);
  const updateDialog = ref(false);

  // Current user
  const User = reactive({
    id: null,
    name: '',
    username: '',
    image_url: null,
    roles: [],
    is_online: false,
    current_login_duration: '-',
    last_session_duration: 'Last session: 0m',
    total_login_duration: 'Total: 0m',
    today_login_duration: '0m',
  });

  // All roles
  const rolesAll = ref([]);

  /** ========================= ROLES ========================= **/
  const FetchRolesData = async () => {
    try {
      const { data } = await axios.get('/roles');

      const raw = Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.roles)
        ? data.roles
        : Array.isArray(data)
        ? data
        : [];

      rolesAll.value = raw
        .map((r) => {
          if (typeof r === 'string') return { name: r };
          return { id: r?.id ?? r?.value ?? null, name: r?.name ?? String(r?.title ?? '') };
        })
        .filter((r) => r.name);
    } catch (e) {
      console.error(e);
      rolesAll.value = [];
    }
  };

  const FetchRolesAll = async () => {
    await FetchRolesData();
  };

  /** ==================== LIST (SERVER) ====================== **/
  const FetchUsersData = async (opts) => {
    try {
      loading.value = true;

      if (opts && typeof opts === 'object') {
        if (Number.isFinite(opts.page)) page.value = Number(opts.page);
        if (Number.isFinite(opts.itemsPerPage)) itemsPerPage.value = Number(opts.itemsPerPage);
      }

      const params = {
        page: page.value,
        perPage: itemsPerPage.value,
        search: Search.value || '',
      };

      const { data } = await axios.get('/users', { params });

      const items = Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.items)
        ? data.items
        : [];

      Users.value = items;

      const total = Number.isFinite(data?.total)
        ? data.total
        : Number.isFinite(data?.meta?.total)
        ? data.meta.total
        : 0;

      totalItems.value = total;
    } catch (e) {
      console.error(e);
      Users.value = [];
      totalItems.value = 0;
      toast.error('Failed to load users');
    } finally {
      loading.value = false;
    }
  };

  /** ======================== CREATE ========================= **/
  const CreateUser = async (formData) => {
    try {
      loading.value = true;
      await axios.post('/users', formData);
      toast.success('User created');
      createDialog.value = false;
      await FetchUsersData();
    } finally {
      loading.value = false;
    }
  };

  /** ======================== UPDATE ========================= **/
  const UpdateUser = async (id, formData) => {
    try {
      loading.value = true;

      const keys = [...formData.keys()];
      const hasRoles = keys.includes('roles[]') || keys.includes('roles') || keys.includes('role');

      if (!hasRoles && Array.isArray(User.roles) && User.roles.length) {
        User.roles.forEach((r) => formData.append('roles[]', r));
      }

      formData.append('_method', 'PUT');
      await axios.post(`/users/${id}`, formData);

      toast.success('User updated');
      updateDialog.value = false;
      await FetchUsersData();
    } finally {
      loading.value = false;
    }
  };

  /** ====================== READ ONE ========================= **/
  const FetchUserData = async (id) => {
    try {
      loading.value = true;
      const { data } = await axios.get(`/users/${id}`);

      const candidates = [
        data?.data,
        data?.user,
        data?.data?.user,
        data?.result,
        data?.data?.result,
        data,
      ];

      const payload =
        candidates.find(
          (c) => c && typeof c === 'object' && ('id' in c || 'username' in c || 'name' in c)
        ) || {};

      Object.assign(User, {
        id: payload?.id ?? null,
        name: payload?.name ?? '',
        username: payload?.username ?? '',
        image_url: payload?.image_url ?? payload?.image ?? null,
        roles: (payload?.roles ?? [])
          .map((r) => (typeof r === 'string' ? r : r?.name ?? r?.title))
          .filter(Boolean),
        is_online: !!payload?.is_online,
        current_login_duration: payload?.current_login_duration ?? '-',
        last_session_duration: payload?.last_session_duration ?? 'Last session: 0m',
        total_login_duration: payload?.total_login_duration ?? 'Total: 0m',
        today_login_duration: payload?.today_login_duration ?? '0m',
      });
    } catch (e) {
      console.error(e);
      toast.error('Failed to load user');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  /** ======================== DELETE ========================= **/
  const DeleteUser = async (id) => {
    try {
      loading.value = true;
      await axios.delete(`/users/${id}`);
      toast.success('User deleted');
      await FetchUsersData();
    } catch (e) {
      console.error(e);
      toast.error('Delete failed');
      throw e;
    } finally {
      loading.value = false;
    }
  };

  /** ========================= EDIT FLOW ===================== **/
  const startEdit = async (id) => {
    await Promise.all([FetchRolesData(), FetchUserData(id)]);
    updateDialog.value = true;
  };

  const resetForm = () => {
    Object.assign(User, {
      id: null,
      name: '',
      username: '',
      image_url: null,
      roles: [],
      is_online: false,
      current_login_duration: '-',
      last_session_duration: 'Last session: 0m',
      total_login_duration: 'Total: 0m',
      today_login_duration: '0m',
    });
  };

  return {
    loading,
    Users,
    totalItems,
    page,
    itemsPerPage,
    Search,
    createDialog,
    updateDialog,
    User,
    rolesAll,
    FetchRolesData,
    FetchRolesAll,
    FetchUsersData,
    CreateUser,
    FetchUserData,
    UpdateUser,
    DeleteUser,
    startEdit,
    resetForm,
  };
});