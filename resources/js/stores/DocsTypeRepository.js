import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';

export const useDocsTypeRepository = defineStore('DocsTypeRepository', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async FetchDocsTypes({ page = 1, perPage = 10000, search = '' } = {}) {
      this.loading = true;
      try {
        const { data } = await axios.get(
          `/docs-types?page=${page}&perPage=${perPage}&search=${encodeURIComponent(search)}`
        );
        return data;
      } finally {
        this.loading = false;
      }
    },

    async FetchDocsType(id) {
      const { data } = await axios.get(`/docs-types/${id}`);
      return data;
    },

    async CreateDocsType(payload) {
      const { data } = await axios.post(`/docs-types`, payload);
      toast.success('نوع سند اضافه شد');
      return data;
    },

    async UpdateDocsType(id, payload) {
      const { data } = await axios.put(`/docs-types/${id}`, payload);
      toast.success('نوع سند تغییر شد');
      return data;
    },

    async DeleteDocsType(id) {
      await axios.delete(`/docs-types/${id}`);
      toast.success('نوع سند حذف شد');
    },
  },
});
