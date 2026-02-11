    // src/stores/LetryFormatRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';

export const useLetryFormatRepository = defineStore('LetryFormatRepository', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async FetchLetryFormats({ page = 1, perPage = 10000, search = '' } = {}) {
      this.loading = true;
      try {
        const { data } = await axios.get(
          `/letry-formats?page=${page}&perPage=${perPage}&search=${encodeURIComponent(search)}`
        );
        return data;
      } finally {
        this.loading = false;
      }
    },

    async FetchLetryFormat(id) {
      const { data } = await axios.get(`/letry-formats/${id}`);
      return data;
    },

    async CreateLetryFormat(payload) {
      const { data } = await axios.post(`/letry-formats`, payload);
      toast.success('فورمت لتری اضافه شد');
      return data;
    },

    async UpdateLetryFormat(id, payload) {
      const { data } = await axios.put(`/letry-formats/${id}`, payload);
      toast.success('فورمت لتری تغییر شد');
      return data;
    },

    async DeleteLetryFormat(id) {
      await axios.delete(`/letry-formats/${id}`);
      toast.success('فورمت لتری حذف شد');
    },
  },
});
