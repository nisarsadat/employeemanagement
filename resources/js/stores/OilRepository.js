// src/stores/OilRepository.js
import { defineStore } from 'pinia'
import { reactive } from 'vue'
import axios from 'axios'

// tiny helper to match your pattern
function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type']
    return
  }
  axios.defaults.headers.common['Content-Type'] = type
}

export const useOilRepository = defineStore('OilRepository', {
  state() {
    return {
      // shared pagination/meta
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      loading: false,

      // ---- ACCOUNTS ----
      OilAccounts: reactive([]),
      OilAccount: reactive({}),

      // ---- INCOME OILS (comes) ----
      IncomeOils: reactive([]),
      IncomeOil: reactive({}),

      // ---- EXPENSE OILS (goes) ----
      ExpenseOils: reactive([]),
      ExpenseOil: reactive({}),

      // ui
      createDialog: false,
      updateDialog: false,

      // filters
      Search: '',
      from: null,
      to: null,

      // ✅ YEARS dropdown loading
      yearsLoading: false,

      // ✅ year/month filters (Gregorian values ONLY)
      year: null, // 2026
      month: null, // 1..12

      // ✅ years dropdown (Gregorian years from backend)
      availableYears: reactive([]),

      // summary (cards)
      summary: reactive({
        totalExpense: 0,
        totalIncome: 0,
        totalOilQuantity: 0,
        expenseCount: 0,
        incomeCount: 0,
        oilAccountsCount: 0,

        // optional extra fields (safe)
        filteredExpense: 0,
        filteredIncome: 0,
      }),
    }
  },

  actions: {
    // =========================
    // YEARS
    // =========================
    async FetchYears(type) {
      this.yearsLoading = true
      try {
        const resp = await axios.get(`/${type}/years`)
        const payload = resp.data

        // ✅ ALWAYS force array
        this.availableYears =
          Array.isArray(payload) ? payload
          : Array.isArray(payload?.data) ? payload.data
          : Array.isArray(payload?.years) ? payload.years
          : []
      } finally {
        this.yearsLoading = false
      }
    },

    // ------------------ ACCOUNTS ------------------
    async FetchOilAccounts({ page, itemsPerPage } = {}) {
      this.loading = true
      setContentType('application/json')

      const p = page ?? this.page
      const pp = itemsPerPage ?? this.itemsPerPage

      try {
        const resp = await axios.get(`/oil-accounts`, {
          params: {
            page: p,
            perPage: pp,
            search: this.Search || '',
            year: this.year ?? undefined,
            month: this.month ?? undefined,
          },
        })

        this.OilAccounts = resp?.data?.data ?? []
        this.totalItems = resp?.data?.meta?.total ?? 0

        if (resp?.data?.summary) this.summary = resp.data.summary
      } finally {
        this.loading = false
      }
    },

    async FetchOilSummary() {
      setContentType('application/json')

      const resp = await axios.get(`/oil/summary`, {
        params: {
          search: this.Search || undefined,
          year: this.year ?? undefined,
          month: this.month ?? undefined,
          from: this.from ?? undefined,
          to: this.to ?? undefined,
        },
      })

      this.summary = resp?.data ?? {
        totalExpense: 0,
        totalIncome: 0,
        totalOilQuantity: 0,
        expenseCount: 0,
        incomeCount: 0,
        oilAccountsCount: 0,
        filteredExpense: 0,
        filteredIncome: 0,
      }
    },

    async FetchOilAccount(id) {
      setContentType('application/json')
      const resp = await axios.get(`/oil-accounts/${id}`)
      this.OilAccount = resp?.data?.data ?? {}
    },

    async CreateOilAccount(payload) {
      setContentType('application/json')
      await axios.post(`/oil-accounts`, payload)
      this.createDialog = false
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    async UpdateOilAccount(id, payload) {
      setContentType('application/json')
      await axios.put(`/oil-accounts/${id}`, payload)
      this.updateDialog = false
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    async DeleteOilAccount(id) {
      await axios.delete(`/oil-accounts/${id}`)
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    // ------------------ INCOME (comes) ------------------
    async FetchIncomeOils({ page, itemsPerPage } = {}) {
      this.loading = true
      setContentType('application/json')

      const p = page ?? this.page
      const pp = itemsPerPage ?? this.itemsPerPage

      try {
        const resp = await axios.get(`/income-oils`, {
          params: {
            page: p,
            perPage: pp,
            search: this.Search || '',
            from: this.from ?? undefined,
            to: this.to ?? undefined,
            year: this.year ?? undefined,
            month: this.month ?? undefined,
          },
        })

        this.IncomeOils = resp?.data?.data ?? []
        this.totalItems = resp?.data?.meta?.total ?? 0

        if (resp?.data?.summary) this.summary = resp.data.summary
      } finally {
        this.loading = false
      }
    },

    async FetchIncomeOil(id) {
      setContentType('application/json')
      const resp = await axios.get(`/income-oils/${id}`)
      this.IncomeOil = resp?.data?.data ?? {}
    },

    async CreateIncomeOil(payload) {
      setContentType('application/json')
      await axios.post(`/income-oils`, payload)
      this.createDialog = false
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    async UpdateIncomeOil(id, payload) {
      setContentType('application/json')
      await axios.put(`/income-oils/${id}`, payload)
      this.updateDialog = false
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    async DeleteIncomeOil(id) {
      await axios.delete(`/income-oils/${id}`)
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    // ------------------ EXPENSE (goes) ------------------
    async FetchExpenseOils({ page, itemsPerPage } = {}) {
      this.loading = true
      setContentType('application/json')

      const p = page ?? this.page
      const pp = itemsPerPage ?? this.itemsPerPage

      try {
        const resp = await axios.get(`/expense-oils`, {
          params: {
            page: p,
            perPage: pp,
            search: this.Search || '',
            year: this.year ?? undefined,
            month: this.month ?? undefined,
          },
        })

        this.ExpenseOils = resp?.data?.data ?? []
        this.totalItems = resp?.data?.meta?.total ?? 0

        if (resp?.data?.summary) this.summary = resp.data.summary
      } finally {
        this.loading = false
      }
    },

    async FetchExpenseOil(id) {
      setContentType('application/json')
      const resp = await axios.get(`/expense-oils/${id}`)
      this.ExpenseOil = resp?.data?.data ?? {}
    },

    async CreateExpenseOil(payload) {
      setContentType('application/json')
      await axios.post(`/expense-oils`, payload)
      this.createDialog = false
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    async UpdateExpenseOil(id, payload) {
      setContentType('application/json')
      await axios.put(`/expense-oils/${id}`, payload)
      this.updateDialog = false
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage })
    },

    async DeleteExpenseOil(id) {
      await axios.delete(`/expense-oils/${id}`)
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage })
    },
  },
})
