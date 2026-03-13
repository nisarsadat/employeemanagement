import { defineStore } from 'pinia';
import axios from 'axios';

export const useZoneHawzaRepository = defineStore('zoneHawzaRepo', {
  state: () => ({
    loading: false,
    error: null,

    page: 1,
    itemsPerPage: 10,
    totalItems: 0,

    Search: '',
  }),

  actions: {
    async FetchZonesData({ page, perPage, search } = {}) {
      this.loading = true;
      this.error = null;
      try {
        this.page = page ?? this.page ?? 1;
        this.itemsPerPage = perPage ?? this.itemsPerPage ?? 10;
        this.Search = search ?? this.Search ?? '';

        const res = await axios.get('/zones', {
          params: {
            page: this.page,
            perPage: this.itemsPerPage,
            search: this.Search,
          },
        });

        this.totalItems =
          res?.data?.meta?.total ??
          res?.data?.total ??
          res?.data?.data?.length ??
          0;

        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        this.totalItems = 0;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchZoneData(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.get(`/zones/${id}`);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async CreateZone(payload) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.post('/zones', payload);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async UpdateZone(id, payload) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.put(`/zones/${id}`, payload);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async DeleteZone(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.delete(`/zones/${id}`);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchHawzasData({ page, perPage, search, zone_id = null } = {}) {
      this.loading = true;
      this.error = null;
      try {
        this.page = page ?? this.page ?? 1;
        this.itemsPerPage = perPage ?? this.itemsPerPage ?? 10;
        this.Search = search ?? this.Search ?? '';

        const res = await axios.get('/hawzas', {
          params: {
            page: this.page,
            perPage: this.itemsPerPage,
            search: this.Search,
            zone_id,
          },
        });

        this.totalItems =
          res?.data?.meta?.total ??
          res?.data?.total ??
          res?.data?.data?.length ??
          0;

        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        this.totalItems = 0;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async FetchHawzaData(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.get(`/hawzas/${id}`);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async CreateHawza(payload) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.post('/hawzas', payload);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async UpdateHawza(id, payload) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.put(`/hawzas/${id}`, payload);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },

    async DeleteHawza(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.delete(`/hawzas/${id}`);
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
        throw e;
      } finally {
        this.loading = false;
      }
    },
  },
});