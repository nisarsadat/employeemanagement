import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';

export const useMarjaRepository = defineStore('MarjaRepository', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async FetchMarjas({ page = 1, perPage = 10000, search = '' } = {}) {
      this.loading = true;
      try {
        const { data } = await axios.get(
          `/marjas?page=${page}&perPage=${perPage}&search=${encodeURIComponent(search)}`
        );
        return data;
      } finally {
        this.loading = false;
      }
    },

    async FetchMarja(id) {
      const { data } = await axios.get(`/marjas/${id}`);
      return data;
    },

    async CreateMarja(payload) {
      const { data } = await axios.post(`/marjas`, payload);
      toast.success('مرجع اضافه شد');
      return data;
    },

    async UpdateMarja(id, payload) {
      const { data } = await axios.put(`/marjas/${id}`, payload);
      toast.success('مرجع تغییر شد');
      return data;
    },

    async DeleteMarja(id) {
      await axios.delete(`/marjas/${id}`);
      toast.success('مرجع حذف شد');
    },
  },
});
