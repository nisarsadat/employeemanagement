import { defineStore } from "pinia";
import { reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
// import { toast } from "vue3-toastify";

function setContentType(type) {
  if (!type) { delete axios.defaults.headers.common["Content-Type"]; return; }
  axios.defaults.headers.common["Content-Type"] = type;
}

export const useIncomesRepository = defineStore("IncomesRepository", {
  state() {
    return {
      router: useRouter(),

      // pagination & meta
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      loading: false,

      // data
      Incomes: reactive([]),
      Income: reactive([]),

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
    async FetchIncomesData({ page, itemsPerPage }) {
      this.loading = true;
      setContentType("application/json");
      const resp = await axios.get(
        `/incomes?page=${page}&perPage=${itemsPerPage}&search=${this.Search}&from=${this.from ?? ""}&to=${this.to ?? ""}`
      );
      this.Incomes = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;
      this.loading = false;
    },

    async FetchIncomeData(id) {
      setContentType("application/json");
      const resp = await axios.get(`/incomes/${id}`);
      this.Income = resp?.data?.data ?? {};
    },

    async CreateIncome(payload) {
      setContentType("application/json");
      await axios.post(`/incomes`, payload);
      // toast?.success?.("Income created", { autoClose: 1000 });
      this.createDialog = false;
      await this.FetchIncomesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateIncome(id, payload) {
      setContentType("application/json");
      await axios.put(`/incomes/${id}`, payload);
      // toast?.success?.("Income updated", { autoClose: 1000 });
      this.updateDialog = false;
      await this.FetchIncomesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteIncome(id) {
      await axios.delete(`/incomes/${id}`);
      // toast?.success?.("Income deleted", { autoClose: 1000 });
      await this.FetchIncomesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },
  },
});
