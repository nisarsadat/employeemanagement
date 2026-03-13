// resources/js/stores/EventRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';

axios.defaults.headers.common.Accept = 'application/json';

export const useEventRepo = defineStore('eventRepo', {
  state: () => ({
    // ---------------- Filters ----------------
    Search: '',
    EventTypeId: '',

    // ---------------- Global Pagination ----------------
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,

    // ---------------- Data ----------------
    eventTypes: [],
    eventTypesMeta: { total: 0, current_page: 1, per_page: 10 },

    eventSpecificTypes: [],
    eventSpecificTypesMeta: { total: 0, current_page: 1, per_page: 10 },

    optionsEventTypes: [],

    // ---------------- UI ----------------
    loading: false,
    error: null,
  }),

  getters: {
    eventTypesTotal(state) {
      return state.eventTypesMeta?.total || 0;
    },
    eventSpecificTypesTotal(state) {
      return state.eventSpecificTypesMeta?.total || 0;
    },
  },

  actions: {
    // ---------------- Helpers ----------------
    _eventTypesParams(extra = {}) {
      return {
        search: this.Search || undefined,
        ...extra,
      };
    },

    _eventSpecificTypesParams(extra = {}) {
      return {
        search: this.Search || undefined,
        event_type_id: this.EventTypeId || undefined,
        ...extra,
      };
    },

    // =========================================================
    // ✅ EVENT TYPES
    // =========================================================
    async FetchEventTypesData({ page = 1, perPage = 10, search = '' } = {}) {
      this.loading = true;
      this.error = null;

      try {
        this.Search = search ?? this.Search;
        this.page = page;
        this.itemsPerPage = perPage;

        const params = this._eventTypesParams({ page, perPage });
        const { data } = await axios.get('/event-types', { params });

        this.eventTypes = data?.data || [];
        this.eventTypesMeta = data?.meta || {
          total: 0,
          current_page: page,
          per_page: perPage,
        };

        this.page = this.eventTypesMeta?.current_page || page;
        this.itemsPerPage = this.eventTypesMeta?.per_page || perPage;
        this.totalItems = this.eventTypesMeta?.total || 0;

        return data;
      } catch (e) {
        console.error(e);
        this.eventTypes = [];
        this.eventTypesMeta = { total: 0, current_page: page, per_page: perPage };
        this.totalItems = 0;
        this.error = e?.response?.data?.message || e.message || 'ستونزه رامنځته شوه.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async CreateEventType(payload) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.post('/event-types', payload);
        return data;
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || e.message || 'ثبت نشو.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async UpdateEventType(id, payload) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.put(`/event-types/${id}`, payload);
        return data;
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || e.message || 'اپډېټ نشو.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async DeleteEventType(id) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.delete(`/event-types/${id}`);
        return data;
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || e.message || 'حذف نشو.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchEventTypeOptions() {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.get('/event-types/options');
        this.optionsEventTypes = data?.data || [];
        return data;
      } catch (e) {
        console.error(e);
        this.optionsEventTypes = [];
        this.error = e?.response?.data?.message || e.message || 'Options ترلاسه نه شول.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    // =========================================================
    // ✅ EVENT SPECIFIC TYPES
    // =========================================================
    async FetchEventSpecificTypesData({
      page = 1,
      perPage = 10,
      search = '',
      event_type_id = null,
    } = {}) {
      this.loading = true;
      this.error = null;

      try {
        this.Search = search ?? this.Search;
        this.EventTypeId = event_type_id ?? '';
        this.page = page;
        this.itemsPerPage = perPage;

        const params = this._eventSpecificTypesParams({ page, perPage });
        const { data } = await axios.get('/event-specific-types', { params });

        this.eventSpecificTypes = data?.data || [];
        this.eventSpecificTypesMeta = data?.meta || {
          total: 0,
          current_page: page,
          per_page: perPage,
        };

        this.page = this.eventSpecificTypesMeta?.current_page || page;
        this.itemsPerPage = this.eventSpecificTypesMeta?.per_page || perPage;
        this.totalItems = this.eventSpecificTypesMeta?.total || 0;

        return data;
      } catch (e) {
        console.error(e);
        this.eventSpecificTypes = [];
        this.eventSpecificTypesMeta = { total: 0, current_page: page, per_page: perPage };
        this.totalItems = 0;
        this.error = e?.response?.data?.message || e.message || 'ستونزه رامنځته شوه.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async CreateEventSpecificType(payload) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.post('/event-specific-types', payload);
        return data;
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || e.message || 'ثبت نشو.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async UpdateEventSpecificType(id, payload) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.put(`/event-specific-types/${id}`, payload);
        return data;
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || e.message || 'اپډېټ نشو.';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async DeleteEventSpecificType(id) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await axios.delete(`/event-specific-types/${id}`);
        return data;
      } catch (e) {
        console.error(e);
        this.error = e?.response?.data?.message || e.message || 'حذف نشو.';
        throw e;
      } finally {
        this.loading = false;
      }
    },
  },
});
