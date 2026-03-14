import { defineStore } from 'pinia';
import axios from 'axios';

axios.defaults.headers.common['Accept'] = 'application/json';

export const useOfficeEmployeeActivityRepository = defineStore('officeEmployeeActivityRepo', {
  state: () => ({
    // shared controls
    search: '',
    itemsPerPage: 10, // number | 'all'

    // active bucket
    active: {
      items: [],
      meta: null,
      links: null,
      page: 1,
      totalItems: 0,
    },

    // inactive bucket
    inactive: {
      items: [],
      meta: null,
      links: null,
      page: 1,
      totalItems: 0,
    },

    counts: {
      active: 0,
      inactive: 0,
      total: 0,
    },

    loading: false,
    error: null,
  }),

  getters: {
    activePage: (state) => Number(state.active.page || 1),
    inactivePage: (state) => Number(state.inactive.page || 1),

    activeTotal: (state) => Number(state.active.totalItems || 0),
    inactiveTotal: (state) => Number(state.inactive.totalItems || 0),

    activeLastPage: (state) => Number(state.active?.meta?.last_page || 1),
    inactiveLastPage: (state) => Number(state.inactive?.meta?.last_page || 1),
  },

  actions: {
    setSearch(v = '') {
      this.search = v?.trim?.() ?? '';
    },

    setItemsPerPage(v = 10) {
      this.itemsPerPage = v === 0 || v === '0' ? 'all' : v;
      this.resetPages();
    },

    setPage(status = 'active', p = 1) {
      const page = Math.max(1, Number(p || 1));
      if (status === 'inactive') {
        this.inactive.page = page;
      } else {
        this.active.page = page;
      }
    },

    getPage(status = 'active') {
      return status === 'inactive'
        ? Number(this.inactive.page || 1)
        : Number(this.active.page || 1);
    },

    getTotalItems(status = 'active') {
      return status === 'inactive'
        ? Number(this.inactive.totalItems || 0)
        : Number(this.active.totalItems || 0);
    },

    resetPages() {
      this.active.page = 1;
      this.inactive.page = 1;
    },

    async fetchCounts() {
      try {
        const { data } = await axios.get('/office-employees/activity-counts');
        this.counts = {
          active: Number(data?.active ?? 0),
          inactive: Number(data?.inactive ?? 0),
          total: Number(data?.total ?? 0),
        };
      } catch (e) {
        // non-fatal
      }
    },

    async fetchList(status = 'active') {
      const url = status === 'inactive' ? '/office-employees/inactive' : '/office-employees/active';
      const currentPage = this.getPage(status);

      const params = {
        search: this.search || undefined,
        perPage: this.itemsPerPage ?? undefined,
        page: this.itemsPerPage === 'all' ? undefined : currentPage,
      };

      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.get(url, { params });
        const parsed = this._normalizeListResponse(data, currentPage);

        if (status === 'inactive') {
          this.inactive.items = parsed.items;
          this.inactive.meta = parsed.meta;
          this.inactive.links = parsed.links;
          this.inactive.totalItems = Number(parsed.meta?.total ?? parsed.items.length ?? 0);
          this.inactive.page = Number(parsed.meta?.current_page || currentPage || 1);
        } else {
          this.active.items = parsed.items;
          this.active.meta = parsed.meta;
          this.active.links = parsed.links;
          this.active.totalItems = Number(parsed.meta?.total ?? parsed.items.length ?? 0);
          this.active.page = Number(parsed.meta?.current_page || currentPage || 1);
        }
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Failed to load employees';

        if (status === 'inactive') {
          this.inactive.items = [];
          this.inactive.meta = null;
          this.inactive.links = null;
          this.inactive.totalItems = 0;
        } else {
          this.active.items = [];
          this.active.meta = null;
          this.active.links = null;
          this.active.totalItems = 0;
        }
      } finally {
        this.loading = false;
      }
    },

    _normalizeListResponse(payload, fallbackPage = 1) {
      if (Array.isArray(payload)) {
        return {
          items: payload,
          meta: {
            current_page: 1,
            last_page: 1,
            per_page: payload.length,
            total: payload.length,
            from: payload.length ? 1 : 0,
            to: payload.length,
          },
          links: null,
        };
      }

      const items = Array.isArray(payload?.data) ? payload.data : [];
      const rawMeta = payload?.meta ?? null;
      const links = payload?.links ?? null;

      const meta = rawMeta ?? {
        current_page: fallbackPage,
        last_page: 1,
        per_page: items.length,
        total: items.length,
        from: items.length ? 1 : 0,
        to: items.length,
      };

      return { items, meta, links };
    },
  },
});