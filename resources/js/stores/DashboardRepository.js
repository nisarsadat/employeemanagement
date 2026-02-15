// src/repositories/DashboardRepository.js
import { defineStore } from 'pinia';
import axios from '@/axios';

export const useDashboardRepository = defineStore('DashboardRepository', {
  state: () => ({
    loading: false,
    silentLoading: false,
    error: null,

    dashboard: null,

    // handy pieces
    calendar: 'miladi',
    calendar_meta: null,

    employees: null,
    employees_new: null,

    resources: null,
    informations: null,
    events: null,
    captive_deliveries: null,
    formats_by_zone: null,
    khayrat: null,

    // finance/oil cards
    money: null,
    oil: null,

    // monthly series (backend-driven labels)
    resources_monthly_counts: [],
    informations_monthly_counts: [],
    events_monthly_counts: [],
    khayrat_monthly_counts: [],
    money_monthly: null,
    oil_monthly: null,

    // period meta
    period: null,
    finance_period: null,

    // buckets meta
    monthly_buckets: [],
  }),

  getters: {
    hasData(state) {
      return !!state.dashboard;
    },

    employeesFlat(state) {
      return state.employees?.flat_sorted || [];
    },

    employeesFlatNew(state) {
      return state.employees_new?.flat_sorted || [];
    },

    employeeJobTypes(state) {
      return state.employees?.job_types || [];
    },

    zones(state) {
      return state.formats_by_zone?.zones || [];
    },

    moneyIncomeTotal(state) {
      return state.money?.income_total ?? 0;
    },
    moneyExpensedTotal(state) {
      return state.money?.expensed_total ?? 0;
    },
    moneyBalance(state) {
      return state.money?.balance ?? 0;
    },

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

      this.calendar = 'miladi';
      this.calendar_meta = null;

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

      this.resources_monthly_counts = [];
      this.informations_monthly_counts = [];
      this.events_monthly_counts = [];
      this.khayrat_monthly_counts = [];
      this.money_monthly = null;
      this.oil_monthly = null;

      this.period = null;
      this.finance_period = null;

      this.monthly_buckets = [];
    },

    /**
     * Fetch dashboard with:
     * - calendar
     * - period (main)
     * - finance_period (money+oil+employee table)
     */
    async fetchDashboard(payload = {}) {
      this.loading = true;
      this.error = null;

      const period = payload?.period || 'yearly';
      const finance_period = payload?.finance_period || 'monthly';
      const calendar = payload?.calendar || this.calendar || 'miladi';

      try {
        const { data } = await axios.get('/dashboard', {
          params: {
            calendar,
            period,
            finance_period,
          },
        });

        this.dashboard = data;

        this.calendar = data?.calendar || calendar;
        this.calendar_meta = data?.calendar_meta || null;

        this.period = data?.period || null;
        this.finance_period = data?.finance_period || null;

        this.employees = data?.employees || null;
        this.employees_new = data?.employees_new || null;

        this.resources = data?.resources || null;
        this.informations = data?.informations || null;
        this.events = data?.events || null;
        this.captive_deliveries = data?.captive_deliveries || null;

        this.money = data?.money || null;
        this.oil = data?.oil || null;

        this.formats_by_zone = data?.formats_by_zone || null;
        this.khayrat = data?.khayrat || null;

        this.resources_monthly_counts = Array.isArray(data?.resources_monthly_counts)
          ? data.resources_monthly_counts
          : [];

        this.informations_monthly_counts = Array.isArray(data?.informations_monthly_counts)
          ? data.informations_monthly_counts
          : [];

        this.events_monthly_counts = Array.isArray(data?.events_monthly_counts)
          ? data.events_monthly_counts
          : [];

        this.khayrat_monthly_counts = Array.isArray(data?.khayrat_monthly_counts)
          ? data.khayrat_monthly_counts
          : [];

        this.money_monthly = data?.money_monthly || null;
        this.oil_monthly = data?.oil_monthly || null;

        this.monthly_buckets = Array.isArray(data?.monthly_buckets) ? data.monthly_buckets : [];
      } catch (e) {
        this.error = e;
      } finally {
        this.loading = false;
      }
    },
  },
});
