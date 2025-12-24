import { defineStore } from "pinia";
import { reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common["Content-Type"];
    return;
  }
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
      Income: reactive({}),

      // ui
      createDialog: false,
      updateDialog: false,

      // filters
      Search: "",
      clearSearch: "",
      from: null, // received_at from
      to: null,   // received_at to
    };
  },

  actions: {
    async FetchIncomesData({ page, itemsPerPage } = {}) {
      this.loading = true;
      setContentType("application/json");

      const p = page ?? this.page;
      const pp = itemsPerPage ?? this.itemsPerPage;

      const resp = await axios.get(`/incomes`, {
        params: {
          page: p,
          perPage: pp,
          search: this.Search || "",
          from: this.from ?? "",
          to: this.to ?? "",
        },
      });

      this.Incomes = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;

      this.page = resp?.data?.meta?.current_page ?? p;
      this.itemsPerPage = resp?.data?.meta?.per_page ?? pp;

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
      this.createDialog = false;
      await this.FetchIncomesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateIncome(id, payload) {
      setContentType("application/json");
      await axios.put(`/incomes/${id}`, payload);
      this.updateDialog = false;
      await this.FetchIncomesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteIncome(id) {
      await axios.delete(`/incomes/${id}`);
      await this.FetchIncomesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    // helpers
    ResetFilters() {
      this.Search = "";
      this.from = null;
      this.to = null;
    },
  },
});
