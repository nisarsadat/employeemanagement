import { defineStore } from "pinia";
import { reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
// import { toast } from "vue3-toastify";

function setContentType(type) {
  if (!type) { delete axios.defaults.headers.common["Content-Type"]; return; }
  axios.defaults.headers.common["Content-Type"] = type;
}

export const useExpensesRepository = defineStore("ExpensesRepository", {
  state() {
    return {
      router: useRouter(),

      // pagination & meta
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      loading: false,

      // data
      Expenses: reactive([]),
      Expense: reactive([]),

      // ui
      createDialog: false,
      updateDialog: false,

      // filters
      Search: "",
      clearSearch: "",
      from: null,
      to: null,
    };
  },

  actions: {
    async FetchExpensesData({ page, itemsPerPage }) {
      this.loading = true;
      setContentType("application/json");
      const resp = await axios.get(
        `/expenses?page=${page}&perPage=${itemsPerPage}&search=${this.Search}&from=${this.from ?? ""}&to=${this.to ?? ""}`
      );
      this.Expenses = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    async FetchExpenseData(id) {
      setContentType("application/json");
      const resp = await axios.get(`/expenses/${id}`);
      this.Expense = resp?.data?.data ?? {};
    },

    async CreateExpense(payload) {
      setContentType("application/json");
      await axios.post(`/expenses`, payload);
      // toast?.success?.("Expense created", { autoClose: 1000 });
      this.createDialog = false;
      await this.FetchExpensesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateExpense(id, payload) {
      setContentType("application/json");
      await axios.put(`/expenses/${id}`, payload);
      // toast?.success?.("Expense updated", { autoClose: 1000 });
      this.updateDialog = false;
      await this.FetchExpensesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteExpense(id) {
      await axios.delete(`/expenses/${id}`);
      // toast?.success?.("Expense deleted", { autoClose: 1000 });
      await this.FetchExpensesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },
  },
});
