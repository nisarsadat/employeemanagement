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

export const useFinancesRepository = defineStore("FinancesRepository", {
  state() {
    return {
      router: useRouter(),

      // pagination & meta
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      loading: false,

      // data
      Finances: reactive([]),
      Finance: reactive({}),

      // options (for selects)
      AccountsOptions: reactive([]),
      EmployeesOptions: reactive([]),
      ResourcesOptions: reactive([]),

      // ui
      createDialog: false,
      updateDialog: false,

      // filters
      Search: "",
      clearSearch: "",
      from: null, // date from
      to: null,   // date to
    };
  },

  actions: {
    async FetchFinancesData({ page, itemsPerPage } = {}) {
      this.loading = true;
      setContentType("application/json");

      const p = page ?? this.page;
      const pp = itemsPerPage ?? this.itemsPerPage;

      const resp = await axios.get(`/finances`, {
        params: {
          page: p,
          perPage: pp,
          search: this.Search || "",
          from: this.from ?? "",
          to: this.to ?? "",
        },
      });

      this.Finances = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;

      this.page = resp?.data?.meta?.current_page ?? p;
      this.itemsPerPage = resp?.data?.meta?.per_page ?? pp;

      this.loading = false;
    },

    async FetchFinanceData(id) {
      setContentType("application/json");
      const resp = await axios.get(`/finances/${id}`);
      this.Finance = resp?.data?.data ?? {};
    },

    async CreateFinance(payload) {
      setContentType("application/json");
      await axios.post(`/finances`, payload);
      this.createDialog = false;
      await this.FetchFinancesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateFinance(id, payload) {
      setContentType("application/json");
      await axios.put(`/finances/${id}`, payload);
      this.updateDialog = false;
      await this.FetchFinancesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteFinance(id) {
      await axios.delete(`/finances/${id}`);
      await this.FetchFinancesData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    // options loaders
    async FetchAccountsOptions({ perPage = 300 } = {}) {
      setContentType("application/json");
      const resp = await axios.get(`/accounts`, { params: { page: 1, perPage, search: "" } });
      this.AccountsOptions = resp?.data?.data ?? [];
    },

    async FetchEmployeesOptions({ perPage = 300 } = {}) {
      setContentType("application/json");
      // ⚠️ adjust endpoint if yours is different
      const resp = await axios.get(`/office-employees`, { params: { page: 1, perPage, search: "" } });
      this.EmployeesOptions = resp?.data?.data ?? [];
    },

    async FetchResourcesOptions({ perPage = 300 } = {}) {
      setContentType("application/json");
      // ⚠️ adjust endpoint if yours is different
      const resp = await axios.get(`/resources`, { params: { page: 1, perPage, search: "" } });
      this.ResourcesOptions = resp?.data?.data ?? [];
    },

    async FetchAllOptions() {
      await Promise.all([
        this.FetchAccountsOptions({ perPage: 500 }),
        this.FetchEmployeesOptions({ perPage: 500 }),
        this.FetchResourcesOptions({ perPage: 500 }),
      ]);
    },

    ResetFilters() {
      this.Search = "";
      this.from = null;
      this.to = null;
    },
  },
});
