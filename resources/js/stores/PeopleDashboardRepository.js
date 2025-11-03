// src/stores/PeopleDashboardRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';

axios.defaults.headers.common['Accept'] = 'application/json';

// ---- Helpers ----
function toArray(res) {
  if (Array.isArray(res)) return res;
  if (res && Array.isArray(res.data)) return res.data;
  return [];
}

function toDataMeta(res) {
  // Expected: { data: [], meta: {...} }
  if (res && Array.isArray(res.data) && res.meta) return res;

  // If backend returned a simple array
  if (Array.isArray(res)) {
    return {
      data: res,
      meta: { total: res.length, current_page: 1, per_page: res.length, last_page: 1 },
    };
  }

  // If backend returned { data: [] }
  if (res && Array.isArray(res.data)) {
    return {
      data: res.data,
      meta: { total: res.data.length, current_page: 1, per_page: res.data.length, last_page: 1 },
    };
  }

  // Fallback (empty)
  return {
    data: [],
    meta: { total: 0, current_page: 1, per_page: 10, last_page: 1 },
  };
}

// ---- Store ----
export const usePeopleDashboardRepository = defineStore('PeopleDashRepo', {
  state: () => ({
    Search: '',
    From: '',
    To: '',
    Zone: '',
    GroupBy: 'month', // day|week|month
    Loading: false,
  }),

  actions: {
    params(extra = {}) {
      const p = {
        search: this.Search || undefined,
        from: this.From || undefined,
        to: this.To || undefined,
        zone: this.Zone || undefined,
        ...extra,
      };
      Object.keys(p).forEach((k) => p[k] === undefined && delete p[k]);
      return p;
    },

    // ---- Cards ----
    async fetchCards() {
      const r = await axios.get('/dashboard/people/cards', { params: this.params() });
      return r?.data ?? { active_employees: 0, new_resources: 0, captive_deliveries: 0 };
    },

    // ---- Charts ----
    async employeesByZone() {
      const r = await axios.get('/dashboard/people/charts/employees-by-zone', {
        params: this.params(),
      });
      return toArray(r?.data);
    },

    async employeesWithEventsByZone() {
      const r = await axios.get('/dashboard/people/charts/employees-with-events-by-zone', {
        params: this.params(),
      });
      return toArray(r?.data);
    },

    async employeesWithInformationsByZone() {
      const r = await axios.get('/dashboard/people/charts/employees-with-informations-by-zone', {
        params: this.params(),
      });
      return toArray(r?.data);
    },

    async resourcesOverTime() {
      const r = await axios.get('/dashboard/people/charts/resources-over-time', {
        params: this.params({ groupBy: this.GroupBy }),
      });
      return toArray(r?.data);
    },

    async captiveDeliveriesOverTime() {
      const r = await axios.get('/dashboard/people/charts/captive-deliveries-over-time', {
        params: this.params({ groupBy: this.GroupBy }),
      });
      return toArray(r?.data);
    },

    // ---- Table ----
    async employeeActivity(group = 'events', page = 1, per_page = 10) {
      const r = await axios.get('/dashboard/people/tables/employee-activity', {
        params: this.params({ group, page, per_page }),
      });
      return toDataMeta(r?.data);
    },
  },
});
