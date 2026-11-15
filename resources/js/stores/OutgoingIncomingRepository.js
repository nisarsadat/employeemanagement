// src/stores/OutgoingIncomingRepository.js
import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';
import { buildListParams } from '@/utils/listHelpers';

export const useOutgoingIncomingRepository = defineStore('OutgoingIncomingRepository', () => {
  const loading = ref(false);
  const error = ref(null);

  // STATE
  const Items = ref([]);
  const Item = ref(null);

  const Search = ref('');
  const page = ref(1);
  const itemsPerPage = ref(10);
  const totalItems = ref(0);

  function getCurrentDate() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  }

  // LIST
  async function FetchOutgoingIncomingData(extraParams = {}) {
    loading.value = true;
    error.value = null;

    try {
      const queryParams = buildListParams(
        {
          page: extraParams.page ?? page.value,
          perPage: extraParams.perPage ?? itemsPerPage.value,
          search: extraParams.search ?? Search.value,
        },
        {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        }
      );

      const finalParams = {
        ...queryParams,
        ...extraParams,
      };

      const res = await axios.get('/outgoing-incomings', {
        params: finalParams,
      });

      // direct mapping
      Items.value = Array.isArray(res?.data?.data) ? res.data.data : [];
      totalItems.value = Number(res?.data?.meta?.total ?? 0);
      page.value = Number(res?.data?.meta?.current_page ?? 1);
      itemsPerPage.value = Number(res?.data?.meta?.per_page ?? 10);

      console.log('OutgoingIncoming list response =>', res.data);
      console.log('OutgoingIncoming Items.value =>', Items.value);

      return res?.data ?? {};
    } catch (e) {
      error.value = e;
      Items.value = [];
      totalItems.value = 0;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function FetchOutgoingIncomingFilterOptions(params = {}) {
    try {
      const res = await axios.get('/outgoing-incomings/filter-options', {
        params,
      });

      return res?.data ?? { data: { options: [] } };
    } catch (e) {
      error.value = e;
      console.error(e);
      return { data: { options: [] } };
    }
  }

  // SINGLE
  async function FetchOutgoingIncomingItem(id) {
    if (!id) return null;

    loading.value = true;
    error.value = null;

    try {
      const res = await axios.get(`/outgoing-incomings/${id}`);
      Item.value = res?.data?.data ?? res?.data ?? null;
      return Item.value;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // CREATE
  async function CreateOutgoingIncoming(payload) {
    loading.value = true;
    error.value = null;

    try {
      const isFormData = payload instanceof FormData;

      const res = await axios.post('/outgoing-incomings', payload);

      return res?.data?.data ?? res?.data ?? null;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function OpenOutgoingIncomingPrintView(payload = {}) {
    const params = {
      ...(payload.filters || {}),

      selected_columns: payload.selected_columns || [],
      column_labels: payload.column_labels || {},
      print_labels: payload.print_labels || {},
      locale: payload.locale || 'fa',
      title: payload.title || '',
      file_name: payload.file_name || 'outgoing-incomings-print',
    };

    const res = await axios.get('/outgoing-incomings/print', {
      params,
      responseType: 'text',
    });

    const printWindow = window.open('', '_blank');

    if (!printWindow) {
      throw new Error('Print window blocked');
    }

    printWindow.document.open();
    printWindow.document.write(res.data);
    printWindow.document.close();

    printWindow.focus();
  }
  // UPDATE
  async function UpdateOutgoingIncoming(id, payload) {
    if (!id) throw new Error('UpdateOutgoingIncoming: id is required');

    loading.value = true;
    error.value = null;

    try {
      if (payload instanceof FormData) {
        payload.delete('_method');
        payload.set('_method', 'PUT');

        const res = await axios.post(`/outgoing-incomings/${id}`, payload);

        return res?.data?.data ?? res?.data ?? null;
      }

      const clean = { ...(payload || {}) };
      delete clean.id;

      const res = await axios.put(`/outgoing-incomings/${id}`, clean);
      return res?.data?.data ?? res?.data ?? null;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // DELETE
  async function DeleteOutgoingIncoming(id) {
    if (!id) return null;

    loading.value = true;
    error.value = null;

    try {
      const res = await axios.delete(`/outgoing-incomings/${id}`);
      return res?.data ?? true;
    } catch (e) {
      error.value = e;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  // ZIP DOWNLOAD
  async function DownloadOutgoingIncomingZip(id) {
    if (!id) return;

    try {
      const res = await axios.get(`/outgoing-incomings/${id}/documents-zip`, {
        responseType: 'arraybuffer',
      });

      const bytes = new Uint8Array(res.data);
      const isZip = bytes[0] === 0x50 && bytes[1] === 0x4b;

      if (!isZip) {
        console.error('Not a ZIP. First bytes:', bytes.slice(0, 20));
        throw new Error('سرور سم ZIP فایل را وانه ستاوه');
      }

      const blob = new Blob([res.data], { type: 'application/zip' });
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

  return {
    loading,
    error,

    Items,
    Item,

    Search,
    page,
    itemsPerPage,
    totalItems,

    FetchOutgoingIncomingData,
    FetchOutgoingIncomingFilterOptions,
    FetchOutgoingIncomingItem,
    CreateOutgoingIncoming,
    UpdateOutgoingIncoming,
    DeleteOutgoingIncoming,
    DownloadOutgoingIncomingZip,
    getCurrentDate,
    OpenOutgoingIncomingPrintView,
  };
});
