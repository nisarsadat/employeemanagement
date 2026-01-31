// src/repositories/DashboardRepository.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useDashboardRepository = defineStore('DashboardRepository', {
  state: () => ({
    loading: false,
    silentLoading: false,
    error: null,

    dashboard: null,

    // handy pieces
    employees: null,
    employees_new: null, // ✅ NEW table payload

    resources: null,
    informations: null,
    events: null,
    captive_deliveries: null,
    formats_by_zone: null,
    khayrat: null, // ✅ if you want to use it

    // ✅ finance/oil cards
    money: null,
    oil: null,

    // ✅ NEW (period meta from backend)
    period: null,
    finance_period: null,
  }),

  getters: {
    hasData(state) {
      return !!state.dashboard;
    },

    // OLD table
    employeesFlat(state) {
      return state.employees?.flat_sorted || [];
    },

    // NEW table
    employeesFlatNew(state) {
      return state.employees_new?.flat_sorted || [];
    },

    // job type chips
    employeeJobTypes(state) {
      return state.employees?.job_types || [];
    },

    zones(state) {
      return state.formats_by_zone?.zones || [];
    },

    // ✅ money
    moneyIncomeTotal(state) {
      return state.money?.income_total ?? 0;
    },
    moneyExpensedTotal(state) {
      return state.money?.expensed_total ?? 0;
    },
    moneyBalance(state) {
      return state.money?.balance ?? 0;
    },

    // ✅ oil
    oilIncomeTotal(state) {
      return state.oil?.income_total ?? 0;
    },
    oilExpensedTotal(state) {
      return state.oil?.expensed_total ?? 0;
    },
    oilBalance(state) {
      return state.oil?.balance ?? 0;
    },
  },

  actions: {
    reset() {
      this.loading = false;
      this.silentLoading = false;
      this.error = null;

      this.dashboard = null;

      this.employees = null;
      this.employees_new = null;

      this.resources = null;
      this.informations = null;
      this.events = null;
      this.captive_deliveries = null;
      this.formats_by_zone = null;
      this.khayrat = null;

      this.money = null;
      this.oil = null;

      this.period = null;
      this.finance_period = null;
    },

    /**
     * Fetch dashboard with TWO filters:
     * period         => main dashboard cards + formats + etc
     * finance_period => money + oil + office employee tables
     */
    async fetchDashboard(payload = {}) {
      this.loading = true;
      this.error = null;

      const period = payload?.period || 'yearly';
      const finance_period = payload?.finance_period || 'monthly';

      try {
        const { data } = await axios.get('/dashboard', {
          params: { period, finance_period },
        });
        this.applyDashboard(data);
        return data;
      } catch (e) {
        this.error = e;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Silent fetch (no UI blocks) with TWO filters
     */
    async fetchDashboardSilent(payload = {}) {
      this.silentLoading = true;
      this.error = null;

      const period = payload?.period || 'yearly';
      const finance_period = payload?.finance_period || 'monthly';

      try {
        const { data } = await axios.get('/dashboard', {
          params: { period, finance_period },
        });
        this.applyDashboard(data);
        return data;
      } catch (e) {
        this.error = e;
        throw e;
      } finally {
        this.silentLoading = false;
      }
    },

    applyDashboard(data) {
      this.dashboard = data || null;

      this.period = data?.period || null;
      this.finance_period = data?.finance_period || null;

      // ---------------- EMPLOYEES ----------------
      const emp = data?.employees || null;

      // ✅ normalize job_types to ALWAYS be array [{type,count}]
      let empJobTypes = emp?.job_types;

      // if backend sent as object: { "اداري": 4, "اوپراتیفي": 2 }
      if (empJobTypes && !Array.isArray(empJobTypes) && typeof empJobTypes === 'object') {
        empJobTypes = Object.entries(empJobTypes).map(([type, count]) => ({
          type,
          count: Number(count) || 0,
        }));
      }

      this.employees = emp
        ? {
            ...emp,
            job_types: Array.isArray(empJobTypes) ? empJobTypes : [],
            active: Number(emp?.active ?? 0),
            inactive: Number(emp?.inactive ?? 0),
          }
        : null;

      // ---------------- EMPLOYEES NEW TABLE ----------------
      this.employees_new = data?.employees_new || null;

      // ---------------- EVENTS ----------------
      const ev = data?.events || null;

      // ✅ normalize events.types to ALWAYS be array [{type,count}]
      let evTypes = ev?.types;

      // if backend sent as object: { "سیاسي": 3, "جنایي": 1 ... }
      if (evTypes && !Array.isArray(evTypes) && typeof evTypes === 'object') {
        evTypes = Object.entries(evTypes).map(([type, count]) => ({
          type,
          count: Number(count) || 0,
        }));
      }

      this.events = ev
        ? {
            ...ev,
            types: Array.isArray(evTypes) ? evTypes : [],
          }
        : null;

      // ---------------- OTHERS ----------------
      this.resources = data?.resources || null;
      this.informations = data?.informations || null;
      this.captive_deliveries = data?.captive_deliveries || null;
      this.formats_by_zone = data?.formats_by_zone || null;
      this.khayrat = data?.khayrat || null;

      this.money = data?.money || null;
      this.oil = data?.oil || null;
    },
  },
});
