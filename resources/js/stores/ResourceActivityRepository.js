import { defineStore } from 'pinia';
import axios from 'axios';

export const useResourceActivityRepository = defineStore('ResourceActivityRepository', {
  state: () => ({
    loading: false,
    error: null,

    // shared filters
    search: '',
    itemsPerPage: 'all',

    // counts
    counts: {
      total: 0,
      active: 0,
      inactive: 0,
      today_added: 0,
      since: null,
    },

    // active list
    active: {
      items: [],
      meta: {},
      page: 1,
      totalItems: 0,
    },

    // inactive list
    inactive: {
      items: [],
      meta: {},
      page: 1,
      totalItems: 0,
    },

    // single
    one: null,
  }),

  actions: {
    setSearch(v = '') {
      this.search = v?.trim?.() ?? '';
    },

    setItemsPerPage(v = 'all') {
      this.itemsPerPage = v === 0 || v === '0' ? 'all' : v;
      this.active.page = 1;
      this.inactive.page = 1;
    },

    setPage(status = 'active', page = 1) {
      const p = Math.max(1, Number(page || 1));
      if (status === 'inactive') {
        this.inactive.page = p;
      } else {
        this.active.page = p;
      }
    },

    getPage(status = 'active') {
      return status === 'inactive'
        ? Number(this.inactive.page || 1)
        : Number(this.active.page || 1);
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
        const currentPage = this.getPage(mode);

        const { data } = await axios.get('/resource-activity', {
          params: {
            mode,
            perPage: this.itemsPerPage,
            search: this.search || undefined,
            page: this.itemsPerPage === 'all' ? undefined : currentPage,
          },
        });

        const items = Array.isArray(data?.data) ? data.data : [];
        const meta = data?.meta || {
          current_page: 1,
          last_page: 1,
          per_page: items.length,
          total: items.length,
          from: items.length ? 1 : 0,
          to: items.length,
        };

        if (mode === 'inactive') {
          this.inactive.items = items;
          this.inactive.meta = meta;
          this.inactive.totalItems = Number(meta?.total ?? items.length ?? 0);
          this.inactive.page = Number(meta?.current_page || currentPage || 1);
        } else {
          this.active.items = items;
          this.active.meta = meta;
          this.active.totalItems = Number(meta?.total ?? items.length ?? 0);
          this.active.page = Number(meta?.current_page || currentPage || 1);
        }
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || 'Error loading resources';

        if (mode === 'inactive') {
          this.inactive.items = [];
          this.inactive.meta = {};
          this.inactive.totalItems = 0;
        } else {
          this.active.items = [];
          this.active.meta = {};
          this.active.totalItems = 0;
        }
      } finally {
        this.loading = false;
      }
    },

    async fetchOne(id) {
      if (!id) return null;
      try {
        const { data } = await axios.get(`/resource-activity/${id}`);
        this.one = data?.data ?? data;
        return this.one;
      } catch (e) {
        console.error(e);
        return null;
      }
    },
  },
});