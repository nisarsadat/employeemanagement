// src/stores/OutgoingIncomingRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useOutgoingIncomingRepository = defineStore('OutgoingIncomingRepository', () => {
  const loading = ref(false);
  const error = ref(null);

  // =========================
  // STATE
  // =========================
  const Items = ref([]);
  const total = ref(0);
  const Item = ref(null);

  const Search = ref('');
  const page = ref(1);
  const perPage = ref(100000);
  function getCurrentDate() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  // =========================
  // HELPERS
  // =========================
  function unwrapListResponse(resp) {
    const data = resp?.data?.data ?? resp?.data ?? [];
    const metaTotal =
      resp?.data?.meta?.total ??
      resp?.data?.total ??
      resp?.data?.totalItems ??
      (Array.isArray(data) ? data.length : 0);

    return {
      data: Array.isArray(data) ? data : [],
      total: metaTotal,
    };
  }

  // =========================
  // API METHODS
  // =========================

  // LIST
  async function FetchOutgoingIncomingData(params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const resp = await axios.get('/outgoing-incomings', {
        params: {
          page: params.page ?? page.value,
          perPage: params.perPage ?? perPage.value,
          search: params.search ?? Search.value,
        },
      });

      const { data, total: t } = unwrapListResponse(resp);
      Items.value = data;
      total.value = t;
      return data;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // SINGLE
  async function FetchOutgoingIncomingItem(id) {
    if (!id) return null;
    loading.value = true;
    error.value = null;
    try {
      const resp = await axios.get(`/outgoing-incomings/${id}`);
      Item.value = resp?.data?.data ?? resp?.data ?? null;
      return Item.value;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // CREATE
  async function CreateOutgoingIncoming(formData) {
    loading.value = true;
    error.value = null;
    try {
      const resp = await axios.post('/outgoing-incomings', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return resp?.data?.data ?? resp?.data ?? null;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // UPDATE (FormData safe)
  async function UpdateOutgoingIncoming(id, formData) {
    if (!id) throw new Error('UpdateOutgoingIncoming: id is required');
    loading.value = true;
    error.value = null;
    try {
      // IMPORTANT: avoid duplicate _method
      formData.delete('_method');
      formData.set('_method', 'PUT');

      const resp = await axios.post(`/outgoing-incomings/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return resp?.data?.data ?? resp?.data ?? null;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // DELETE
  async function DeleteOutgoingIncoming(id) {
    if (!id) return;
    loading.value = true;
    error.value = null;
    try {
      const resp = await axios.delete(`/outgoing-incomings/${id}`);
      return resp?.data ?? true;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }
  // =========================
  // ZIP DOWNLOAD (DOCUMENTS)
  // =========================
  async function DownloadOutgoingIncomingZip(id) {
    if (!id) return;

    try {
      const resp = await axios.get(`/outgoing-incomings/${id}/documents-zip`, {
        responseType: 'arraybuffer', // IMPORTANT
      });

      // 🔍 ZIP VALIDATION (PK HEADER)
      const bytes = new Uint8Array(resp.data);
      const isZip = bytes[0] === 0x50 && bytes[1] === 0x4b; // PK

      if (!isZip) {
        console.error('Not a ZIP. First bytes:', bytes.slice(0, 20));
        throw new Error('Server did not return a valid ZIP file');
      }

      // ✅ CREATE ZIP
      const blob = new Blob([resp.data], { type: 'application/zip' });
      const url = URL.createObjectURL(blob);

      const a = document.createElement('a');
      a.href = url;
      a.download = `outgoing_incoming_${id}_documents.zip`;
      document.body.appendChild(a);
      a.click();

      a.remove();
      URL.revokeObjectURL(url);
    } catch (e) {
      console.error('ZIP download failed:', e);
      throw e;
    }
  }

  // =========================
  // EXPORT
  // =========================
  return {
    loading,
    error,

    Items,
    total,
    Item,

    Search,
    page,
    perPage,

    FetchOutgoingIncomingData,
    FetchOutgoingIncomingItem,
    CreateOutgoingIncoming,
    UpdateOutgoingIncoming,
    DeleteOutgoingIncoming,
    DownloadOutgoingIncomingZip,
    getCurrentDate,
  };
});
