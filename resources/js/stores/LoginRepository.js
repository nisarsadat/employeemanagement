// src/stores/LoginRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';
import router from '../router/router'; // ✅ use your index.js export
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { firstAccessiblePath } from '@/rbac';

function setAuthHeader(token) {
  if (token) axios.defaults.headers.common.Authorization = `Bearer ${token}`;
  else delete axios.defaults.headers.common.Authorization;
}
// keep your function name; make it actually work
function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}
// Normalize roles/permissions whether backend sends flat arrays
// or nested user.roles[].permissions[] objects
function normalizeRbac(payload) {
  let roles = payload.roles ?? [];
  let permissions = payload.permissions ?? [];

  if (!roles.length && payload.user?.roles?.length) {
    roles = payload.user.roles.map((r) => (typeof r === 'string' ? r : r?.name)).filter(Boolean);
  } else {
    roles = roles.map((r) => (typeof r === 'string' ? r : r?.name)).filter(Boolean);
  }

  if (!permissions.length) {
    const nested = (payload.user?.roles ?? [])
      .flatMap((r) => r?.permissions ?? [])
      .map((p) => (typeof p === 'string' ? p : p?.name))
      .filter(Boolean);
    permissions = Array.from(new Set(nested));
  } else {
    permissions = permissions.map((p) => (typeof p === 'string' ? p : p?.name)).filter(Boolean);
  }

  return { roles, permissions };
}

export const useLoginRepository = defineStore('LoginRepository', () => {
  const loading = ref(false);
  const user = ref(JSON.parse(sessionStorage.getItem('user') || 'null'));
  const token = ref(sessionStorage.getItem('token') || null);
  const roles = ref(JSON.parse(sessionStorage.getItem('roles') || '[]'));
  const permissions = ref(JSON.parse(sessionStorage.getItem('permissions') || '[]'));
  const isLoggedIn = ref(!!token.value);
  const error = ref(null);
  const rail = ref(false);

  setAuthHeader(token.value);

  const hasRole = (role) => roles.value?.includes(role);
  const hasAnyRole = (arr) => arr?.some((r) => roles.value?.includes(r));
  const can = (perm) => permissions.value?.includes(perm);
  const canAny = (arr) => arr?.some((p) => permissions.value?.includes(p));

  const Login = async (form) => {
    if (loading.value) return; // ✅ prevent double submit
    try {
      loading.value = true;
      error.value = null;

      const { data } = await axios.post('/login', {
        username: form.username,
        password: form.password,
      });

      // ✅ validate payload even if HTTP 200
      if (!data || !data.token || !data.user) {
        const msg = data?.message || 'Invalid credentials or malformed response.';
        throw new Error(msg);
      }

      token.value = data.token;
      user.value = data.user;

      const { roles: r, permissions: p } = normalizeRbac(data);
      roles.value = r;
      permissions.value = p;

      sessionStorage.setItem('token', token.value);
      sessionStorage.setItem('user', JSON.stringify(user.value));
      sessionStorage.setItem('roles', JSON.stringify(roles.value));
      sessionStorage.setItem('permissions', JSON.stringify(permissions.value));

      setAuthHeader(token.value);
      isLoggedIn.value = true;

      toast.success('تاسو سیستم ته په موفقانه ډول دننه شوی', {
        position: 'top-right',
        autoClose: 1500,
      });

      // redirect to first permitted page
      // src/stores/LoginRepository.js (inside Login)
      const target = firstAccessiblePath({ roles: roles.value, permissions: permissions.value });
      console.log('[RBAC] redirect target:', target, {
        roles: roles.value,
        permissions: permissions.value,
      }); // temp debug
      try {
        await router.push(target || '/app/no-access');
      } catch {}

      return true;
    } catch (err) {
      console.error('Login error:', err);
      error.value =
        err?.response?.data?.message ||
        err?.message ||
        'Login failed! Please check your credentials.';
      toast.error(error.value, { position: 'top-right', autoClose: 3000 });
      return false; // ✅ don't rethrow
    } finally {
      loading.value = false;
    }
  };

  const Logout = async () => {
    try {
      await axios.post('/logout');
    } catch {}
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('user');
    sessionStorage.removeItem('roles');
    sessionStorage.removeItem('permissions');
    token.value = null;
    user.value = null;
    roles.value = [];
    permissions.value = [];
    isLoggedIn.value = false;
    setAuthHeader(null);
    toast.success('Logged out', { position: 'top-right', autoClose: 1200 });
    try {
      await router.push('/');
    } catch {}
  };

  const fetchUser = async () => {
    try {
      const { data } = await axios.get('/auth/me'); // or '/user'
      const payload = data?.user ? data : { user: data }; // normalize

      user.value = payload.user || null;
      const { roles: r, permissions: p } = normalizeRbac(payload);
      roles.value = r;
      permissions.value = p;

      sessionStorage.setItem('user', JSON.stringify(user.value));
      sessionStorage.setItem('roles', JSON.stringify(roles.value));
      sessionStorage.setItem('permissions', JSON.stringify(permissions.value));
    } catch {
      await Logout();
    }
  };
  const fetchProfile = async () => {
    try {
      const { data } = await axios.get('/profile');
      const payload = data?.data ?? data;
      return payload;
    } catch (err) {
      console.error('fetchProfile error:', err);
      throw err;
    }
  };

  const updateProfile = async (formData) => {
    try {
      loading.value = true;
      setContentType(null);

      formData.append('_method', 'PUT');

      const { data } = await axios.post('/profile', formData);

      const payload = data?.data?.data ?? data?.data ?? null;
      if (payload) {
        user.value = payload;
        sessionStorage.setItem('user', JSON.stringify(user.value));
      }

      toast.success(data?.message || 'Profile updated successfully.');
      return data;
    } catch (err) {
      console.error('updateProfile error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };
  const changePassword = async (payload) => {
    try {
      loading.value = true;
      const { data } = await axios.put('/profile/change-password', payload);
      toast.success(data?.message || 'Password changed successfully.');
      return data;
    } catch (err) {
      console.error('changePassword error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };
  const toggleRail = () => (rail.value = !rail.value);

  return {
    loading,
    user,
    token,
    roles,
    permissions,
    isLoggedIn,
    error,
    rail,
    Login,
    Logout,
    fetchUser,
    toggleRail,
    hasRole,
    hasAnyRole,
    can,
    canAny,
    fetchProfile,
    updateProfile,
    changePassword,
  };
});
