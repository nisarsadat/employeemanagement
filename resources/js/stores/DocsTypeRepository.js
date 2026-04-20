import { defineStore } from 'pinia';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { buildListParams, applyPaginatedResponse } from '@/utils/listHelpers';

function errText(e, fallback = 'بعضی مشکل پیش آمد') {
  return e?.response?.data?.message || e?.message || (typeof e === 'string' ? e : fallback);
}

export const useDocsTypeRepository = defineStore('DocsTypeRepository', {
  state: () => ({
    loading: false,
    error: null,

    DocsTypes: [],

    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    Search: '',
  }),

  actions: {
    async FetchDocsTypes() {
      this.loading = true;
      this.error = null;

      try {
        const queryParams = buildListParams(this, {
          includeSearch: true,
          includePagination: true,
          includeDateFilter: false,
        });

        const res = await axios.get('/docs-types', {
          params: queryParams,
        });

        applyPaginatedResponse(this, res, 'DocsTypes');
        return res.data;
      } catch (e) {
        this.error = errText(e);
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchDocsType(id) {
      const { data } = await axios.get(`/docs-types/${id}`);
      return data;
    },

    async CreateDocsType(payload) {
      const { data } = await axios.post('/docs-types', payload);
      return data;
    },

    async UpdateDocsType(id, payload) {
      const { data } = await axios.put(`/docs-types/${id}`, payload);
      return data;
    },

    async DeleteDocsType(id) {
      await axios.delete(`/docs-types/${id}`);
    },
  },
});