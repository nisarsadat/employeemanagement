// src/stores/LetryFormatRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

export const useLetryFormatRepository = defineStore('LetryFormatRepository', {
  state: () => ({
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,

    Search: '',

    LetryFormats: [],
    LetryFormat: {},

    loading: false,
    error: null,
  }),

  actions: {
    async FetchLetryFormats() {
      this.loading = true;
      try {
        const queryParams = buildListParams(this, {
          includeSearch: true,
          includePagination: true,
        });

        const res = await axios.get('/letry-formats', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'LetryFormats');
        return res.data;
      } catch (e) {
        this.error = e;
        this.LetryFormats = [];
        this.totalItems = 0;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchLetryFormat(id) {
      const { data } = await axios.get(`/letry-formats/${id}`);
      this.LetryFormat = data?.data ?? data ?? {};
      return data;
    },

    async CreateLetryFormat(payload) {
      const { data } = await axios.post(`/letry-formats`, payload);
      return data;
    },

    async UpdateLetryFormat(id, payload) {
      const { data } = await axios.put(`/letry-formats/${id}`, payload);
      return data;
    },

    async DeleteLetryFormat(id) {
      await axios.delete(`/letry-formats/${id}`);
      await this.FetchLetryFormats();
    },
  },
});