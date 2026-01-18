import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';

export const useFormatZoneRepository = defineStore('FormatZoneRepository', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async FetchFormatZones({ page = 1, perPage = 10000, search = '' } = {}) {
      this.loading = true;
      try {
        const { data } = await axios.get(
          `/format-zones?page=${page}&perPage=${perPage}&search=${encodeURIComponent(search)}`
        );
        return data;
      } finally {
        this.loading = false;
      }
    },

    async FetchFormatZone(id) {
      const { data } = await axios.get(`/format-zones/${id}`);
      return data;
    },

    async CreateFormatZone(payload) {
      const { data } = await axios.post(`/format-zones`, payload);
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
