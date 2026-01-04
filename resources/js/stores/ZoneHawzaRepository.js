import { defineStore } from 'pinia';
import axios from 'axios';

export const useZoneHawzaRepository = defineStore('zoneHawzaRepo', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async FetchZonesData({ page = 1, perPage = 100000, search = '' } = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.get('/zones', { params: { page, perPage, search } });
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
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

    async FetchHawzasData({ page = 1, perPage = 100000, search = '', zone_id = null } = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.get('/hawzas', { params: { page, perPage, search, zone_id } });
        return res.data;
      } catch (e) {
        this.error = e?.response?.data?.message || 'Error';
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
