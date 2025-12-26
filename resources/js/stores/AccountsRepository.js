import { defineStore } from 'pinia';
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

function setContentType(type) {
  if (!type) {
    delete axios.defaults.headers.common['Content-Type'];
    return;
  }
  axios.defaults.headers.common['Content-Type'] = type;
}

export const useAccountsRepository = defineStore('AccountsRepository', {
  state() {
    return {
      router: useRouter(),

      // pagination & meta
      page: 1,
      itemsPerPage: 10,
      totalItems: 0,
      loading: false,

      // data
      Accounts: [],
      Account: reactive({}),

      // ui
      createDialog: false,
      updateDialog: false,

      // filters
      Search: '',
      clearSearch: '',
    };
  },

  actions: {
    async FetchAccountsData({ page, itemsPerPage } = {}) {
      this.loading = true;
      setContentType('application/json');

      const p = page ?? this.page;
      const pp = itemsPerPage ?? this.itemsPerPage;

      const resp = await axios.get(`/accounts`, {
        params: {
          page: p,
          perPage: pp,
          search: this.Search || '',
        },
      });

      this.Accounts = resp?.data?.data ?? [];
      this.totalItems = resp?.data?.meta?.total ?? 0;

      this.page = resp?.data?.meta?.current_page ?? p;
      this.itemsPerPage = resp?.data?.meta?.per_page ?? pp;

      this.loading = false;
    },

    async FetchAccountData(id) {
      setContentType('application/json');
      const resp = await axios.get(`/accounts/${id}`);
      this.Account = resp?.data?.data ?? {};
    },

    async CreateAccount(payload) {
      setContentType('application/json');
      await axios.post(`/accounts`, payload);
      this.createDialog = false;
      await this.FetchAccountsData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async UpdateAccount(id, payload) {
      setContentType('application/json');
      await axios.put(`/accounts/${id}`, payload);
      this.updateDialog = false;
      await this.FetchAccountsData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    async DeleteAccount(id) {
      await axios.delete(`/accounts/${id}`);
      await this.FetchAccountsData({ page: this.page, itemsPerPage: this.itemsPerPage });
    },

    ResetFilters() {
      this.Search = '';
    },
  },
});
