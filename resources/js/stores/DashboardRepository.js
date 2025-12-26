// src/repositories/DashboardRepository.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useDashboardRepository = defineStore('DashboardRepository', {
  state: () => ({
    loading: false,
    silentLoading: false,
    error: null,

    dashboard: null,

    // handy pieces (optional, but useful)
    employees: null,
    resources: null,
    informations: null,
    events: null,
    captive_deliveries: null,
    formats_by_zone: null,

    // ✅ ADDED ONLY
    money: null,
    oil: null,
  }),

  getters: {
    hasData(state) {
      return !!state.dashboard
    },
    employeesFlat(state) {
      return state.employees?.flat_sorted || []
    },
    employeesGrouped(state) {
      return state.employees?.grouped || []
    },
    zones(state) {
      return state.formats_by_zone?.zones || []
    },

    // ✅ ADDED ONLY (optional convenience)
    moneyIncomeTotal(state) {
      return state.money?.income_total ?? 0
    },
    moneyExpensedTotal(state) {
      return state.money?.expensed_total ?? 0
    },
    moneyBalance(state) {
      return state.money?.balance ?? 0
    },

    oilIncomeTotal(state) {
      return state.oil?.income_total ?? 0
    },
    oilExpensedTotal(state) {
      return state.oil?.expensed_total ?? 0
    },
    oilBalance(state) {
      return state.oil?.balance ?? 0
    },
  },

  actions: {
    reset() {
      this.loading = false
      this.silentLoading = false
      this.error = null

      this.dashboard = null
      this.employees = null
      this.resources = null
      this.informations = null
      this.events = null
      this.captive_deliveries = null
      this.formats_by_zone = null

      // ✅ ADDED ONLY
      this.money = null
      this.oil = null
    },

    async fetchDashboard() {
      this.loading = true
      this.error = null
      try {
        const { data } = await axios.get('/dashboard')
        this.applyDashboard(data)
        return data
      } catch (e) {
        this.error = e
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchDashboardSilent() {
      this.silentLoading = true
      this.error = null
      try {
        const { data } = await axios.get('/dashboard')
        this.applyDashboard(data)
        return data
      } catch (e) {
        this.error = e
        throw e
      } finally {
        this.silentLoading = false
      }
    },

    applyDashboard(data) {
      this.dashboard = data || null

      this.employees = data?.employees || null
      this.resources = data?.resources || null
      this.informations = data?.informations || null
      this.events = data?.events || null
      this.captive_deliveries = data?.captive_deliveries || null
      this.formats_by_zone = data?.formats_by_zone || null

      // ✅ ADDED ONLY
      this.money = data?.money || null
      this.oil = data?.oil || null
    },
  },
})
