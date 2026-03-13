import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

function errText(e, fallback = 'بعضی مشکل پیش آمد') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useFormatZoneRepository = defineStore('FormatZoneRepository', {
  state: () => ({
    loading: false,
    error: null,

    FormatZones: [],

    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    Search: '',
  }),

  actions: {
    async FetchFormatZones() {
      this.loading = true;
      this.error = null;

      try {
        const queryParams = buildListParams(this, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

        const res = await axios.get('/format-zones', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'FormatZones');
        return res.data;
      } catch (e) {
        this.error = errText(e);
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchFormatZone(id) {
      const { data } = await axios.get(`/format-zones/${id}`);
      return data;
    },

    async CreateFormatZone(payload) {
      const { data } = await axios.post('/format-zones', payload);
      toast.success('فارمت زون اضافه شو');
      return data;
    },

    async UpdateFormatZone(id, payload) {
      const { data } = await axios.put(`/format-zones/${id}`, payload);
      toast.success('فارمت زون تغییر شو');
      return data;
    },

    async DeleteFormatZone(id) {
      await axios.delete(`/format-zones/${id}`);
      toast.success('فارمت زون حذف شو');
    },
  },
});