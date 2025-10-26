// src/stores/OilRepository.js
import { defineStore } from "pinia";
import { reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

// tiny helper to match your pattern
function setContentType(type) {
  if (!type) { delete axios.defaults.headers.common["Content-Type"]; return; }
  axios.defaults.headers.common["Content-Type"] = type;
}

export const useOilRepository = defineStore("OilRepository", {
  state() {
    return {
      router: useRouter(),

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
      Search: "",
      from: null,
      to: null,
    };
  },

  actions: {
    // ------------------ ACCOUNTS ------------------
    async FetchOilAccounts({ page, itemsPerPage }) {
      this.loading = true;
      setContentType("application/json");
      const resp = await axios.get(
        `/oil-accounts?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(this.Search || "")}`
      );
      this.OilAccounts = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    async FetchOilAccount(id) {
      setContentType("application/json");
      const resp = await axios.get(`/oil-accounts/${id}`);
      this.OilAccount = resp?.data?.data ?? {};
    },

    async CreateOilAccount(payload) {
      setContentType("application/json");
      await axios.post(`/oil-accounts`, payload);
      this.createDialog = false;
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateOilAccount(id, payload) {
      setContentType("application/json");
      await axios.put(`/oil-accounts/${id}`, payload);
      this.updateDialog = false;
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteOilAccount(id) {
      await axios.delete(`/oil-accounts/${id}`);
      await this.FetchOilAccounts({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    // ------------------ INCOME (comes) ------------------
    async FetchIncomeOils({ page, itemsPerPage }) {
      this.loading = true;
      setContentType("application/json");
      const resp = await axios.get(
        `/income-oils?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(this.Search || "")}`
      );
      this.IncomeOils = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    async FetchIncomeOil(id) {
      setContentType("application/json");
      const resp = await axios.get(`/income-oils/${id}`);
      this.IncomeOil = resp?.data?.data ?? {};
    },

    async CreateIncomeOil(payload) {
      setContentType("application/json");
      await axios.post(`/income-oils`, payload); // { oil_account_id, oil_quantity, source, note }
      this.createDialog = false;
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateIncomeOil(id, payload) {
      setContentType("application/json");
      await axios.put(`/income-oils/${id}`, payload);
      this.updateDialog = false;
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteIncomeOil(id) {
      await axios.delete(`/income-oils/${id}`);
      await this.FetchIncomeOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    // ------------------ EXPENSE (goes) ------------------
    async FetchExpenseOils({ page, itemsPerPage }) {
      this.loading = true;
      setContentType("application/json");
      const resp = await axios.get(
        `/expense-oils?page=${page}&perPage=${itemsPerPage}&search=${encodeURIComponent(this.Search || "")}`
      );
      this.ExpenseOils = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    async FetchExpenseOil(id) {
      setContentType("application/json");
      const resp = await axios.get(`/expense-oils/${id}`);
      this.ExpenseOil = resp?.data?.data ?? {};
    },

    async CreateExpenseOil(payload) {
      setContentType("application/json");
      await axios.post(`/expense-oils`, payload); // { oil_account_id, name, destination, oil_quantity, note }
      this.createDialog = false;
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateExpenseOil(id, payload) {
      setContentType("application/json");
      await axios.put(`/expense-oils/${id}`, payload);
      this.updateDialog = false;
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteExpenseOil(id) {
      await axios.delete(`/expense-oils/${id}`);
      await this.FetchExpenseOils({ page: this.page, itemsPerPage: this.itemsPerPage });
    },
  },
});
