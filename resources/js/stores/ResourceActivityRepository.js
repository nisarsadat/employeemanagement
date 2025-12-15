import { defineStore } from 'pinia';
import axios from 'axios';

export const useResourceActivityRepository = defineStore('ResourceActivityRepository', {
  state: () => ({
    loading: false,
    error: null,

    // filters
    search: '',
    perPage: 'all',

    // counts
    counts: {
      total: 0,
      active: 0,
      inactive: 0,
      today_added: 0,
      since: null,
    },

    // lists
    active: { items: [], meta: {} },
    inactive: { items: [], meta: {} },

    // single
    one: null,
  }),

  actions: {
    setPage() {
      // (kept for compatibility with your UI calls)
    },

    async fetchCounts() {
      try {
        const { data } = await axios.get('/resource-activity/counts');
        this.counts = data || this.counts;
      } catch (e) {
        console.error(e);
      }
    },

    async fetchList(mode = 'active') {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await axios.get('/resource-activity', {
          params: {
            mode,
            perPage: this.perPage,
            search: this.search,
          },
        });

        // Laravel Resource collection shape: { data: [...], meta: {...} }
        const items = data?.data || [];
        const meta = data?.meta || {};

        if (mode === 'active') {
          this.active = { items, meta };
        } else {
          this.inactive = { items, meta };
        }
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || 'Error loading resources';
      } finally {
        this.loading = false;
      }
    },

    async fetchOne(id) {
      if (!id) return null;
      try {
        const { data } = await axios.get(`/resource-activity/${id}`);
        // single returns ResourceResource -> { data: {...} } in many setups; but can also be plain object
        this.one = data?.data ?? data;
        return this.one;
      } catch (e) {
        console.error(e);
        return null;
      }
    },
  },
});
