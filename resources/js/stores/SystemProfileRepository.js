import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from '@/axios';

export const useSystemProfileRepository = defineStore('SystemProfile', () => {
  const name = ref('');
  const logoUrl = ref('');
  const defaultLanguage = ref('ps');
  const adminEmail = ref('');
  const phone = ref('');
  const address = ref('');
  const loading = ref(false);
  const loaded = ref(false);

  let pending = null;

  function apply(data) {
    name.value = data?.name || '';
    logoUrl.value = data?.logo_url || '';
    defaultLanguage.value = data?.default_language || 'ps';
    adminEmail.value = data?.admin_email || '';
    phone.value = data?.phone || '';
    address.value = data?.address || '';
    loaded.value = true;
    if (typeof window !== 'undefined') {
      window.systemName = name.value;
      window.systemLogo = logoUrl.value || '';
      if (name.value) document.title = name.value;
    }
  }

  async function fetchProfile() {
    loading.value = true;
    try {
      const { data } = await axios.get('/system-profile');
      apply(data);
      return data;
    } catch {
      loaded.value = true;
      return null;
    } finally {
      loading.value = false;
    }
  }

  function ensureLoaded() {
    if (loaded.value) return Promise.resolve();
    if (!pending) {
      pending = fetchProfile().finally(() => {
        pending = null;
      });
    }
    return pending;
  }

  async function save(formData) {
    loading.value = true;
    try {
      const { data } = await axios.post('/system-profile', formData);
      apply(data);
      return data;
    } finally {
      loading.value = false;
    }
  }

  return {
    name,
    logoUrl,
    defaultLanguage,
    adminEmail,
    phone,
    address,
    loading,
    loaded,
    ensureLoaded,
    fetchProfile,
    save,
  };
});
