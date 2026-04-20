import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';

export const useMarjaRepository = defineStore('MarjaRepository', {
  state: () => ({
    loading: false,
    error: null,

    Marjas: [],
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    Search: '',
  }),

  actions: {
    async FetchMarjas() {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.get(
          `/marjas?page=${this.page}&perPage=${this.itemsPerPage}&search=${encodeURIComponent(this.Search)}`
        );

        this.Marjas = data?.data || [];
        this.totalItems = data?.meta?.total || 0;

        return data;
      } catch (error) {
        this.error = error?.response?.data?.message || 'خطا در دریافت معلومات';
        throw error;
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
      return data;
    },

    async UpdateMarja(id, payload) {
      const { data } = await axios.put(`/marjas/${id}`, payload);
      return data;
    },

    async DeleteMarja(id) {
      await axios.delete(`/marjas/${id}`);
    },
  },
});